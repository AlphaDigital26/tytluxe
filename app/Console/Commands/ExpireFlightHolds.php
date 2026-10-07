<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackFlightClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Moves held flight fares to hold_expired once TripJack's hold deadline
 * (Booking Details itemInfos.AIR.timeLimit) has passed. The guest page
 * already shows them as expired; this keeps the database and admin panel in
 * line. A hold whose deadline wasn't saved at hold time is looked up in
 * Booking Details instead.
 *
 * Skipped: a hold the guest has already paid for (tripjack_confirm_attempted_at
 * is set while Confirm-Book runs) — that path settles the status itself.
 */
class ExpireFlightHolds extends Command
{
    protected $signature = 'flights:expire-holds';

    protected $description = 'Mark flight holds past their TripJack deadline as expired';

    /** Booking Details statuses meaning the hold is gone (Release PNR doc: UNCONFIRMED). */
    protected const GONE = ['UNCONFIRMED', 'CANCELLED', 'FAILED', 'ABORTED'];

    public function handle(TripJackFlightClient $client): int
    {
        $expired = Booking::query()
            ->where('vertical', 'flight')
            ->where('status', 'on_hold')
            ->whereNull('tripjack_confirm_attempted_at')
            ->whereNotNull('tripjack_hold_expires_at')
            ->where('tripjack_hold_expires_at', '<=', now())
            ->update(['status' => 'hold_expired']);

        $looked = 0;
        Booking::query()
            ->where('vertical', 'flight')
            ->where('status', 'on_hold')
            ->whereNull('tripjack_confirm_attempted_at')
            ->whereNull('tripjack_hold_expires_at')
            ->whereNotNull('tripjack_booking_id')
            ->orderBy('id')
            ->each(function (Booking $booking) use ($client, &$expired, &$looked) {
                $looked++;
                try {
                    $details = $client->bookingDetails($booking->tripjack_booking_id);
                } catch (TripJackException $e) {
                    Log::channel('tripjack')->warning('flight_hold_expiry_check_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

                    return;
                }

                $timeLimit = $details['itemInfos']['AIR']['timeLimit'] ?? null;
                $gone = in_array($details['order']['status'] ?? null, self::GONE, true)
                    || ($timeLimit && now()->gte($timeLimit));

                $booking->update(array_filter([
                    'tripjack_hold_expires_at' => $timeLimit,
                    'status' => $gone ? 'hold_expired' : null,
                ]));
                $expired += $gone ? 1 : 0;
            });

        $this->info("Marked {$expired} hold(s) as expired; looked up {$looked} hold(s) without a saved deadline.");

        return self::SUCCESS;
    }
}
