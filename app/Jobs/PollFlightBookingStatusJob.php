<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\FlightBookingService;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackFlightClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Follows a flight booking through to TripJack's final order status after
 * Book / Confirm-Book. TripJack's Booking Flow doc: "Booking Detail should be
 * called after 5 seconds elapsed" — Book's own response only says the
 * request was accepted, and the status is usually still PENDING right
 * after. Dispatched with a 5s delay, then self-requeues until SUCCESS /
 * FAILED / ABORTED (FlightBookingService::applyBookingDetails() acts on it).
 *
 * $verifyUnconfirmed is set when the Book / Confirm-Book call itself timed
 * out or 5xx'd: we don't know whether TripJack ticketed it, so the booking
 * sits in failed_needs_review (no refund yet) until this job finds out —
 * SUCCESS confirms it, FAILED/ABORTED or "booking not found" refunds it.
 */
class PollFlightBookingStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 90;

    protected const FAST_ATTEMPTS = 12; // every 10s — ~2 minutes

    protected const SLOW_POLL_MINUTES = 10;

    protected const MAX_SLOW_ATTEMPTS = 18; // 3 hours

    // Doc error code 1057 — "Booking not found".
    protected const BOOKING_NOT_FOUND = '1057';

    public function __construct(
        protected int $bookingId,
        protected int $attempt = 1,
        protected bool $verifyUnconfirmed = false,
    ) {}

    public function handle(TripJackFlightClient $client, FlightBookingService $flights, RazorpayService $razorpay): void
    {
        $booking = Booking::find($this->bookingId);
        if (! $booking || ! $booking->tripjack_booking_id) {
            return;
        }

        // Something else already settled it (the confirmation page's own
        // polling, a refund, a cancellation) — nothing left to do.
        $expectedStatus = $this->verifyUnconfirmed ? 'failed_needs_review' : 'confirmed';
        if ($booking->status !== $expectedStatus) {
            return;
        }

        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
        } catch (TripJackApiException $e) {
            // Only a Book that timed out can legitimately be missing on
            // TripJack's side — and only conclude "never booked" once the
            // fast polls are over, so a slow TripJack write isn't refunded.
            if ($this->verifyUnconfirmed && (string) $e->errorCode === self::BOOKING_NOT_FOUND && $this->attempt >= self::FAST_ATTEMPTS) {
                $flights->refundUnconfirmedBooking($booking, $razorpay, 'TripJack has no record of this booking after the Book request timed out.');

                return;
            }
            $this->requeueOrGiveUp($booking, $e->getMessage());

            return;
        } catch (TripJackException $e) {
            $this->requeueOrGiveUp($booking, $e->getMessage());

            return;
        }

        $resolved = $flights->applyBookingDetails($booking, $details, $razorpay, resolvingUncertain: $this->verifyUnconfirmed);

        if (! $resolved) {
            $this->requeueOrGiveUp($booking, 'order.status '.($details['order']['status'] ?? 'missing'));
        }
    }

    protected function requeueOrGiveUp(Booking $booking, string $lastSeen): void
    {
        if ($this->attempt >= self::FAST_ATTEMPTS + self::MAX_SLOW_ATTEMPTS) {
            $booking->update(['admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '')
                ."Flight booking {$booking->tripjack_booking_id} still has no final TripJack status after 3h of automatic checks (last: {$lastSeen}) — check with TripJack support and confirm or refund manually.")]);
            Log::channel('tripjack')->critical('flight_booking_status_poll_abandoned', [
                'booking_id' => $booking->id, 'tripjack_booking_id' => $booking->tripjack_booking_id,
                'verifyUnconfirmed' => $this->verifyUnconfirmed, 'lastSeen' => $lastSeen,
            ]);

            return;
        }

        $delay = $this->attempt >= self::FAST_ATTEMPTS ? now()->addMinutes(self::SLOW_POLL_MINUTES) : now()->addSeconds(10);

        self::dispatch($this->bookingId, $this->attempt + 1, $this->verifyUnconfirmed)->delay($delay);
    }
}
