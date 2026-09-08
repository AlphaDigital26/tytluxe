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

        if (! empty($stats['city_mismatches'])) {
            $this->newLine();
            $this->warn(count($stats['city_mismatches']).' hotel(s) may be filed under the wrong destination — TripJack reports a different city than what they\'re assigned to locally. Review and reassign manually if confirmed wrong:');
            $this->table(
                ['Hotel ID', 'Title', 'Assigned Destination', 'TripJack Says'],
                $stats['city_mismatches']
            );
        }

        return self::SUCCESS;
    }
}
