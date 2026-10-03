<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\FlightBookingService;
use App\Services\Payment\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

/**
 * TripJack Flights doc rules around Fare Validate, Hold / Release PNR and
 * amendments (SUCCESS-only).
 */
class FlightBookingRulesTest extends TestCase
{
    use RefreshDatabase;

    protected FakeRazorpayService $razorpay;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->razorpay = new FakeRazorpayService();
        $this->app->instance(RazorpayService::class, $this->razorpay);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function draft(array $conditions = []): array
    {
        $segment = ['id' => '1', 'da' => ['code' => 'DEL'], 'aa' => ['code' => 'BOM'], 'fD' => ['aI' => ['code' => '6E'], 'fN' => '101'], 'dt' => '2026-11-01T06:00', 'at' => '2026-11-01T08:10'];

        return [
            'bookingId' => 'TJS200000000001',
            'resultsUrl' => route('flights.search').'?from=DEL&to=BOM',
            'expiresAt' => now()->addMinutes(10)->timestamp,
            'context' => ['tripType' => 'oneway', 'from' => 'DEL', 'to' => 'BOM', 'departDate' => '2026-11-01', 'returnDate' => '',
                'adults' => 1, 'children' => 0, 'infants' => 0, 'cabinClass' => 'ECONOMY', 'fareType' => 'REGULAR', 'legs' => []],
            'response' => [
                'bookingId' => 'TJS200000000001',
                'tripInfos' => [['sI' => [$segment]]],
                'conditions' => $conditions,
                'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 5000, 'BF' => 4200, 'TAF' => 800]]],
            ],
        ];
    }

    protected function submit(array $extra = [], array $conditions = [])
    {
        session(['flight_booking_draft' => $this->draft($conditions)]);

        return $this->post(route('flights.book'), array_merge([
            'review_booking_id' => 'TJS200000000001',
            'intent' => 'pay',
            'contact_email' => 'guest@example.com',
            'contact_phone' => '9876543210',
            'travellers' => [['title' => 'Mr', 'first_name' => 'Rahul', 'last_name' => 'Probe']],
        ], $extra));
    }

    protected function confirmedBooking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => $this->user->id,
            'vertical' => 'flight',
            'status' => 'confirmed',
            'tripjack_booking_id' => 'TJS300000000001',
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
            'flight_legs' => [['src' => 'DEL', 'dest' => 'BOM', 'departureDate' => '2026-11-01']],
            'flight_segments_payload' => ['travellerInfo' => [['fN' => 'Rahul', 'lN' => 'Probe', 'pt' => 'ADULT']]],
        ], $extra));
    }

    protected function tjError(string $code): array
    {
        return ['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => $code, 'message' => 'x']]];
    }

    public function test_fare_validate_input_error_returns_to_passenger_form_not_search(): void
    {
        Http::fake(['*/air/book/fare-validate' => Http::response($this->tjError('1067'), 400)]);

        $this->submit()
            ->assertRedirect(route('flights.passengers.show', ['booking' => 'TJS200000000001']))
            ->assertSessionHasErrors('travellers');
        $this->assertSame(0, Booking::count());
    }

    public function test_fare_validate_sold_out_still_returns_to_results(): void
    {
        Http::fake(['*/air/book/fare-validate' => Http::response($this->tjError('1071'), 400)]);

        $response = $this->submit();
        $this->assertStringContainsString('notice=unavailable', $response->headers->get('Location'));
        $this->assertSame(0, Booking::count());
    }

    public function test_hold_is_refused_server_side_when_fare_does_not_allow_it(): void
    {
        Http::fake();

        $this->submit(['intent' => 'hold'], ['isBA' => false])->assertSessionHasErrors('intent');
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/air/book'));
        $this->assertSame(0, Booking::count());
    }

    public function test_hold_that_tripjack_reports_failed_is_not_shown_as_held(): void
    {
        Http::fake([
            '*/air/book' => Http::response(['bookingId' => 'TJS200000000001', 'status' => ['success' => true]]),
            '*/booking-details' => Http::response(['order' => ['status' => 'FAILED'], 'status' => ['success' => true]]),
        ]);

        $this->submit(['intent' => 'hold'], ['isBA' => true])->assertRedirect(route('flights.search'));
        $this->assertSame(0, Booking::count());
    }

    public function test_changed_held_fare_is_repriced_before_payment(): void
    {
        $booking = $this->confirmedBooking(['status' => 'on_hold']);
        $stale = Payment::create(['booking_id' => $booking->id, 'razorpay_order_id' => 'order_old', 'amount' => 5600, 'currency' => 'INR', 'status' => 'created', 'purpose' => 'flight_confirm_book']);
        Http::fake(['*/air/fare-validate' => Http::response([
            'bookingId' => 'TJS300000000001', 'iobfe' => false,
            'alerts' => [['type' => 'FAREALERT', 'oldFare' => 5000, 'newFare' => 5400]],
            'status' => ['success' => true],
        ])]);

        $result = app(FlightBookingService::class)->validateHoldFare($booking);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('changed this fare', $result['message']);
        $booking->refresh();
        $this->assertEquals(5400, (float) $booking->tripjack_total_price);
        $this->assertGreaterThan(5600, (float) $booking->total_amount);
        $this->assertSame('failed', $stale->fresh()->status);

        // Pressing confirm again at the new price goes through.
        $this->assertTrue(app(FlightBookingService::class)->validateHoldFare($booking)['ok']);
    }

    public function test_release_hold_is_verified_as_unconfirmed(): void
    {
        $booking = $this->confirmedBooking(['status' => 'on_hold', 'tripjack_flight_pnr' => ['DEL-BOM' => 'PNR1']]);
        Http::fake([
            '*/air/unhold' => Http::response(['status' => ['success' => true]]),
            '*/booking-details' => Http::sequence()
                ->push(['order' => ['status' => 'UNCONFIRMED'], 'status' => ['success' => true]])
                ->push(['order' => ['status' => 'ON_HOLD'], 'status' => ['success' => true]]),
        ]);

        app(FlightBookingService::class)->releaseHold($booking);
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertNull($booking->fresh()->admin_note);

        $other = $this->confirmedBooking(['status' => 'on_hold', 'tripjack_booking_id' => 'TJS300000000002', 'tripjack_flight_pnr' => ['DEL-BOM' => 'PNR2']]);
        app(FlightBookingService::class)->releaseHold($other);
        $this->assertSame('cancelled', $other->fresh()->status);
        $this->assertStringContainsString('Release PNR was not confirmed', $other->fresh()->admin_note);
    }

    public function test_cancellation_is_blocked_while_tripjack_is_not_success(): void
    {
        Http::fake(['*/booking-details' => Http::response(['order' => ['status' => 'PENDING'], 'status' => ['success' => true]])]);
        $booking = $this->confirmedBooking();

        $result = app(FlightBookingService::class)->submitCancellation($booking);

        $this->assertFalse($result['success']);
        $this->assertSame(FlightBookingService::NOT_TICKETED_YET_MESSAGE, $result['message']);
        $this->assertNull($booking->fresh()->cancellation_requested_at);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'submit-amendment'));
    }
}
