<?php

namespace App\Jobs;

use App\Models\Hotel;
use App\Services\TripJack\TripJackHotelSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Fetches a hotel's room-level data (occupancy, bed type, amenities,
 * room-specific photos) via TripJack's single-hotel Static Detail API,
 * falling back to deriving rooms from the live Pricing API if that comes
 * back empty — the same two-step lookup TripJackHotelSync already used
 * everywhere else. Always queued, never run inline, so neither an admin
 * saving a hotel nor a guest loading its page ever waits on this TripJack
 * call.
 *
 * Dispatched from two places: TripJackHotelSync::upsertHotel() (a hotel
 * was just created/updated and still has no room types after the bulk
 * static-content sync, which frequently omits rooms) and the rolling
 * resync command app:resync-stale-room-types (keeps already-synced hotels
 * from going stale over time).
 */
class SyncHotelRoomTypes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 2;

    public function __construct(protected int $hotelId) {}

    public function handle(TripJackHotelSync $sync): void
    {
        $hotel = Hotel::find($this->hotelId);

        if (! $hotel || $hotel->source !== 'tripjack' || ! $hotel->tripjack_hotel_id) {
            return;
        }

        try {
            $result = $sync->syncRoomImagesFromStaticDetail($hotel);

            if ($result['synced'] === 0) {
                $sync->syncLiveRoomsFromPricing($hotel);
            }
        } catch (\Throwable $e) {
            Log::channel('tripjack')->warning('room_type_job_sync_failed', [
                'hotel_id' => $this->hotelId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dispatches a room-type sync for this hotel unless one was already
     * requested recently — guards against re-queuing a hotel on every one
     * of its daily mapping-sync updates when TripJack genuinely has no
     * room catalogue for it (a real, observed case — not just slow sync).
     */
    public static function dispatchIfNeeded(Hotel $hotel): void
    {
        if ($hotel->source !== 'tripjack' || ! $hotel->tripjack_hotel_id) {
            return;
        }

        $lockKey = "tripjack_room_autosync_attempted:{$hotel->id}";
        if (Cache::has($lockKey)) {
            return;
        }
        Cache::put($lockKey, true, now()->addHours(6));

        static::dispatch($hotel->id);
    }
}
