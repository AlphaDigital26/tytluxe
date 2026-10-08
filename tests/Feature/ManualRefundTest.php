<?php

namespace Tests\Feature;

use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\FlightBookings\Pages\ListFlightBookings;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\FlightBookingService;
use App\Services\Payment\RazorpayService;
use App\Support\FlightBookingStatus;
use App\Support\HotelBookingStatus;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

/**
 * A refund that fails automatically keeps the money visible (payment stays
 * captured), flags the booking for attention and keeps staff notes; staff
 * then record the refund they made in Razorpay, which clears the flag.
 */
class ManualRefundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = Admin::forceCreate(['name' => 'Priya', 'email' => 'owner@example.com', 'password' => bcrypt('x'), 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function booking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => User::factory()->create()->id,
            'vertical' => 'flight',
            'status' => 'pending_payment',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '9876543210',
            'tripjack_hold_id' => 'TJS'.random_int(100000000, 999999999),
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
            'currency' => 'INR',
            'flight_segments_payload' => ['travellerInfo' => [['ti' => 'Mr', 'pt' => 'ADULT', 'fN' => 'Rahul', 'lN' => 'Probe']]],
        ], $extra));
    }

    protected function payment(Booking $booking, string $status = 'captured', float $amount = 5600, string $purpose = 'booking'): Payment
    {
        return Payment::create([
            'booking_id' => $booking->id, 'razorpay_order_id' => 'order_'.uniqid(), 'razorpay_payment_id' => 'pay_'.uniqid(),
            'amount' => $amount, 'currency' => 'INR', 'status' => $status, 'purpose' => $purpose,
        ]);
    }

    protected function failingRazorpay(): FakeRazorpayService
    {
        $razorpay = new class extends FakeRazorpayService
        {
            public function refund(string $paymentId, ?float $amountRupees = null): array
            {
                throw new \RuntimeException('BAD_REQUEST_ERROR refund window closed');
            }
        };
        $this->app->instance(RazorpayService::class, $razorpay);

        return $razorpay;
    }

    public function test_failed_refund_keeps_the_payment_and_flags_the_booking(): void
    {
        Queue::fake();
        Http::fake(['*/air/book' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '1071', 'message' => 'Fare no longer available']]], 400)]);
        $booking = $this->booking(['admin_note' => 'Guest called about window seats.']);
        $payment = $this->payment($booking, 'created');

        app(FlightBookingService::class)->confirmAfterPayment($payment, $this->failingRazorpay());

        $booking->refresh();
        $this->assertSame('failed_needs_review', $booking->status);
        $this->assertNotNull($booking->manual_refund_due_at);
        $this->assertSame('captured', $payment->fresh()->status); // the money was taken
        $this->assertStringStartsWith('Guest called about window seats.', $booking->admin_note);
        $this->assertStringContainsString('Record manual refund', $booking->admin_note);
        $this->assertTrue(FlightBookingStatus::applyNeedsAttention(Booking::query())->whereKey($booking->id)->exists());
        $this->assertTrue($booking->hasInvoice());
    }

    public function test_recording_the_refund_clears_the_flag_and_shows_on_the_invoice(): void
    {
        $booking = $this->booking(['status' => 'failed_needs_review', 'manual_refund_due_at' => now(), 'admin_note' => 'Earlier note.']);
        $payment = $this->payment($booking);

        Livewire::test(ListFlightBookings::class)
            ->callTableAction('recordManualRefund', $booking, ['payment_id' => $payment->id, 'amount' => 5600, 'razorpay_refund_id' => 'rfnd_ABC123'])
            ->assertHasNoTableActionErrors();

        $booking->refresh();
        $this->assertSame('refunded', $booking->status);
        $this->assertNull($booking->manual_refund_due_at);
        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertEquals(5600, $payment->fresh()->refund_amount);
        $this->assertStringStartsWith('Earlier note.', $booking->admin_note);
        $this->assertStringContainsString('Priya: recorded a refund of INR 5,600.00 made in Razorpay (refund ID rfnd_ABC123)', $booking->admin_note);
        $this->assertFalse(FlightBookingStatus::applyNeedsAttention(Booking::query())->whereKey($booking->id)->exists());
        $this->assertSame(0.0, $booking->invoiceTotals()['net']);
    }

    public function test_cannot_record_more_than_was_paid(): void
    {
        $booking = $this->booking(['status' => 'failed_needs_review', 'manual_refund_due_at' => now()]);
        $payment = $this->payment($booking);

        Livewire::test(ListFlightBookings::class)
            ->callTableAction('recordManualRefund', $booking, ['payment_id' => $payment->id, 'amount' => 9000])
            ->assertHasTableActionErrors(['amount']);

        $this->assertNull($payment->fresh()->refund_amount);
    }

    public function test_hotel_cancellation_waiting_on_a_manual_refund_is_cleared(): void
    {
        $booking = $this->booking([
            'vertical' => 'hotel', 'status' => 'cancelled',
            'cancellation_reason' => 'Cancelled — the automatic refund failed and needs manual processing: timeout',
        ]);
        $payment = $this->payment($booking);
        $this->assertSame('cancelled_refund_pending', HotelBookingStatus::key($booking));

        Livewire::test(ListBookings::class)
            ->callTableAction('recordManualRefund', $booking, ['payment_id' => $payment->id, 'amount' => 3000])
            ->assertHasNoTableActionErrors();

        $booking->refresh();
        $this->assertSame('cancelled', HotelBookingStatus::key($booking));
        $this->assertStringContainsString('INR 3,000.00 has been refunded', $booking->guestCancellationMessage());
        $this->assertSame('partially_refunded', $payment->fresh()->status);
    }

    public function test_list_menu_only_offers_it_on_bookings_waiting_for_a_refund(): void
    {
        $fine = $this->booking(['status' => 'confirmed', 'tripjack_booking_id' => 'TJS1']);
        $this->payment($fine);

        Livewire::test(ListFlightBookings::class)->assertTableActionHidden('recordManualRefund', $fine);
    }

    public function test_rejected_cancellation_note_is_added_not_replacing_staff_notes(): void
    {
        $booking = $this->booking(['status' => 'confirmed', 'tripjack_booking_id' => 'TJS2', 'cancellation_requested_at' => now(), 'admin_note' => 'Guest prefers WhatsApp.']);

        app(FlightBookingService::class)->finalizeCancellation($booking, ['amendmentStatus' => 'REJECTED']);

        $note = $booking->fresh()->admin_note;
        $this->assertStringStartsWith('Guest prefers WhatsApp.', $note);
        $this->assertStringContainsString('rejected', $note);
    }
}
