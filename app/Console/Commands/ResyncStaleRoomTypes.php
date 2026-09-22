<?php

namespace App\Console\Commands;

use App\Jobs\SyncHotelRoomTypes;
use App\Models\Hotel;
use Illuminate\Console\Command;

/**
 * Keeps already-synced hotels' room data from silently going stale.
 * TripJackHotelSync::upsertHotel() only queues a room sync when a hotel
 * currently has zero room types — it has no way to notice TripJack later
 * renaming a room, changing its photos, or updating occupancy for a hotel
 * that already has rows. This command instead picks a small batch of
 * hotels ordered by longest-since-last-room-sync (oldest/never first) and
 * queues a fresh sync for each, so every hotel gets refreshed on a rolling
 * basis regardless of whether TripJack ever reported it as "updated".
 *
 * Batched small and run frequently (see routes/console.php) rather than
 * resyncing everything at once, for the same rate-limiting reason
 * documented on syncLiveRoomsFromPricing() — TripJack throttles/blocks
 * bursts of per-hotel calls.
 */
class ResyncStaleRoomTypes extends Command
{
    protected $signature = 'app:resync-stale-room-types {--batch=30 : How many hotels to queue this run}';

    protected $description = 'Queues a room-type resync for a rotating batch of TripJack hotels, oldest-synced first, so room data never goes stale without manual intervention';

    public function handle(): int
    {
        $batchSize = max(1, (int) $this->option('batch'));

        $hotels = Hotel::where('source', 'tripjack')
            ->where('is_active', true)
            ->whereNotNull('tripjack_hotel_id')
            ->orderByRaw('rooms_synced_at IS NOT NULL, rooms_synced_at ASC')
            ->limit($batchSize)
            ->get();

        foreach ($hotels as $hotel) {
            SyncHotelRoomTypes::dispatch($hotel->id);
        }

        $this->info("Queued room resync for {$hotels->count()} hotel(s).");

        return self::SUCCESS;
    }
}
