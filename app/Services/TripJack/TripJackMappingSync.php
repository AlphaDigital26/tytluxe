<?php

namespace App\Services\TripJack;

use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TripJackMappingSync
{
    public function __construct(
        protected TripJackClient $client,
        protected TripJackHotelSync $hotelSync,
    ) {
    }

    protected function settingKey(string $type): string
    {
        return "tripjack_mapping_sync_last_run_{$type}";
    }

    /**
     * Pulls NEW or UPDATE hotel mappings since the last successful run of
     * this $type and syncs each into the local hotels table.
     *
     * A NEW hotel is only synced when its city already matches an existing
     * Destination — TripJack's catalogue spans 200+ countries and we don't
     * want every worldwide city silently turned into a browsable destination
     * just because a hotel mapping appeared; unmatched hotels are counted
     * under skipped_no_destination for manual review instead. An UPDATE row
     * for a hotel we don't have locally is treated the same way rather than
     * created blind.
     *
     * @return array{fetched:int, synced:int, skipped_no_destination:int, errors:int}
     */
    public function syncMappings(string $type, ?int $maxPages = null): array
    {
        $type = strtoupper($type);
        if (! in_array($type, ['NEW', 'UPDATE'], true)) {
            throw new InvalidArgumentException("Invalid mapping sync type: {$type}. Must be NEW or UPDATE.");
        }

        $lastUpdateTime = Setting::get($this->settingKey($type), now()->subYears(5)->toIso8601String());
        $runStartedAt = now()->toIso8601String();

        $stats = ['fetched' => 0, 'synced' => 0, 'skipped_no_destination' => 0, 'errors' => 0];
        $cursor = null;
        $page = 0;

        do {
            $mappingResponse = $this->client->fetchHotelMappingSync($type, $lastUpdateTime, $cursor, $page);
            $rows = $mappingResponse['hotels'] ?? [];
            $stats['fetched'] += count($rows);

            $tjHotelIds = collect($rows)
                ->pluck('tjHotelId')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values();

            // Bulk fetch content 100 IDs at a time (TripJack's per-call cap)
            // instead of one static-detail call per hotel — the difference
            // between ~20 calls and ~2000 for a full 2000-hotel page.
            foreach ($tjHotelIds->chunk(100) as $chunk) {
                try {
                    $contentResponse = $this->client->fetchHotelContent($chunk->all());
                } catch (\Throwable $e) {
                    Log::channel('tripjack')->warning('mapping_sync_chunk_failed', [
                        'tjHotelIds' => $chunk->all(),
                        'type' => $type,
                        'message' => $e->getMessage(),
                    ]);
                    $stats['errors'] += $chunk->count();

                    continue;
                }

                $detailsByHotelId = collect($contentResponse['hotels'] ?? [])->keyBy(fn ($d) => (string) ($d['tjHotelId'] ?? ''));

                foreach ($chunk as $tjHotelId) {
                    $detail = $detailsByHotelId->get($tjHotelId);
                    if (! $detail) {
                        // TripJack didn't return content for this ID (e.g. it
                        // was delisted between the mapping call and now).
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

            $cursor = $mappingResponse['nextCursor'] ?? null;
            $page++;
        } while ($cursor && ($maxPages === null || $page < $maxPages));

        // Only advance the watermark once the whole run has completed
        // without throwing — a mid-run failure should retry from the same
        // lastUpdateTime next time rather than silently skip the remainder.
        Setting::set($this->settingKey($type), $runStartedAt);

        return $stats;
    }

    /**
     * @param  array<string,mixed>  $detail  One hotel's payload from
     *                                       fetchHotelContent() — same shape as staticDetail().
     * @param  array{fetched:int, synced:int, skipped_no_destination:int, errors:int}  $stats
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

        $city = $detail['locale']['address']['city'] ?? null;
        $destination = $city ? Destination::whereRaw('LOWER(name) = ?', [strtolower($city)])->first() : null;

        if (! $destination) {
            $stats['skipped_no_destination']++;

            return;
        }

        $this->hotelSync->upsertHotel($detail, $destination->id);
        $stats['synced']++;
    }

    /**
     * Pulls deleted hotel mappings since the last successful run and
     * deactivates the matching local hotels. Soft-deactivates only (never
     * deletes the row) so admin overrides and booking history stay intact.
     *
     * @return array{fetched:int, deactivated:int}
     */
    public function syncDeleted(?int $maxPages = null): array
    {
        $lastUpdateTime = Setting::get($this->settingKey('DELETE'), now()->subYears(5)->toIso8601String());
        $runStartedAt = now()->toIso8601String();

        $stats = ['fetched' => 0, 'deactivated' => 0];
        $cursor = null;
        $page = 0;

        do {
            $response = $this->client->fetchDeletedHotelMapping($lastUpdateTime, $cursor, $page);
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

            $cursor = $response['nextCursor'] ?? null;
            $page++;
        } while ($cursor && ($maxPages === null || $page < $maxPages));

        Setting::set($this->settingKey('DELETE'), $runStartedAt);

        return $stats;
    }
}
