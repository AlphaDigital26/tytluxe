<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\TripJackLowBalanceAlert;
use App\Services\FlightBookingService;
use App\Services\Payment\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

/**
 * User Detail balance checks, international contact numbers, MF/MFT, Seat
 * Map gating on isa, and no-charge reschedules.
 */
class FlightAccountAndContactTest extends TestCase
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
            'bookingId' => 'TJS400000000001',
            'resultsUrl' => route('flights.search').'?from=DEL&to=BOM',
            'expiresAt' => now()->addMinutes(10)->timestamp,
            'context' => ['tripType' => 'oneway', 'from' => 'DEL', 'to' => 'BOM', 'departDate' => '2026-11-01', 'returnDate' => '',
                'adults' => 1, 'children' => 0, 'infants' => 0, 'cabinClass' => 'ECONOMY', 'fareType' => 'REGULAR', 'legs' => []],
            'response' => [
                'bookingId' => 'TJS400000000001',
                'tripInfos' => [['sI' => [$segment]]],
                'conditions' => $conditions,
                'totalPriceInfo' => ['totalFareDetail' => [
                    'fC' => ['TF' => 5000, 'BF' => 4200, 'TAF' => 800],
                    'afC' => ['TAF' => ['MF' => 120, 'MFT' => 21.6, 'AGST' => 200]],
                ]],
            ],
        ];
    }

    protected function submit(array $extra = [], array $conditions = [])
    {
        session(['flight_booking_draft' => $this->draft($conditions)]);

        return $this->post(route('flights.book'), array_merge([
            'review_booking_id' => 'TJS400000000001',
            'intent' => 'pay',
            'contact_email' => 'guest@example.com',
            'contact_dial_code' => '+91',
            'contact_phone' => '9876543210',
            'travellers' => [['title' => 'Mr', 'first_name' => 'Rahul', 'last_name' => 'Probe']],
        ], $extra));
    }

    protected function fareValid(): array
    {
        return ['status' => ['success' => true], 'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 5000]]]];
    }

    public function test_phone_helpers_build_international_numbers(): void
    {
        $this->assertSame('+919876543210', FlightBookingService::phoneFromInput('+91', '09876543210'));
        $this->assertSame('+919876543210', FlightBookingService::phoneFromInput(null, '+91 98765 43210'));
        $this->assertSame('+447700900123', FlightBookingService::phoneFromInput('44', '07700 900123'));
        $this->assertSame('+919876543210', FlightBookingService::e164('9876543210')); // older bookings
        $this->assertSame('+447700900123', FlightBookingService::e164('+447700900123'));
    }

    public function test_international_contact_number_is_sent_and_stored_with_its_country_code(): void
    {
        Http::fake([
            '*/air/book/fare-validate' => Http::response($this->fareValid()),
            '*/user-detail' => Http::response(['totalBalance' => 100000]),
        ]);

        $this->submit(['contact_dial_code' => '+44', 'contact_phone' => '07700 900123'])->assertRedirect();

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'fare-validate')
            && $r['deliveryInfo']['contacts'] === ['+447700900123']);
        $this->assertSame('+447700900123', Booking::first()->guest_phone);
    }

    public function test_indian_number_still_needs_ten_digits(): void
    {
        Http::fake();

        $this->submit(['contact_phone' => '98765'])->assertSessionHasErrors('contact_phone');
    }

    public function test_management_fee_is_stored_from_afc_taf(): void
    {
        Http::fake([
            '*/air/book/fare-validate' => Http::response($this->fareValid()),
            '*/user-detail' => Http::response(['totalBalance' => 100000]),
        ]);

        $this->submit();

        $booking = Booking::first();
        $this->assertEquals(120, (float) $booking->tripjack_mf);
        $this->assertEquals(21.6, (float) $booking->tripjack_mft);
    }

    public function test_no_payment_is_taken_when_tripjack_balance_cannot_cover_the_fare(): void
    {
        Http::fake([
            '*/air/book/fare-validate' => Http::response($this->fareValid()),
            // Real (sandbox-confirmed) shape: balances nested under user.bs.
            '*/user-detail' => Http::response(['user' => ['userId' => '413398', 'bs' => ['totalBalance' => 1200, 'walletBalance' => 1200]], 'status' => ['success' => true, 'httpStatus' => 200]]),
        ]);

        $this->submit()->assertSessionHasErrors('booking');

        $this->assertSame(0, Booking::count());
        $this->assertCount(0, $this->razorpay->orders);
    }

    public function test_seat_map_is_not_called_when_review_says_seats_are_not_applicable(): void
    {
        Http::fake();

        $this->submit(['travellers' => [['title' => 'Mr', 'first_name' => 'Rahul', 'last_name' => 'Probe', 'seats' => ['1' => '12A']]]], ['isa' => false])
            ->assertSessionHasErrors('addons');

        Http::assertNotSent(fn (Request $r) => str_ends_with($r->url(), '/seat'));
    }

    public function test_health_check_alerts_admins_when_balance_is_low(): void
    {
        Notification::fake();
        config(['services.tripjack.flight.low_balance_alert' => 25000]);
        $admin = Admin::forceCreate(['name' => 'Ops', 'email' => 'ops@example.com', 'password' => bcrypt('x'), 'role' => 'admin', 'status' => 'Active']);
        Http::fake([
            '*/user-detail' => Http::response(['user' => ['userId' => '413398', 'bs' => ['totalBalance' => 8000, 'walletBalance' => 8000]], 'status' => ['success' => true, 'httpStatus' => 200]]),
            '*' => Http::response(['status' => ['success' => true]]),
        ]);

        $this->artisan('tripjack:health-check', ['--fail-silently' => true])->assertSuccessful();
        $this->artisan('tripjack:health-check', ['--fail-silently' => true])->assertSuccessful();

        Notification::assertSentToTimes($admin, TripJackLowBalanceAlert::class, 1); // cooldown stops a repeat
    }

    public function test_no_charge_reschedule_books_without_razorpay(): void
    {
        $booking = Booking::forceCreate([
            'reference' => 'TYTRSCHED1', 'user_id' => $this->user->id, 'vertical' => 'flight', 'status' => 'confirmed',
            'tripjack_booking_id' => 'TJSOLD000001', 'guest_email' => 'guest@example.com', 'guest_phone' => '9876543210',
            'flight_route' => 'DEL-BOM', 'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0, 'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
            'flight_legs' => [['src' => 'DEL', 'dest' => 'BOM', 'departureDate' => now()->addDays(20)->toDateString()]],
        ]);
        Http::fake([
            '*/reissue/review' => Http::response([
                'bookingId' => 'TJSNEW000001', 'status' => ['success' => true],
                'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 0]]],
                'tripInfos' => [['sI' => [['dt' => now()->addDays(22)->format('Y-m-d').'T06:00']]]],
                'travellers' => [['ti' => 'Mr', 'pt' => 'ADULT', 'fN' => 'Rahul', 'lN' => 'Probe']],
            ]),
            '*/amendment/auto-reissue' => Http::response(['status' => ['success' => true]]),
            '*/booking-details' => Http::response(['order' => ['status' => 'SUCCESS'], 'itemInfos' => ['AIR' => ['travellerInfos' => [['pnrDetails' => ['DEL-BOM' => 'NEWPNR']]]]], 'status' => ['success' => true]]),
        ]);

        $this->post(route('flights.reissue.review', $booking->reference), ['price_id' => 'P1', 'leg_index' => 0])
            ->assertRedirect(route('hotel.booking.confirmation', $booking->reference));

        $this->assertCount(0, $this->razorpay->orders);
        $booking->refresh();
        $this->assertNotNull($booking->flight_reissued_at);
        $this->assertSame('TJSNEW000001', $booking->tripjack_booking_id);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'auto-reissue') && $r['deliveryInfo']['contacts'] === ['+919876543210']);
    }
}
