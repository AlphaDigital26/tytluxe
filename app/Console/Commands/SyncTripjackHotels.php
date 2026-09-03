<?php

namespace App\Console\Commands;

use App\Services\TripJack\TripJackHotelSync;
use Illuminate\Console\Command;

class SyncTripjackHotels extends Command
{
    protected $signature = 'app:sync-tripjack-hotels
        {city : City name, e.g. Dubai}
        {country : Country name, e.g. "United Arab Emirates"}
        {--limit=20 : Max hotels to sync}';

    protected $description = 'Sync TripJack static hotel content for a city into the hotels table';

    public function handle(TripJackHotelSync $sync): int
    {
        $stats = $sync->syncCity(
            $this->argument('city'),
            $this->argument('country'),
            (int) $this->option('limit')
        );

        $this->info("Found: {$stats['found']}, Synced: {$stats['synced']}, Skipped (city mismatch): {$stats['skipped']}, Errors: {$stats['errors']}");

        return self::SUCCESS;
    }
}
