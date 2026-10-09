<?php

namespace Tests\Feature;

use App\Mail\BookingUpdateMail;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Guests get an email at each booking event that matters to them,
 * whichever path changed the booking (BookingObserver).
 */
class BookingEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::fake();
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
            'tripjack_booking_id' => 'TJS'.random_int(100000000, 999999999),
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 2, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
            'currency' => 'INR',
            'flight_segments_payload' => ['travellerInfo' => [
                ['ti' => 'Mr', 'pt' => 'ADULT', 'fN' => 'Rahul', 'lN' => 'Probe'],
                ['ti' => 'Ms', 'pt' => 'ADULT', 'fN' => 'Asha', 'lN' => 'Probe'],
            ]],
        ], $extra));
    }

    protected function paid(Booking $booking, float $refund = 0): void
    {
        Payment::create([
            'booking_id' => $booking->id, 'razorpay_order_id' => 'order_'.uniqid(), 'razorpay_payment_id' => 'pay_'.uniqid(),
            'amount' => 5600, 'currency' => 'INR', 'status' => $refund ? 'refunded' : 'captured', 'purpose' => 'booking', 'refund_amount' => $refund ?: null,
        ]);
    }

    public function test_flight_email_waits_for_the_ticket_and_carries_pnr_and_ticket_numbers(): void
    {
        $booking = $this->booking();
        $this->paid($booking);

        $booking->update(['status' => 'confirmed']); // Book accepted, not ticketed yet
        Mail::assertNothingQueued();

        $booking->update([
            'tripjack_flight_pnr' => ['DEL-BOM' => 'ABC123'],
            'tripjack_flight_ticket_numbers' => [
                ['name' => 'Mr Rahul Probe', 'tickets' => ['DEL-BOM' => '0981234567890']],
                ['name' => 'Ms Asha Probe', 'tickets' => ['DEL-BOM' => '0981234567891']],
            ],
        ]);

        Mail::assertQueued(BookingUpdateMail::class, function (BookingUpdateMail $mail) {
            $html = $mail->render();

            return $mail->hasTo('guest@example.com') && $mail->kind === BookingUpdateMail::CONFIRMED
                && str_contains($html, 'ABC123') && str_contains($html, '0981234567890') && str_contains($html, '0981234567891')
                && count($mail->attachments()) === 1;
        });
        Mail::assertQueuedCount(1);
    }

    public function test_rescheduled_flight_gets_its_new_details(): void
    {
        $booking = $this->booking(['status' => 'confirmed', 'flight_reissued_at' => now(), 'tripjack_flight_pnr' => null]);

        $booking->update(['tripjack_flight_pnr' => ['DEL-BOM' => 'NEW999']]);

        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->kind === BookingUpdateMail::RESCHEDULED && str_contains($mail->render(), 'NEW999'));
    }

    public function test_hotel_confirmation_email(): void
    {
        $destination = Destination::create(['name' => 'Dubai', 'slug' => 'dubai-mail', 'country' => 'UAE', 'type' => 'city', 'for' => ['hotel'], 'is_active' => true]);
        $hotel = Hotel::create(['destination_id' => $destination->id, 'title' => 'Palm Mail Resort', 'slug' => 'palm-mail', 'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai', 'star_rating' => 5, 'price_from' => 0, 'source' => 'tripjack', 'tripjack_hotel_id' => 'H1', 'is_active' => true]);
        $booking = $this->booking(['vertical' => 'hotel', 'hotel_id' => $hotel->id, 'status' => 'pending_confirmation', 'check_in' => now()->addDays(30), 'check_out' => now()->addDays(32), 'room_name' => 'Deluxe King']);
        $this->paid($booking);

        $booking->update(['status' => 'confirmed']);

        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->kind === BookingUpdateMail::CONFIRMED
            && str_contains($mail->render(), 'Palm Mail Resort') && str_contains($mail->render(), 'Deluxe King'));
    }

    public function test_cancellation_email_uses_the_guest_wording_not_the_staff_reason(): void
    {
        $booking = $this->booking(['status' => 'confirmed']);
        $this->paid($booking);

        $booking->update(['status' => 'cancelled', 'cancellation_reason' => 'Cancelled — refund failed automatically and needs manual processing: BAD_REQUEST_ERROR']);

        Mail::assertQueued(BookingUpdateMail::class, function ($mail) {
            $html = $mail->render();

            return $mail->kind === BookingUpdateMail::CANCELLED
                && str_contains($html, 'Our team is processing your refund')
                && ! str_contains($html, 'BAD_REQUEST_ERROR');
        });
    }

    public function test_failed_booking_refund_and_hold_emails(): void
    {
        $failed = $this->booking(['status' => 'confirmed']);
        $this->paid($failed, 5600);
        $failed->update(['status' => 'refunded']);
        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->kind === BookingUpdateMail::FAILED_REFUNDED && $mail->booking->is($failed));

        $hold = $this->booking(['tripjack_hold_expires_at' => now()->addHours(6)]);
        $hold->update(['status' => 'on_hold']);
        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->kind === BookingUpdateMail::HELD && str_contains($mail->render(), 'Pay before'));

        $hold->update(['tripjack_hold_expires_at' => now()->subMinute()]);
        $this->artisan('flights:expire-holds');
        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->kind === BookingUpdateMail::HOLD_EXPIRED && $mail->booking->is($hold));
    }

    public function test_team_gets_a_hidden_copy(): void
    {
        config(['services.booking_emails.team_copy' => 'team@tytluxe.test']);
        $booking = $this->booking(['status' => 'confirmed']);
        $booking->update(['status' => 'cancelled']);

        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->hasTo('guest@example.com') && $mail->hasBcc('team@tytluxe.test'));

        // No guest email on file: the team still hears about it.
        $noEmail = $this->booking(['status' => 'confirmed', 'guest_email' => null]);
        $noEmail->update(['status' => 'cancelled']);
        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->booking->is($noEmail) && $mail->hasTo('team@tytluxe.test'));

        // Turned off.
        config(['services.booking_emails.team_copy' => '']);
        $off = $this->booking(['status' => 'confirmed']);
        $off->update(['status' => 'cancelled']);
        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->booking->is($off) && ! $mail->hasBcc('team@tytluxe.test'));
    }

    public function test_guest_replies_go_to_the_team_inbox_not_the_noreply_sender(): void
    {
        config(['services.booking_emails.reply_to' => 'help@tytluxe.test']);
        $booking = $this->booking(['status' => 'confirmed']);
        $booking->update(['status' => 'cancelled']);

        Mail::assertQueued(BookingUpdateMail::class, fn ($mail) => $mail->hasReplyTo('help@tytluxe.test'));

        config(['services.booking_emails.reply_to' => '']);
        $this->assertSame([], (new BookingUpdateMail($booking, BookingUpdateMail::CANCELLED))->envelope()->replyTo);
    }

    public function test_no_email_for_review_states_or_staff_notes(): void
    {
        $booking = $this->booking(['status' => 'confirmed']);

        $booking->update(['status' => 'failed_needs_review']);
        $booking->update(['admin_note' => 'Called the guest.']);

        Mail::assertNothingQueued();
    }
}
