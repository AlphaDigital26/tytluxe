<?php

namespace App\Console\Commands;

use App\Services\TripJack\TripJackHotelSync;
use Illuminate\Console\Command;

class ResyncTripjackHotels extends Command
{
    protected $signature = 'app:resync-tripjack-hotels';

    protected $description = 'Re-syncs every already-synced TripJack hotel with the current Static Detail mapping (picks up fields/fixes added since each was last synced)';

    public function handle(TripJackHotelSync $sync): int
    {
        $bar = null;

        $stats = $sync->resyncAll(function ($stats) use (&$bar) {
            if (! $bar) {
                $bar = $this->output->createProgressBar($stats['total']);
                $bar->start();
            }
            $bar->advance();
        });

        $bar?->finish();
        $this->newLine(2);
        $this->info("Done. Total: {$stats['total']}, Synced: {$stats['synced']}, Errors: {$stats['errors']}");

        return self::SUCCESS;
    }
}
