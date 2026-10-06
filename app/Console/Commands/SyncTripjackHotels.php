<?php

namespace App\Console\Commands;

use App\Services\TripJack\TripJackHotelSync;
use Illuminate\Console\Command;

class SyncTripjackHotels extends Command
{
    protected $signature = 'app:sync-tripjack-hotels
        {city : City name, e.g. Dubai}
        {country : Country name as TripJack lists it (any case), e.g. "UNITED ARAB EMIRATES"}
        {--limit=20 : Max hotels to sync}
        {--max-country-pages=10 : When the city has no cached region, how many 2000-hotel pages of the country to scan}';

    protected $description = 'Sync TripJack static hotel content for a city into the hotels table';

    public function handle(TripJackHotelSync $sync): int
    {
        $stats = $sync->syncCity(
            $this->argument('city'),
            $this->argument('country'),
            (int) $this->option('limit'),
            (int) $this->option('max-country-pages'),
        );

        if (isset($stats['error'])) {
            $this->error($stats['error']);

            return self::FAILURE;
        }

        $this->info("Found: {$stats['found']}, Synced: {$stats['synced']}, Skipped (city mismatch): {$stats['skipped']}, Errors: {$stats['errors']}");
        if ($stats['synced'] === 0) {
            $this->warn('No hotels matched this city — no destination was created.');
        }

        return self::SUCCESS;
    }
}
