<?php

namespace App\Services\TripJack;

use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TripJackMappingSync
{
    /** How far back a first-ever run starts (TripJack has 1.5M+ hotels over 5 years). */
    protected const FIRST_RUN_LOOKBACK_DAYS = 7;

    /** Extra attempts for a failed content batch before its IDs are parked for the next run. */
    protected const CHUNK_RETRIES = 2;

    public function __construct(
        protected TripJackClient $client,
        protected TripJackHotelSync $hotelSync,
    ) {
    }

    protected function settingKey(string $type): string
    {
        return "tripjack_mapping_sync_last_run_{$type}";
    }

    /** In-progress run: {since, runStartedAt, cursor, page} — saved after every page. */
    protected function stateKey(string $type): string
    {
        return "tripjack_mapping_sync_state_{$type}";
    }

    /** tjHotelIds whose content fetch failed every attempt, retried next run. */
    protected function failedKey(string $type): string
    {
        return "tripjack_mapping_sync_failed_{$type}";
    }

    /**
     * Where this run starts: an unfinished run's saved cursor, or a fresh run
     * from the last completed run (first ever run: only a week back).
     *
     * @return array{since:string, runStartedAt:string, cursor:?string, page:int}
     */
    protected function startState(string $type): array
    {
        $saved = Setting::getJson($this->stateKey($type), []);
        if (! empty($saved['since']) && ! empty($saved['runStartedAt'])) {
            return [
                'since' => $saved['since'],
                'runStartedAt' => $saved['runStartedAt'],
                'cursor' => $saved['cursor'] ?? null,
                'page' => (int) ($saved['page'] ?? 0),
            ];
        }

        return [
            'since' => Setting::get($this->settingKey($type)) ?: now()->subDays(self::FIRST_RUN_LOOKBACK_DAYS)->toIso8601String(),
            'runStartedAt' => now()->toIso8601String(),
            'cursor' => null,
            'page' => 0,
        ];
    }

    /** Saves progress, or — when the run reached the end — moves the watermark and clears it. */
    protected function saveState(string $type, array $state, bool $finished): void
    {
        if ($finished) {
            Setting::set($this->settingKey($type), $state['runStartedAt']);
            Setting::setJson($this->stateKey($type), []);

            return;
        }

        Setting::setJson($this->stateKey($type), $state);
    }

    /**
     * Pulls NEW or UPDATE hotel mappings since the last completed run and
     * syncs each into the local hotels table. Progress is saved after every
     * page, so a run cut short (timeout, rate limit, --max-pages) resumes
     * from the same cursor next time instead of starting over.
     *
     * A NEW hotel is only synced when its city AND country match an existing
     * Destination; unmatched hotels are counted under skipped_no_destination.
     * UPDATE only fetches content for hotels we already have.
     *
     * @return array{fetched:int, synced:int, skipped_no_destination:int, errors:int, retried_ok:int, finished:bool}
     */
    public function syncMappings(string $type, ?int $maxPages = null): array
    {
        $type = strtoupper($type);
        if (! in_array($type, ['NEW', 'UPDATE'], true)) {
            throw new InvalidArgumentException("Invalid mapping sync type: {$type}. Must be NEW or UPDATE.");
        }

        $stats = ['fetched' => 0, 'synced' => 0, 'skipped_no_destination' => 0, 'errors' => 0, 'retried_ok' => 0, 'finished' => false];

        // Batches that failed last time go first.
        $parked = collect(Setting::getJson($this->failedKey($type), []))->map(fn ($id) => (string) $id);
        Setting::setJson($this->failedKey($type), []);
        if ($parked->isNotEmpty()) {
            $before = $stats['synced'];
            $this->syncIds($parked, $type, $stats);
            $stats['retried_ok'] = $stats['synced'] - $before;
        }

        $state = $this->startState($type);
        $pagesThisRun = 0;

        do {
            $mappingResponse = $this->client->fetchHotelMappingSync($type, $state['since'], $state['cursor'], $state['page']);
            $rows = $mappingResponse['hotels'] ?? [];
            $stats['fetched'] += count($rows);

            $this->syncIds(collect($rows)->pluck('tjHotelId')->filter()->map(fn ($id) => (string) $id)->values(), $type, $stats);

            $state['cursor'] = $mappingResponse['nextCursor'] ?? null;
            $state['page']++;
            $pagesThisRun++;
            $this->saveState($type, $state, finished: ! $state['cursor']);
        } while ($state['cursor'] && ($maxPages === null || $pagesThisRun < $maxPages));

        $stats['finished'] = ! $state['cursor'];

        return $stats;
    }

    /**
     * Fetches content for the given IDs (100 per call, retried) and syncs
     * each. IDs whose batch still fails are parked for the next run.
     */
    protected function syncIds(Collection $tjHotelIds, string $type, array &$stats): void
    {
        // UPDATE: only hotels we actually list — content for the rest of
        // the world's updated hotels would just be thrown away.
        if ($type === 'UPDATE' && $tjHotelIds->isNotEmpty()) {
            $local = Hotel::whereIn('tripjack_hotel_id', $tjHotelIds->all())->pluck('tripjack_hotel_id')->map(fn ($id) => (string) $id);
            $stats['skipped_no_destination'] += $tjHotelIds->count() - $local->count();
            $tjHotelIds = $tjHotelIds->intersect($local)->values();
        }

        foreach ($tjHotelIds->chunk(100) as $chunk) {
            $contentResponse = $this->fetchContentWithRetry($chunk->values()->all(), $type);
            if ($contentResponse === null) {
                $this->park($type, $chunk->all());
                $stats['errors'] += $chunk->count();

                continue;
            }

            $detailsByHotelId = collect($contentResponse['hotels'] ?? [])->keyBy(fn ($d) => (string) ($d['tjHotelId'] ?? ''));

            foreach ($chunk as $tjHotelId) {
                $detail = $detailsByHotelId->get($tjHotelId);
                if (! $detail) {
                    // No content for this ID (e.g. delisted since the mapping call).
                    $stats['errors']++;

                    continue;
                }

                try {
                    $this->syncOne($detail, $type, $stats);
                } catch (\Throwable $e) {
                    Log::channel('tripjack')->warning('mapping_sync_hotel_failed', [
                        'tjHotelId' => $tjHotelId,
                        'type' => $type,
                        'message' => $e->getMessage(),
                    ]);
                    $stats['errors']++;
                }
            }
        }
    }

    protected function fetchContentWithRetry(array $ids, string $type): ?array
    {
        for ($attempt = 0; $attempt <= self::CHUNK_RETRIES; $attempt++) {
            try {
                return $this->client->fetchHotelContent($ids);
            } catch (\Throwable $e) {
                Log::channel('tripjack')->warning('mapping_sync_chunk_failed', [
                    'tjHotelIds' => $ids,
                    'type' => $type,
                    'attempt' => $attempt + 1,
                    'message' => $e->getMessage(),
                ]);
                if ($attempt < self::CHUNK_RETRIES && ! app()->runningUnitTests()) {
                    sleep(2 ** $attempt);
                }
            }
        }

        return null;
    }

    protected function park(string $type, array $ids): void
    {
        $existing = Setting::getJson($this->failedKey($type), []);
        Setting::setJson($this->failedKey($type), array_values(array_unique(array_merge($existing, array_map('strval', $ids)))));
    }

    /**
     * @param  array<string,mixed>  $detail  One hotel's payload from fetchHotelContent().
     */
    protected function syncOne(array $detail, string $type, array &$stats): void
    {
        $tjHotelId = (string) ($detail['tjHotelId'] ?? '');
        $existing = $tjHotelId !== '' ? Hotel::where('tripjack_hotel_id', $tjHotelId)->first() : null;

        if ($existing) {
            $this->hotelSync->upsertHotel($detail, $existing->destination_id);
            $stats['synced']++;

            return;
        }

        if ($type === 'UPDATE') {
            $stats['skipped_no_destination']++;

            return;
        }

        $destination = $this->destinationFor($detail);
        if (! $destination) {
            $stats['skipped_no_destination']++;

            return;
        }

        $this->hotelSync->upsertHotel($detail, $destination->id);
        $stats['synced']++;
    }

    /**
     * The local destination for a hotel: same city name AND same country
     * (Hyderabad, Pakistan must not land in Hyderabad, India). A hotel whose
     * content has no country is not matched rather than guessed.
     */
    protected function destinationFor(array $detail): ?Destination
    {
        $address = $detail['locale']['address'] ?? [];
        $city = $address['city'] ?? null;
        // Static content has address.countryname (e.g. "United Arab
        // Emirates") and countrycode; there is no "country" key.
        $country = $address['countryname'] ?? null;

        if (! $city || ! $country) {
            return null;
        }

        return Destination::whereRaw('LOWER(name) = ?', [strtolower(trim($city))])
            ->whereRaw('LOWER(country) = ?', [strtolower(trim($country))])
            ->first();
    }

    /**
     * Pulls deleted hotel mappings since the last completed run and
     * deactivates the matching local hotels. Soft-deactivates only (never
     * deletes the row) so admin overrides and booking history stay intact.
     * Resumable page by page, like syncMappings().
     *
     * @return array{fetched:int, deactivated:int, finished:bool}
     */
    public function syncDeleted(?int $maxPages = null): array
    {
        $stats = ['fetched' => 0, 'deactivated' => 0, 'finished' => false];
        $state = $this->startState('DELETE');
        $pagesThisRun = 0;

        do {
            $response = $this->client->fetchDeletedHotelMapping($state['since'], $state['cursor'], $state['page']);
            $rows = $response['hotels'] ?? [];
            $stats['fetched'] += count($rows);

            $tjHotelIds = collect($rows)
                ->pluck('tjHotelId')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->all();

            if (! empty($tjHotelIds)) {
                $stats['deactivated'] += Hotel::whereIn('tripjack_hotel_id', $tjHotelIds)->update(['is_active' => false]);
            }

            $state['cursor'] = $response['nextCursor'] ?? null;
            $state['page']++;
            $pagesThisRun++;
            $this->saveState('DELETE', $state, finished: ! $state['cursor']);
        } while ($state['cursor'] && ($maxPages === null || $pagesThisRun < $maxPages));

        $stats['finished'] = ! $state['cursor'];

        return $stats;
    }
}
