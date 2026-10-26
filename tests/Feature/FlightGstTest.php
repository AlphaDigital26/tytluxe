<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Rules\Gstin;
use App\Services\Payment\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

/**
 * Fixes from the TripJack flight UAT (23 Oct 2026). GST: Book needs a real
 * GSTIN plus the company's address, email and mobile — without them TripJack
 * refused the booking after the guest had paid (errors 805/819/800/804).
 */
class FlightGstTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(RazorpayService::class, new FakeRazorpayService());
        $this->actingAs(User::factory()->create());
        Http::fake([
            '*/air/book/fare-validate' => Http::response(['status' => ['success' => true], 'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 5000]]]]),
            '*/user-detail' => Http::response(['totalBalance' => 100000]),
        ]);
    }

    public function test_passport_fares_that_need_a_date_of_birth_ask_for_it_before_payment(): void
    {
        // International fare: passport mandatory with DOB (pcs.dobe), but no dob flags.
        $conditions = ['pcs' => ['pm' => true, 'dobe' => true, 'pped' => true, 'pid' => true]];
        $passport = ['passport_number' => 'Z4123456', 'passport_expiry' => '2032-06-30', 'passport_nationality' => 'IN', 'passport_issue_date' => '2022-07-01'];

        $this->submit(['travellers' => [['title' => 'Mr', 'first_name' => 'Arjun', 'last_name' => 'Malhotra'] + $passport]], $conditions)
            ->assertSessionHasErrors('travellers.0.dob');
        $this->assertSame(0, Booking::count());

        $this->submit(['travellers' => [['title' => 'Mr', 'first_name' => 'Arjun', 'last_name' => 'Malhotra', 'dob' => '1985-05-10'] + $passport]], $conditions)
            ->assertRedirect();
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'fare-validate') && ($r['travellerInfo'][0]['dob'] ?? null) === '1985-05-10');
    }

    public function test_a_fare_over_the_payment_limit_leaves_no_unpaid_booking(): void
    {
        $this->app->instance(RazorpayService::class, new class extends FakeRazorpayService
        {
            public function createOrder(float $amountRupees, string $receipt): array
            {
                throw new \App\Services\Payment\PaymentOrderException(\App\Services\Payment\PaymentOrderException::OVER_LIMIT_MESSAGE);
            }
        });

        $this->submit([])->assertRedirect(route('flights.confirm.show'))
            ->assertSessionHasErrors(['intent' => \App\Services\Payment\PaymentOrderException::OVER_LIMIT_MESSAGE]);
        $this->assertSame(0, Booking::count());
        $this->assertNotNull(session('flight_booking_draft'));
    }

    public function test_there_is_no_hold_every_booking_goes_to_payment(): void
    {
        // Even a crafted "hold" request is treated as Proceed to Pay: a
        // booking awaiting payment, and nothing reserved with TripJack.
        $response = $this->submit(['intent' => 'hold']);
        $booking = Booking::first();
        $response->assertRedirect(route('hotel.payment.show', $booking->reference));
        $this->assertSame('pending_payment', $booking->status);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/air/book') && ! str_contains($r->url(), 'fare-validate'));
    }

    public function test_second_pay_click_goes_to_the_same_payment_page(): void
    {
        $first = $this->submit([]);
        $booking = Booking::first();
        $first->assertRedirect(route('hotel.payment.show', $booking->reference));

        // The draft is gone after the first click; the second lands on the same payment page.
        $this->post(route('flights.book'), ['review_booking_id' => 'TJS400000000009', 'intent' => 'pay'])
            ->assertRedirect(route('hotel.payment.show', $booking->reference));
        $this->assertSame(1, Booking::count());

        // Once paid, it opens the booking instead.
        $booking->update(['status' => 'confirmed']);
        $this->post(route('flights.book'), ['review_booking_id' => 'TJS400000000009', 'intent' => 'pay'])
            ->assertRedirect(route('hotel.booking.confirmation', $booking->reference));

        // Another account's fare id never reveals someone else's booking.
        $this->actingAs(User::factory()->create())->post(route('flights.book'), ['review_booking_id' => 'TJS400000000009', 'intent' => 'pay'])
            ->assertRedirect(route('flights.search'));
    }

    private function submit(array $extra, array $conditions = [])
    {
        $segment = ['id' => '1', 'da' => ['code' => 'DEL'], 'aa' => ['code' => 'BOM'], 'fD' => ['aI' => ['code' => '6E'], 'fN' => '101'], 'dt' => '2026-12-01T06:00', 'at' => '2026-12-01T08:10'];
        session(['flight_booking_draft' => [
            'bookingId' => 'TJS400000000009', 'resultsUrl' => route('flights.search'), 'expiresAt' => now()->addMinutes(10)->timestamp,
            'context' => ['tripType' => 'oneway', 'from' => 'DEL', 'to' => 'BOM', 'departDate' => '2026-12-01', 'returnDate' => '',
                'adults' => 1, 'children' => 0, 'infants' => 0, 'cabinClass' => 'ECONOMY', 'fareType' => 'REGULAR', 'legs' => []],
            'response' => ['bookingId' => 'TJS400000000009', 'tripInfos' => [['sI' => [$segment]]], 'conditions' => $conditions,
                'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 5000, 'BF' => 4200, 'TAF' => 800]]]],
        ]]);

        return $this->post(route('flights.book'), array_merge([
            'review_booking_id' => 'TJS400000000009', 'intent' => 'pay',
            'contact_email' => 'guest@gmail.com', 'contact_dial_code' => '+91', 'contact_phone' => '9876543210',
            'travellers' => [['title' => 'Mr', 'first_name' => 'Arjun', 'last_name' => 'Malhotra']],
        ], $extra));
    }

    public function test_gstin_check_digit_is_verified(): void
    {
        $this->assertTrue(Gstin::isValid('27AAPFU0939F1ZV'));
        $this->assertTrue(Gstin::isValid('29aagcb7383j1z4'));
        foreach (['07AABCT1234L1Z5', '27AAPFU0939F1ZW', '27AAPFU0939F1Z', '99AAPFU0939F1ZV', ''] as $bad) {
            $this->assertFalse(Gstin::isValid($bad), $bad);
        }
    }

    public function test_an_invalid_gstin_or_missing_address_is_refused_before_payment(): void
    {
        $this->submit(['gst_number' => '07AABCT1234L1Z5', 'gst_registered_name' => 'Acme Pvt Ltd', 'gst_address' => '12 MG Road, Pune 411001'])
            ->assertSessionHasErrors('gst_number');
        $this->submit(['gst_number' => '27AAPFU0939F1ZV', 'gst_registered_name' => 'Acme Pvt Ltd'])
            ->assertSessionHasErrors('gst_address');

        $this->assertSame(0, Booking::count());
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'fare-validate'));
    }

    public function test_gst_details_sent_to_tripjack_include_address_email_and_mobile(): void
    {
        $this->submit(['gst_number' => '27aapfu0939f1zv', 'gst_registered_name' => 'Acme Pvt Ltd', 'gst_address' => "12 MG Road,\n Pune 411001"])
            ->assertRedirect();

        $expected = ['gstNumber' => '27AAPFU0939F1ZV', 'registeredName' => 'Acme Pvt Ltd', 'address' => '12 MG Road, Pune 411001',
            'email' => 'guest@gmail.com', 'mobile' => '9876543210'];
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'fare-validate') && $r['gstInfo'] === $expected);
        $this->assertSame($expected, Booking::first()->tripjack_gst_info);
    }
}
