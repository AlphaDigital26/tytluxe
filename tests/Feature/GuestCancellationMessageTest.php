<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * cancellation_reason is written for staff ("needs manual review", raw
 * Razorpay errors). Guests see Booking::guestCancellationMessage() instead.
 */
class GuestCancellationMessageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Http::fake();
    }

    protected function booking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => $this->user->id,
            'vertical' => 'flight',
            'status' => 'cancelled',
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
            'currency' => 'INR',
        ], $extra));
    }

    public function test_refund_failure_details_are_never_shown_to_the_guest(): void
    {
        $booking = $this->booking(['cancellation_reason' => 'Cancelled — refund failed automatically and needs manual processing: BAD_REQUEST_ERROR The amount exceeds the captured amount']);

        $this->actingAs($this->user)
            ->get(route('hotel.booking.confirmation', $booking->reference))
            ->assertOk()
            ->assertSee('Our team is processing your refund')
            ->assertDontSee('BAD_REQUEST_ERROR')
            ->assertDontSee('manual processing');

        $this->actingAs($this->user)
            ->get(route('history'))
            ->assertOk()
            ->assertDontSee('BAD_REQUEST_ERROR');
    }

    public function test_messages_follow_what_actually_happened(): void
    {
        $refunded = $this->booking(['cancellation_reason' => 'Cancelled. Refunded INR 4200 automatically.']);
        Payment::create(['booking_id' => $refunded->id, 'razorpay_order_id' => 'o1', 'razorpay_payment_id' => 'p1', 'amount' => 5600, 'currency' => 'INR', 'status' => 'partially_refunded', 'refund_amount' => 4200]);
        $this->assertStringContainsString('INR 4,200.00 has been refunded', $refunded->guestCancellationMessage());

        $this->assertStringContainsString('no refund is due', $this->booking(['vertical' => 'hotel', 'cancellation_reason' => 'Cancelled with a cancellation penalty of INR 5000.00 — no refund due.'])->guestCancellationMessage());
        $this->assertStringContainsString('no payment was taken', $this->booking(['cancellation_reason' => 'Hold released by guest before payment.'])->guestCancellationMessage());
        $this->assertStringContainsString('being processed', $this->booking(['status' => 'confirmed', 'cancellation_requested_at' => now(), 'cancellation_reason' => 'Cancellation requested — TripJack reports status: PENDING. Refund not yet processed.'])->guestCancellationMessage());
        $this->assertNull($this->booking(['status' => 'confirmed'])->guestCancellationMessage());
    }
}
