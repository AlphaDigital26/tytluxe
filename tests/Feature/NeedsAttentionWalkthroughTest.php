<?php

namespace Tests\Feature;

use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Filament\Resources\FlightBookings\Pages\ViewFlightBooking;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\User;
use App\Support\DashboardData;
use App\Support\FlightBookingStatus;
use App\Support\HotelBookingStatus;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fixes from working through the dashboard's "Needs your attention" as a
 * staff member (29 Oct 2026).
 */
class NeedsAttentionWalkthroughTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(Admin::forceCreate(['name' => 'Priya Shah', 'email' => 'owner@example.com', 'password' => bcrypt('x'), 'role' => 'Super Admin', 'status' => 'Active']), 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function booking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => User::factory()->create()->id, 'vertical' => 'flight', 'status' => 'pending_payment',
            'guest_email' => 'guest@example.com', 'guest_phone' => '9876543210', 'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(), 'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe', 'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000, 'currency' => 'INR',
        ], $extra));
    }

    protected function payment(Booking $booking, string $status = 'captured', float $amount = 5600): Payment
    {
        return Payment::create(['booking_id' => $booking->id, 'razorpay_order_id' => 'order_'.uniqid(), 'razorpay_payment_id' => 'pay_'.uniqid(),
            'amount' => $amount, 'currency' => 'INR', 'status' => $status, 'purpose' => 'booking']);
    }

    public function test_dashboard_buttons_open_the_right_tab(): void
    {
        Enquiry::factory()->create();
        $failed = $this->booking(['status' => 'failed_needs_review', 'manual_refund_due_at' => now()]);
        $this->payment($failed);

        $urls = collect(DashboardData::todo())->pluck('url')->filter()->implode(' ');
        $this->assertStringContainsString('/enquiries?tab=new', $urls);
        $this->assertStringContainsString('/flight-bookings?tab=attention', $urls);
        $this->assertStringNotContainsString('activeTab', $urls);
    }

    public function test_failed_bookings_say_exactly_what_to_do(): void
    {
        // Booking failed and the automatic refund failed too.
        $refundDue = $this->booking(['status' => 'failed_needs_review', 'manual_refund_due_at' => now()]);
        $this->payment($refundDue);
        $this->assertSame('Booking failed, refund needs action', FlightBookingStatus::for($refundDue)['label']);

        // Booking failed and the guest was never charged: nothing to do, not on the list.
        $notCharged = $this->booking(['vertical' => 'hotel', 'status' => 'failed_needs_review']);
        $this->payment($notCharged, 'failed');
        $this->assertSame('Did not go through, nothing charged', HotelBookingStatus::for($notCharged->load('payments'))['label']);
        $this->assertSame(0, HotelBookingStatus::applyNeedsAttention(Booking::where('vertical', 'hotel'))->count());

        // Paid but TripJack never sent a number: no "Check status" button to point at.
        $noId = $this->booking(['status' => 'failed_needs_review']);
        $this->payment($noId);
        $status = FlightBookingStatus::for($noId->load('payments'));
        $this->assertStringNotContainsString('Check status', $status['help']);
        $this->assertStringContainsString('contact TripJack support', $status['help']);
    }

    public function test_record_manual_refund_is_a_visible_button_and_fills_the_amount_only_for_a_failed_booking(): void
    {
        $failed = $this->booking(['status' => 'failed_needs_review', 'manual_refund_due_at' => now(), 'tripjack_booking_id' => 'TJS1']);
        $this->payment($failed);

        Livewire::test(ViewFlightBooking::class, ['record' => $failed->getRouteKey()])
            ->assertActionVisible('recordManualRefund')
            ->mountAction('recordManualRefund')
            ->assertSchemaStateSet(['amount' => 5600], 'mountedActionSchema0');

        // A cancellation where the airline kept charges: staff enter the amount from the notes.
        $cancelled = $this->booking(['status' => 'cancelled', 'cancellation_reason' => 'Cancelled — refund failed automatically and needs manual processing', 'manual_refund_due_at' => now(), 'tripjack_booking_id' => 'TJS2']);
        $this->payment($cancelled);
        Livewire::test(ViewFlightBooking::class, ['record' => $cancelled->getRouteKey()])
            ->mountAction('recordManualRefund')
            ->assertSchemaStateSet(['amount' => null], 'mountedActionSchema0');
    }

    public function test_check_status_on_a_stuck_cancellation_asks_about_the_cancellation(): void
    {
        $booking = $this->booking(['status' => 'confirmed', 'tripjack_booking_id' => 'TJS9', 'tripjack_flight_pnr' => ['ABC123'],
            'cancellation_requested_at' => now()->subDays(3), 'admin_note' => 'Flight CANCELLATION amendment (0403169258) still unresolved after 6 quick checks.']);
        $this->payment($booking);
        Http::fake(['*/amendment-details*' => Http::response(['status' => ['success' => true], 'amendmentStatus' => 'REQUESTED'])]);

        Livewire::test(ViewFlightBooking::class, ['record' => $booking->getRouteKey()])
            ->callAction('checkStatus')
            ->assertNotified('Cancellation still waiting at TripJack');

        Http::assertSent(fn ($r) => str_contains($r->url(), 'amendment-details') && str_contains((string) $r->body(), '0403169258'));
    }

    public function test_tripjack_having_no_booking_is_explained_and_moves_to_refund(): void
    {
        $booking = $this->booking(['status' => 'failed_needs_review', 'tripjack_booking_id' => 'TJS404']);
        $this->payment($booking);
        Http::fake(['*/booking-details' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '2005', 'message' => 'No order found with given orderId']]], 400)]);

        Livewire::test(ViewFlightBooking::class, ['record' => $booking->getRouteKey()])
            ->callAction('checkStatus')
            ->assertNotified('TripJack has no booking under this number');

        $booking->refresh();
        $this->assertNotNull($booking->manual_refund_due_at);
        $this->assertSame('Booking failed, refund needs action', FlightBookingStatus::for($booking)['label']);
    }

    public function test_closing_an_enquiry_keeps_earlier_notes_and_confirms(): void
    {
        $enquiry = Enquiry::factory()->create(['admin_notes' => 'Called twice, no answer.']);

        Livewire::test(ListEnquiries::class)
            ->callTableAction('resolve', $enquiry, ['admin_notes' => 'Guest not interested'])
            ->assertNotified('Enquiry closed');

        $notes = $enquiry->fresh()->admin_notes;
        $this->assertStringContainsString('Called twice, no answer.', $notes);
        $this->assertStringContainsString('Closed by Priya Shah: Guest not interested', $notes);
        $this->assertSame('closed', $enquiry->fresh()->status);
    }
}
