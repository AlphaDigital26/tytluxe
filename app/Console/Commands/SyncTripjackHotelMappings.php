<?php

namespace App\Console\Commands;

use App\Services\TripJack\TripJackMappingSync;
use Illuminate\Console\Command;

class SyncTripjackHotelMappings extends Command
{
    protected $signature = 'app:sync-tripjack-hotel-mappings
        {type : NEW, UPDATE, or DELETE}
        {--max-pages= : Stop after N pages (omit for a full sync)}';

    protected $description = 'Incrementally syncs TripJack hotel mappings (new/updated/deleted) since the last successful run of this type';

    public function handle(TripJackMappingSync $sync): int
    {
        $type = strtoupper((string) $this->argument('type'));
        $maxPages = $this->option('max-pages') !== null ? (int) $this->option('max-pages') : null;

        if (! in_array($type, ['NEW', 'UPDATE', 'DELETE'], true)) {
            $this->error("Invalid type: {$type}. Must be NEW, UPDATE, or DELETE.");

            return self::FAILURE;
        }

        $stats = $type === 'DELETE'
            ? $sync->syncDeleted($maxPages)
            : $sync->syncMappings($type, $maxPages);

        $this->info("Type: {$type}. ".collect($stats)->map(fn ($v, $k) => "{$k}: {$v}")->implode(', '));

        return self::SUCCESS;
    }
}
