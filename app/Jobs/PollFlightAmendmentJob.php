<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\FlightBookingService;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackFlightClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * TripJack's amendment-details endpoint is async — submit-amendment returns
 * an amendmentId immediately, but the real outcome (SUCCESS/REJECTED) can
 * take TripJack's own doc-recommended "4-5 polls, 10 seconds apart" to
 * settle. A synchronous web request can't block that long, so this job owns
 * the polling loop instead — self-requeuing with a 10s delay until it
 * resolves or hits the attempt cap, at which point the booking is left for
 * manual follow-up rather than silently stuck.
 */
class PollFlightAmendmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    protected const MAX_ATTEMPTS = 6; // doc says 4-5x; one extra for margin

    // After the fast polls, keep checking slowly so a late answer from
    // TripJack (e.g. once their support team acts on it) still finalises
    // the booking and refunds the guest, instead of leaving it stuck.
    protected const SLOW_POLL_MINUTES = 30;

    protected const MAX_SLOW_ATTEMPTS = 48; // 24 hours

    public function __construct(
        protected int $bookingId,
        protected string $amendmentId,
        protected int $attempt = 1,
        protected bool $isFullBooking = true,
        protected string $type = 'CANCELLATION',
    ) {}

    public function handle(TripJackFlightClient $client, FlightBookingService $flights): void
    {
        $booking = Booking::find($this->bookingId);
        if (! $booking) {
            return;
        }

        try {
            $details = $client->amendmentDetails($this->amendmentId);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('flight_amendment_poll_failed', [
                'booking_id' => $this->bookingId, 'amendmentId' => $this->amendmentId, 'attempt' => $this->attempt, 'message' => $e->getMessage(),
            ]);
            $this->requeueOrGiveUp($booking);

            return;
        }

        $status = $details['amendmentStatus'] ?? null;

        if (in_array($status, ['SUCCESS', 'REJECTED'], true)) {
            $flights->finalizeCancellation($booking, $details, $this->isFullBooking, $this->type);

            return;
        }

        // Still REQUESTED/PENDING.
        $this->requeueOrGiveUp($booking, $details);
    }

    protected function requeueOrGiveUp(Booking $booking, array $lastDetails = []): void
    {
        if ($this->attempt === self::MAX_ATTEMPTS) {
            // Doc: still REQUESTED after 4-5 polls → contact TripJack support.
            $this->appendAdminNote($booking, "Flight {$this->type} amendment ({$this->amendmentId}) still unresolved after ".self::MAX_ATTEMPTS.' quick checks — please follow up with TripJack support. The site keeps re-checking every '.self::SLOW_POLL_MINUTES.' min for 24h and will finalise/refund automatically if it resolves.');
            Log::channel('tripjack')->critical('flight_amendment_poll_exhausted', [
                'booking_id' => $this->bookingId, 'amendmentId' => $this->amendmentId, 'lastDetails' => $lastDetails,
            ]);
        }

        if ($this->attempt >= self::MAX_ATTEMPTS + self::MAX_SLOW_ATTEMPTS) {
            $this->appendAdminNote($booking, "Flight {$this->type} amendment ({$this->amendmentId}) still unresolved after 24h of automatic checks — automatic checking has stopped; resolve manually with TripJack.");
            Log::channel('tripjack')->critical('flight_amendment_poll_abandoned', [
                'booking_id' => $this->bookingId, 'amendmentId' => $this->amendmentId, 'lastDetails' => $lastDetails,
            ]);

            return;
        }

        $delay = $this->attempt >= self::MAX_ATTEMPTS ? now()->addMinutes(self::SLOW_POLL_MINUTES) : now()->addSeconds(10);

        self::dispatch($this->bookingId, $this->amendmentId, $this->attempt + 1, $this->isFullBooking, $this->type)
            ->delay($delay);
    }

    protected function appendAdminNote(Booking $booking, string $note): void
    {
        $booking->update(['admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '').$note)]);
    }
}
