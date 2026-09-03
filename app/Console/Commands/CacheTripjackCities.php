<?php

namespace App\Console\Commands;

use App\Models\TripjackCity;
use App\Services\TripJack\TripJackClient;
use Illuminate\Console\Command;

class CacheTripjackCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cache-tripjack-cities
        {--limit=2000 : Records per page (max 2000)}
        {--max-pages= : Stop after N pages (omit for a full sync)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync TripJack city/region IDs into the tripjack_cities table';

    public function handle(TripJackClient $client): int
    {
        $limit = (int) $this->option('limit');
        $maxPages = $this->option('max-pages') !== null ? (int) $this->option('max-pages') : null;

        $cursor = null;
        $page = 0;
        $total = 0;

        do {
            $response = $client->fetchCityRegionIds($limit, $cursor);
            $rows = $response['hotelCityRegionIds'] ?? [];

            foreach ($rows as $row) {
                TripjackCity::updateOrCreate(
                    ['city_region_id' => $row['cityRegionId']],
                    [
                        'city_name' => $row['cityName'],
                        'region_name' => $row['regionName'] ?? null,
                        'country_name' => $row['countryName'],
                        'region_type' => $row['regionType'] ?? null,
                        'full_region_name' => $row['fullRegionName'] ?? null,
                    ]
                );
            }

            $total += count($rows);
            $page++;
            $cursor = $response['nextCursor'] ?? null;
            $hasMore = (bool) ($response['hasMore'] ?? false);

            $this->info("Page {$page}: upserted ".count($rows)." cities (total {$total})");
        } while ($hasMore && $cursor && ($maxPages === null || $page < $maxPages));

        $this->info("Done. {$total} cities upserted across {$page} page(s).");

        return self::SUCCESS;
    }
}
