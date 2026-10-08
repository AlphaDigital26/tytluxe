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
        $this->assertStringContainsString('Air Fare (incl. airline taxes)', $html);
        $this->assertStringContainsString('operating airline', $html);
        $this->assertStringNotContainsString('Room Charges', $html);
        $this->assertStringNotContainsString('the hotel remains', $html);
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
