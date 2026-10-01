<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\FlightAncillaryService;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackFlightClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Same async-poll pattern as PollFlightAmendmentJob (cancellation), applied
 * to Add SSR's amendmentIds — TripJack's amendment-details endpoint doesn't
 * resolve synchronously, so this self-requeues until SUCCESS/REJECTED or
 * the attempt cap, at which point it's left for manual follow-up.
 */
class PollFlightSsrAmendmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    protected const MAX_ATTEMPTS = 6;

    // Same slow follow-up as PollFlightAmendmentJob — a late resolution
    // still confirms or refunds the extras automatically.
    protected const SLOW_POLL_MINUTES = 30;

    protected const MAX_SLOW_ATTEMPTS = 48; // 24 hours

    public function __construct(
        protected int $bookingId,
        protected string $amendmentId,
        protected array $batchAmendmentIds,
        protected int $attempt = 1,
        protected ?int $paymentId = null, // the extras payment to refund if TripJack rejects
    ) {}

    public function handle(TripJackFlightClient $client, FlightAncillaryService $ancillaries): void
    {
        $booking = Booking::find($this->bookingId);
        if (! $booking) {
            return;
        }

        try {
            $details = $client->amendmentDetails($this->amendmentId);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('flight_ssr_amendment_poll_failed', [
                'booking_id' => $this->bookingId, 'amendmentId' => $this->amendmentId, 'attempt' => $this->attempt, 'message' => $e->getMessage(),
            ]);
            $this->requeueOrGiveUp($booking);

            return;
        }

        $status = $details['amendmentStatus'] ?? null;

        if (in_array($status, ['SUCCESS', 'REJECTED'], true)) {
            $ancillaries->finalizeAmendment($booking, $this->amendmentId, $details, $this->batchAmendmentIds, $this->paymentId);

            return;
        }

        $this->requeueOrGiveUp($booking, $details);
    }

    protected function requeueOrGiveUp(Booking $booking, array $lastDetails = []): void
    {
        if ($this->attempt === self::MAX_ATTEMPTS) {
            $booking->update([
                'flight_ssr_status' => 'needs_review',
                'admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '')
                    ."SSR amendment ({$this->amendmentId}) still unresolved after ".self::MAX_ATTEMPTS.' quick checks — please follow up with TripJack support. The site keeps re-checking every '.self::SLOW_POLL_MINUTES.' min for 24h.'),
            ]);
            Log::channel('tripjack')->critical('flight_ssr_amendment_poll_exhausted', [
                'booking_id' => $this->bookingId, 'amendmentId' => $this->amendmentId, 'lastDetails' => $lastDetails,
            ]);
        }

        if ($this->attempt >= self::MAX_ATTEMPTS + self::MAX_SLOW_ATTEMPTS) {
            $booking->update([
                'admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '')
                    ."SSR amendment ({$this->amendmentId}) still unresolved after 24h — automatic checking has stopped; resolve and refund manually."),
            ]);
            Log::channel('tripjack')->critical('flight_ssr_amendment_poll_abandoned', [
                'booking_id' => $this->bookingId, 'amendmentId' => $this->amendmentId, 'lastDetails' => $lastDetails,
            ]);

            return;
        }

        $delay = $this->attempt >= self::MAX_ATTEMPTS ? now()->addMinutes(self::SLOW_POLL_MINUTES) : now()->addSeconds(10);

        self::dispatch($this->bookingId, $this->amendmentId, $this->batchAmendmentIds, $this->attempt + 1, $this->paymentId)
            ->delay($delay);
    }
}
