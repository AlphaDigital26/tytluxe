<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * "Select & Continue" on a fare that sold out since the search must return
 * the guest to the same results with an explanation — not a blank search
 * page (the reason used to live only in a one-request flash that a
 * background request from the results tab could consume first).
 */
class FlightReviewRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected string $resultsUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->resultsUrl = route('flights.search').'?trip_type=oneway&from=BOM&to=SHL&depart_date='.now()->addDays(10)->toDateString().'&adults=1';
    }

    protected function contextSession(): array
    {
        return ['flight_search_context' => ['tripType' => 'oneway', 'from' => 'BOM', 'to' => 'SHL', 'adults' => 1, 'children' => 0, 'infants' => 0]];
    }

    public function test_sold_out_fare_returns_to_the_same_results_with_a_message(): void
    {
        Http::fake(['*/review' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '1000', 'message' => 'Requested flight is no longer available. Please try different flight']]], 400)]);

        $response = $this->withSession($this->contextSession())
            ->from($this->resultsUrl)
            ->post(route('flights.review'), ['price_ids' => ['ONWARD' => 'P1']]);

        $response->assertRedirect($this->resultsUrl.'&notice=unavailable');
    }

    public function test_notice_is_shown_even_when_the_flash_message_was_already_consumed(): void
    {
        // No session flash at all — only the URL's notice code.
        Http::fake(['*/air-search-all' => Http::response(['searchResult' => ['tripInfos' => []], 'status' => ['success' => true]])]);

        $this->get($this->resultsUrl.'&notice=unavailable')
            ->assertOk()
            ->assertSee('showToast("Fare sold out"', false)
            ->assertSee('that fare just sold out with the airline', false)
            ->assertDontSee('class="frx-error"', false)
            ->assertDontSee('Enter your route and travel date above');
    }

    protected function draftSession(): array
    {
        return ['flight_booking_draft' => [
            'bookingId' => 'TJS-DRAFT-1',
            'resultsUrl' => $this->resultsUrl,
            'expiresAt' => now()->addMinutes(10)->timestamp,
            'context' => ['tripType' => 'oneway', 'from' => 'BOM', 'to' => 'SHL', 'departDate' => now()->addDays(10)->toDateString(), 'adults' => 1, 'children' => 0, 'infants' => 0, 'cabinClass' => 'ECONOMY'],
            'response' => [
                'bookingId' => 'TJS-DRAFT-1',
                'tripInfos' => [['sI' => [['id' => '1', 'fD' => ['aI' => ['code' => '6E']], 'da' => ['code' => 'BOM'], 'aa' => ['code' => 'SHL']]]]],
                'conditions' => [],
                'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 5000, 'BF' => 4000, 'TAF' => 1000]]],
            ],
        ]];
    }

    protected function payPost(): array
    {
        return [
            'intent' => 'pay', 'review_booking_id' => 'TJS-DRAFT-1', 'contact_email' => 'guest@example.com', 'contact_phone' => '9876543210',
            'travellers' => [['title' => 'Mr', 'first_name' => 'Valid', 'last_name' => 'Fare']],
        ];
    }

    public function test_a_valid_fare_does_not_leave_a_sold_out_message_behind(): void
    {
        $this->app->instance(\App\Services\Payment\RazorpayService::class, new \Tests\Doubles\FakeRazorpayService());
        Http::fake(['*/air/book/fare-validate' => Http::response(['status' => ['success' => true], 'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 5000]]]])]);

        $response = $this->withSession($this->draftSession())->post(route('flights.book'), $this->payPost());

        $response->assertRedirect(); // to the payment page
        $this->assertStringContainsString('/pay', $response->headers->get('Location'));
        $response->assertSessionMissing('booking_error');
    }

    public function test_a_sold_out_fare_at_payment_returns_to_the_results_with_the_toast(): void
    {
        Http::fake(['*/air/book/fare-validate' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '1000', 'message' => 'Requested flight is no longer available']]], 400)]);

        $response = $this->withSession($this->draftSession())->post(route('flights.book'), $this->payPost());

        $response->assertRedirect($this->resultsUrl.'&notice=unavailable');
        $this->assertDatabaseCount('bookings', 0);
    }

    protected function pendingFlightBooking(?int $fareExpiresAt): \App\Models\Booking
    {
        return \App\Models\Booking::forceCreate([
            'reference' => 'TYTPAYTEST'.random_int(1, 9),
            'user_id' => auth()->id(),
            'vertical' => 'flight',
            'status' => 'pending_payment',
            'tripjack_hold_id' => 'TJS-PAY-'.random_int(1000, 9999),
            'flight_route' => 'BOM-DEL',
            'lead_guest_name' => 'Pay Test',
            'base_amount' => 7000,
            'total_amount' => 8336.73,
            'tripjack_total_price' => 7000,
            'flight_segments_payload' => ['travellerInfo' => [], 'fareExpiresAt' => $fareExpiresAt, 'resultsUrl' => $this->resultsUrl],
        ]);
    }

    public function test_payment_page_shows_the_exact_amount_and_the_fare_hold_countdown(): void
    {
        $fake = new \Tests\Doubles\FakeRazorpayService();
        $this->app->instance(\App\Services\Payment\RazorpayService::class, $fake);
        $booking = $this->pendingFlightBooking(now()->addMinutes(8)->timestamp);

        $this->get(route('hotel.payment.show', $booking->reference))
            ->assertOk()
            ->assertSee('&#8377;8,336.73', false)
            ->assertDontSee('INR 8,337')
            ->assertSee('id="flrSessionTimer"', false)
            ->assertSee('checkout.razorpay.com', false);
        $this->assertCount(1, $fake->orders);
    }

    public function test_payment_page_refuses_to_take_payment_once_the_fare_hold_has_expired(): void
    {
        $fake = new \Tests\Doubles\FakeRazorpayService();
        $this->app->instance(\App\Services\Payment\RazorpayService::class, $fake);
        $booking = $this->pendingFlightBooking(now()->subMinute()->timestamp);

        $this->get(route('hotel.payment.show', $booking->reference))
            ->assertOk()
            ->assertSee('Fare Hold Expired')
            ->assertSee('You have not been charged.')
            ->assertSee(e($this->resultsUrl), false)
            ->assertDontSee('checkout.razorpay.com', false);
        $this->assertCount(0, $fake->orders, 'no Razorpay order is created for an expired fare');
    }

    public function test_unknown_notice_code_shows_nothing(): void
    {
        $this->get(route('flights.search').'?notice=<script>alert(1)</script>')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Enter your route and travel date above');
    }
}
