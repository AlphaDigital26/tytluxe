<?php

namespace Tests\Feature;

use App\Http\Controllers\FlightAncillaryController;
use App\Mail\BookingUpdateMail;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * "Add seats, meals & baggage" for airlines that don't sell them online
 * (TripJack: add-ons are LCC-only, and several carriers have none), and the
 * guest's email once the airline answers an add-on purchase.
 */
class FlightExtrasAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function booking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => $this->user->id,
            'vertical' => 'flight',
            'status' => 'confirmed',
            'guest_email' => 'guest@example.com',
            'tripjack_booking_id' => 'TJS'.random_int(100000, 999999),
            'flight_route' => 'DEL-DXB',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 20000, 'total_amount' => 22000, 'tripjack_total_price' => 20000,
            'currency' => 'INR',
        ], $extra));
    }

    protected function ssrResponse(): array
    {
        return ['tripInfos' => [['sI' => [[
            'id' => 'S1', 'fD' => ['aI' => ['code' => 'G9', 'name' => 'Air Arabia'], 'fN' => '436'], 'da' => ['code' => 'DEL'], 'aa' => ['code' => 'DXB'], 'dt' => '2026-11-20T06:00', 'at' => '2026-11-20T08:45',
            'bI' => ['tI' => [['id' => 't1', 'fN' => 'Rahul', 'lN' => 'Probe', 'pt' => 'ADULT', 'ssrInfo' => ['MEAL' => [['code' => 'VGML', 'amount' => 600, 'desc' => 'Veg meal']]]]]],
        ]]]], 'status' => ['success' => true]];
    }

    public function test_airline_refusing_add_ons_gets_a_clear_message_not_try_again(): void
    {
        $booking = $this->booking();
        Http::fake(['*/ancillaries/fetch/ssr' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '2001', 'message' => 'SSR not supported for this airline']]], 400)]);

        $this->get(route('flights.extras.show', $booking->reference))
            ->assertRedirect(route('hotel.booking.confirmation', $booking->reference))
            ->assertSessionHas('booking_error', FlightAncillaryController::NOT_OFFERED_MESSAGE);
    }

    public function test_tripjack_outage_still_says_try_again(): void
    {
        $booking = $this->booking();
        Http::fake(['*/ancillaries/fetch/ssr' => Http::response('down', 503)]);

        $this->get(route('flights.extras.show', $booking->reference))
            ->assertSessionHas('booking_error', fn ($m) => str_contains($m, 'try again shortly'));
    }

    public function test_no_seat_map_still_offers_meals(): void
    {
        $booking = $this->booking();
        Http::fake([
            '*/ancillaries/fetch/ssr' => Http::response($this->ssrResponse()),
            '*/ancillaries/fetch/seat' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '2002', 'message' => 'Seat map not available']]], 400),
            '*/booking-details' => Http::response(['order' => ['status' => 'SUCCESS'], 'status' => ['success' => true]]),
        ]);

        $this->get(route('flights.extras.show', $booking->reference))
            ->assertOk()
            ->assertSee('VGML');
    }

    public function test_guest_is_emailed_when_the_airline_answers_an_add_on_purchase(): void
    {
        $booking = $this->booking(['flight_ssr_status' => 'pending']);

        $booking->update(['flight_ssr_status' => 'needs_review']); // staff only
        Mail::assertNothingQueued();

        $booking->update(['flight_ssr_status' => 'confirmed']);
        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->kind === BookingUpdateMail::EXTRAS
            && str_contains($mail->envelope()->subject, 'are confirmed')
            && str_contains($mail->render(), 'Your extras are confirmed'));

        $other = $this->booking(['flight_ssr_status' => 'pending']);
        $other->update(['flight_ssr_status' => 'failed', 'manual_refund_due_at' => now()]);
        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->booking->is($other)
            && str_contains($mail->render(), 'Our team is processing your refund'));
    }
}
