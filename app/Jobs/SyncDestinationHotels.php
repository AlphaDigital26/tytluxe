<?php

namespace App\Jobs;

use App\Models\Destination;
use App\Services\TripJack\TripJackHotelSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncDestinationHotels implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        protected int $destinationId,
        protected int $limit = 100,
    ) {}

    public function handle(TripJackHotelSync $sync): void
    {
        $destination = Destination::find($this->destinationId);

        if (! $destination) {
            return;
        }

        $destination->update(['hotel_sync_status' => 'syncing']);

        try {
            $stats = $sync->syncCity($destination->name, (string) $destination->country, $this->limit);

            $destination->update([
                'hotel_sync_status' => 'done',
                'hotel_sync_count' => $stats['synced'] ?? 0,
                'hotel_synced_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::channel('tripjack')->error('destination_hotel_sync_failed', [
                'destination_id' => $this->destinationId,
                'message' => $e->getMessage(),
            ]);

            $destination->update(['hotel_sync_status' => 'failed']);
        }
    }
}
