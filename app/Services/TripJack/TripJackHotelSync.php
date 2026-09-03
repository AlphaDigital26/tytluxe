<?php

namespace App\Services\TripJack;

use App\Models\Destination;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\TripjackCity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TripJackHotelSync
{
    public function __construct(protected TripJackClient $client)
    {
    }

    /**
     * Sync hotels for a city into the local `hotels` table.
     *
     * Prefers the cached city_region_id (precise, from tripjack_cities) when
     * available; otherwise falls back to TripJack's countryName filter and
     * matches hotels client-side by static-detail's locale.address.city,
     * since the world city/region list is not practical to fully crawl
     * up front just to resolve one city.
     */
    public function syncCity(string $cityName, string $countryName, int $limit = 20): array
    {
        $destination = Destination::firstOrCreate(
            ['slug' => Str::slug($cityName)],
            [
                'name' => Str::title($cityName),
                'country' => Str::title($countryName),
                'type' => 'city',
                'for' => ['hotel'],
                'is_active' => true,
            ]
        );

        $tripjackCity = TripjackCity::whereRaw('LOWER(city_name) = ?', [strtolower($cityName)])->first();

        $tjHotelIds = $tripjackCity
            ? $this->hotelIdsByRegion((string) $tripjackCity->city_region_id, $limit)
            : $this->hotelIdsByCountry(strtoupper($countryName), $limit);

        $stats = ['found' => count($tjHotelIds), 'synced' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($tjHotelIds as $tjHotelId) {
            try {
                $detail = $this->client->staticDetail((string) $tjHotelId);
            } catch (\Throwable $e) {
                Log::channel('tripjack')->warning('static_detail_failed', ['tjHotelId' => $tjHotelId, 'message' => $e->getMessage()]);
                $stats['errors']++;

                continue;
            }

            $detailCity = $detail['locale']['address']['city'] ?? null;
            if (! $tripjackCity && $detailCity && strtolower($detailCity) !== strtolower($cityName)) {
                $stats['skipped']++;

                continue;
            }

            $this->upsertHotel($detail, $destination->id);
            $stats['synced']++;

            if ($stats['synced'] >= $limit) {
                break;
            }
        }

        return $stats;
    }

    /** @return string[] */
    protected function hotelIdsByRegion(string $regionId, int $limit): array
    {
        $ids = [];
        $page = 0;

        do {
            $response = $this->client->fetchHotelMapping(regionIds: [$regionId], page: $page, size: min($limit, 2000));
            $rows = $response['hotels'] ?? [];
            foreach ($rows as $row) {
                $ids[] = $row['tjHotelId'];
            }
            $page++;
            $totalPages = $response['pageable']['totalPages'] ?? 1;
        } while (count($ids) < $limit && $page < $totalPages);

        return array_slice($ids, 0, $limit);
    }

    /** @return string[] */
    protected function hotelIdsByCountry(string $countryName, int $limit): array
    {
        $response = $this->client->fetchHotelMapping(countryName: $countryName, page: 0, size: min(max($limit * 10, 200), 2000));
        $rows = $response['hotels'] ?? [];

        return array_map(fn ($row) => $row['tjHotelId'], $rows);
    }

    /**
     * TripJack's descriptions.default is normally prose, but for some
     * (mostly apartment-style) listings it's itself a JSON-encoded object
     * of sub-sections (location, rooms, business_amenities, ...). Detect
     * that and build readable prose instead of storing the raw JSON.
     */
    public function resolveDescription(array $descriptions): string
    {
        $default = $descriptions['default'] ?? '';
        $headline = trim((string) ($descriptions['headline'] ?? ''));

        if (is_string($default) && str_starts_with(trim($default), '{')) {
            $decoded = json_decode($default, true);

            if (is_array($decoded)) {
                $preferredKeys = ['location', 'rooms', 'business_amenities', 'onsite_payments', 'spoken_languages', 'overview', 'general description', 'snippet'];
                $lowerMap = collect($decoded)->keyBy(fn ($v, $k) => strtolower((string) $k));

                $parts = [];
                foreach ($preferredKeys as $key) {
                    $value = $lowerMap->get($key);
                    if (! empty($value) && is_string($value)) {
                        $parts[] = trim($value);
                    }
                }

                // Unknown key names (TripJack's naming isn't consistent) — use
                // every string value in the object rather than leave raw JSON visible.
                if (empty($parts)) {
                    $parts = collect($decoded)->filter(fn ($v) => is_string($v) && trim($v) !== '')->map(fn ($v) => trim($v))->values()->all();
                }

                $text = trim(implode("\n\n", array_unique($parts)));
                if ($text !== '') {
                    return $text;
                }
            }

            // Undecodable or empty after extraction — fall through to headline.
            $default = '';
        }

        if (is_string($default) && trim($default) !== '') {
            return trim($default);
        }

        return $headline;
    }

    /**
     * TripJack's docs claim checkInCheckOut times are 24h "HH:MM" strings,
     * but real responses have already been observed sending "3:00 PM"-style
     * 12h strings instead — another spot where their docs and live behavior
     * disagree. Accept either rather than trusting the docs' claimed format.
     */
    protected function formatClockTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        foreach (['H:i', 'g:i A', 'g:i a', 'h:i A'] as $format) {
            try {
                return \Illuminate\Support\Carbon::createFromFormat($format, trim($time))->format('g:i A');
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    protected function upsertHotel(array $detail, int $destinationId): void
    {
        $tjHotelId = (string) ($detail['tjHotelId'] ?? '');
        if ($tjHotelId === '') {
            return;
        }

        $name = $detail['name'] ?? 'Unnamed Hotel';
        $starRating = (int) ($detail['star_rating'] ?? 0);
        $address = $detail['locale']['address']['fulladdr'] ?? '';
        $lat = $detail['locale']['coordinates']['lat'] ?? null;
        $lng = $detail['locale']['coordinates']['long'] ?? null;
        $description = $this->resolveDescription($detail['descriptions'] ?? []);

        $hotel = Hotel::firstOrNew(['tripjack_hotel_id' => $tjHotelId]);
        $hotel->fill(
            [
                'destination_id' => $destinationId,
                'title' => $name,
                'slug' => $hotel->slug ?? Str::slug($name.'-'.$tjHotelId),
                'description' => $description ?: 'No description available.',
                'category' => 'city_luxury',
                'address' => $address,
                'lat' => $lat,
                'lng' => $lng,
                'star_rating' => max(1, min(5, $starRating ?: 3)),
                'price_from' => 0,
                'source' => 'tripjack',
                'is_active' => (bool) ($detail['is_active'] ?? true),
                'check_in_time' => $this->formatClockTime($detail['policies']['checkInCheckOut']['checkin_from'] ?? null) ?? $hotel->check_in_time,
                'check_out_time' => $this->formatClockTime($detail['policies']['checkInCheckOut']['checkout_from'] ?? null) ?? $hotel->check_out_time,
                'mandatory_fees' => $detail['policies']['mandatory_fees'] ?? null,
            ]
        );
        $hotel->save();

        $images = collect($detail['images'] ?? [])
            ->map(function ($image) {
                $links = $image['links'] ?? [];
                $href = $links['original']['href'] ?? (reset($links)['href'] ?? null);

                return $href ? ['url' => $href, 'caption' => $image['caption'] ?? null, 'hero' => (bool) ($image['is_hero_image'] ?? false)] : null;
            })
            ->filter()
            ->values();

        if ($images->isNotEmpty()) {
            $hotel->images()->delete();
            foreach ($images->values() as $index => $image) {
                HotelImage::create([
                    'hotel_id' => $hotel->id,
                    'path' => $image['url'],
                    'sort_order' => $image['hero'] ? 0 : $index + 1,
                    'alt_text' => $image['caption'],
                ]);
            }
        }
    }
}
