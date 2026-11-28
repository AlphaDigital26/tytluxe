<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Invoices: only once money was taken, flight wording and passengers on a
 * flight invoice, and totals from the actual payments (later add-ons,
 * reschedule charges, refunds).
 */
class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Http::fake();
    }

    protected function flightBooking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => $this->user->id,
            'vertical' => 'flight',
            'status' => 'confirmed',
            'guest_email' => 'guest@example.com',
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 1,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000, 'tax_amount' => 600, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
            'currency' => 'INR',
            'flight_segments_payload' => ['travellerInfo' => [
                ['ti' => 'Mr', 'pt' => 'ADULT', 'fN' => 'Rahul', 'lN' => 'Probe'],
                ['ti' => 'Master', 'pt' => 'INFANT', 'fN' => 'Kabir', 'lN' => 'Probe'],
            ]],
        ], $extra));
    }

    protected function pay(Booking $booking, float $amount, string $status = 'captured', string $purpose = 'booking', float $refund = 0): Payment
    {
        return Payment::create([
            'booking_id' => $booking->id, 'razorpay_order_id' => 'order_'.uniqid(), 'razorpay_payment_id' => 'pay_'.uniqid(),
            'amount' => $amount, 'currency' => 'INR', 'status' => $status, 'purpose' => $purpose, 'refund_amount' => $refund ?: null,
        ]);
    }

    public function test_no_invoice_until_the_guest_has_paid(): void
    {
        $this->actingAs($this->user);

        foreach (['on_hold', 'hold_expired', 'cancelled'] as $status) {
            $hold = $this->flightBooking(['status' => $status]);
            $this->get(route('hotel.booking.invoice', $hold->reference))->assertNotFound();
            $this->get(route('hotel.booking.confirmation', $hold->reference))->assertDontSee('Download Invoice');
        }

        $paid = $this->flightBooking();
        $this->pay($paid, 5600);
        $this->get(route('hotel.booking.invoice', $paid->reference))->assertOk();
        $this->get(route('hotel.booking.confirmation', $paid->reference))->assertSee('Download Invoice');
    }

    public function test_flight_invoice_lists_passengers_and_uses_airline_terms(): void
    {
        $booking = $this->flightBooking();
        $this->pay($booking, 5600);

        $html = view('pdf.invoice', ['booking' => $booking->load('travelers')])->render();

        $this->assertStringContainsString('Mr Rahul Probe', $html);
        $this->assertStringContainsString('Master Kabir Probe', $html);
        $return = view('pdf.invoice', ['booking' => $booking->fresh()->forceFill(['flight_journey_type' => 'RETURN'])->load('travelers')])->render();
        $this->assertStringContainsString('Round Trip', $return);
        $this->assertStringNotContainsString('>Return<', $return);

        // Passengers as "1A 1I"; no fare break-up, only what was paid.
        $this->assertStringContainsString('1A 1I', $html);
        $this->assertStringContainsString('Net Amount Paid', $html);
        $this->assertStringNotContainsString('Air Fare (incl. airline taxes)', $html);
        $this->assertStringNotContainsString('Service &amp; Convenience Fee', $html);
        $this->assertStringNotContainsString('Management Fee', $html);
        $this->assertStringContainsString('operating airline', $html);
        $this->assertStringNotContainsString('Room Charges', $html);
        $this->assertStringNotContainsString('the hotel remains', $html);
    }

    /** Same split as TripJack's own invoice (DEL-BOM-DEL round trip, live UAT numbers). */
    public function test_pax_fares_add_up_every_trip_like_tripjacks_invoice(): void
    {
        $domestic = ['da' => ['code' => 'DEL', 'countryCode' => 'IN'], 'aa' => ['code' => 'BOM', 'countryCode' => 'IN']];
        $trip = fn (float $bf, float $tf, float $yq, float $k3, float $ibf, float $itf) => ['sI' => [$domestic, $domestic + ['sN' => 1]], 'totalPriceList' => [['fd' => [
            'ADULT' => ['fC' => ['BF' => $bf, 'TF' => $tf], 'afC' => ['TAF' => ['MF' => 75, 'YQ' => $yq, 'AGST' => $k3, 'MFT' => 13.5]]],
            'INFANT' => ['fC' => ['BF' => $ibf, 'TF' => $itf], 'afC' => ['TAF' => ['MF' => 75, 'MFT' => 13.5]]],
        ]]]];
        $review = ['tripInfos' => [$trip(3231, 4929.5, 850, 208, 3000, 3088.5), $trip(2000, 3668.5, 850, 146, 3000, 3088.5)]];

        // Each trip is one flight with a connection: ₹250 per adult per flight, ₹100 per infant.
        $this->assertSame([
            'ADULT' => ['base' => 5231.0, 'tax' => 1313.0, 'k3' => 354.0, 'yq' => 1700.0, 'total' => 8598.0, 'markup' => 500.0],
            'INFANT' => ['base' => 6000.0, 'tax' => 177.0, 'k3' => 0.0, 'yq' => 0.0, 'total' => 6177.0, 'markup' => 200.0],
        ], \App\Services\FlightBookingService::paxFares($review));
    }

    public function test_flight_invoice_shows_each_passengers_fare_and_ticket(): void
    {
        // 8598 + 6177 + 400 of seats, through the real pricing formula.
        $price = \App\Services\FlightPricingService::price(15175, 700);
        $booking = $this->flightBooking([
            'base_amount' => 15175, 'tripjack_total_price' => 15175,
            'tax_amount' => round($price['customer_price'] - 15175, 2), 'total_amount' => $price['customer_price'],
            'margin_amount' => $price['margin_amount'], 'gst_on_margin' => $price['gst_on_margin'],
            'razorpay_recovery' => $price['razorpay_recovery'],
            'management_fee' => $price['management_fee'], 'management_fee_gst' => $price['management_fee_gst'],
            'tripjack_flight_pnr' => ['Y8WDGK'],
            'tripjack_flight_ticket_numbers' => [['name' => 'Mr Rahul Probe', 'tickets' => ['DEL-BOM' => '0981234567890']]],
            'flight_segments_payload' => [
                'travellerInfo' => [
                    ['ti' => 'Mr', 'pt' => 'ADULT', 'fN' => 'Rahul', 'lN' => 'Probe'],
                    ['ti' => 'Master', 'pt' => 'INFANT', 'fN' => 'Kabir', 'lN' => 'Probe'],
                ],
                'paxFares' => [
                    'ADULT' => ['base' => 5231.0, 'tax' => 1313.0, 'k3' => 354.0, 'yq' => 1700.0, 'total' => 8598.0, 'markup' => 500.0],
                    'INFANT' => ['base' => 6000.0, 'tax' => 177.0, 'k3' => 0.0, 'yq' => 0.0, 'total' => 6177.0, 'markup' => 200.0],
                ],
            ],
        ]);
        $this->pay($booking, (float) $booking->total_amount);

        $rows = $booking->flightFareRows();
        $this->assertSame([8598.0, 6177.0, 400.0], array_column($rows, 'base'));
        $this->assertTrue($rows[2]['addons']);
        // Adult: ₹500 markup + 5% GST + its Razorpay share + half of ₹118.
        $this->assertEqualsWithDelta(500 * 1.05 + $price['razorpay_recovery'] * (8598 + 525) / (15175 + 735) + 59, $rows[0]['other'], 0.02);
        // Every line adds up to exactly what was charged.
        $this->assertSame((float) $booking->tax_amount, round(array_sum(array_column($rows, 'other')), 2));
        $this->assertSame((float) $booking->total_amount, round(array_sum(array_column($rows, 'total')), 2));

        $html = view('pdf.invoice', ['booking' => $booking->load('travelers')])->render();

        $this->assertStringContainsString('Passengers &amp; Fare Details', $html);
        $this->assertStringContainsString('Other Taxes (incl. GST)', $html);
        $this->assertStringContainsString('0981234567890', $html);
        $this->assertStringContainsString('Y8WDGK', $html);
        $this->assertStringContainsString(number_format($rows[0]['total'], 2), $html);
        $this->assertStringContainsString(number_format((float) $booking->total_amount, 2), $html);
    }

    public function test_totals_include_later_payments_and_refunds(): void
    {
        $booking = $this->flightBooking(['status' => 'cancelled']);
        $this->pay($booking, 5600, 'partially_refunded', 'booking', 4200);
        $this->pay($booking, 900, 'captured', 'flight_ssr');
        $this->pay($booking, 1500, 'captured', 'flight_reissue');
        $this->pay($booking, 700, 'failed', 'flight_ssr'); // never taken

        $this->assertSame(['extras' => ['Seats, meals & baggage added later' => 900.0, 'Reschedule charges' => 1500.0], 'paid' => 8000.0, 'refunded' => 4200.0, 'net' => 3800.0], $booking->invoiceTotals());

        $html = view('pdf.invoice', ['booking' => $booking->load('travelers')])->render();
        $this->assertStringContainsString('Seats, meals &amp; baggage added later', $html);
        $this->assertStringContainsString('INR 8,000.00', $html);
        $this->assertStringContainsString('INR 4,200.00', $html);
        $this->assertStringContainsString('INR 3,800.00', $html);
    }
}
