<?php

namespace App\Services\TripJack;

use App\Models\Amenity;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\RoomType;
use App\Models\TripjackCity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TripJackHotelSync
{
    public function __construct(protected TripJackClient $client)
    {
    }

    /**
     * Re-syncs every already-synced TripJack hotel with the current mapping
     * logic (picks up new fields/fixes added since each hotel was last
     * synced). Reuses the tripjack_hotel_id already stored locally — no need
     * to re-discover hotel IDs via the city/region mapping endpoints.
     */
    public function resyncAll(?callable $onProgress = null): array
    {
        $hotelIds = Hotel::where('source', 'tripjack')
            ->whereNotNull('tripjack_hotel_id')
            ->orderBy('id')
            ->pluck('tripjack_hotel_id', 'id');

        $stats = ['total' => $hotelIds->count(), 'synced' => 0, 'errors' => 0];

        foreach ($hotelIds as $localId => $tjHotelId) {
            try {
                $hotel = Hotel::find($localId);
                $detail = $this->client->staticDetail((string) $tjHotelId);
                $this->upsertHotel($detail, $hotel->destination_id);
                $stats['synced']++;
            } catch (\Throwable $e) {
                Log::channel('tripjack')->warning('resync_failed', ['tjHotelId' => $tjHotelId, 'message' => $e->getMessage()]);
                $stats['errors']++;
            }

            if ($onProgress) {
                $onProgress($stats);
            }
        }

        return $stats;
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
        $unwrapped = $this->unwrapJsonBlob($descriptions['default'] ?? null, [
            'location', 'rooms', 'business_amenities', 'onsite_payments', 'spoken_languages', 'overview', 'general description', 'snippet',
        ]);

        return $unwrapped ?: trim((string) ($descriptions['headline'] ?? ''));
    }

    /**
     * TripJack's policies fields (mandatory_fees, special_instructions,
     * know_before_you_go) have the exact same quirk as descriptions.default:
     * for some (mostly apartment-style) listings they're themselves
     * JSON-encoded objects of sub-sections instead of plain text/HTML —
     * confirmed on a real response where mandatory_fees came back as
     * '{"Mandatory":"You\'ll be asked to pay..."}' rather than prose. These
     * are rendered with {!! !!} (raw HTML) on the hotel page, so storing the
     * undecoded JSON would show literal `{"key":"..."}` syntax to customers.
     *
     * @param  string[]  $preferredKeys  Tried first, in order; any other
     *                                   string-valued key is used as a fallback.
     */
    protected function unwrapJsonBlob(?string $value, array $preferredKeys = []): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        if (! str_starts_with(trim($value), '{')) {
            return trim($value);
        }

        $decoded = json_decode($value, true);
        if (! is_array($decoded)) {
            return trim($value); // undecodable — return as-is rather than lose it
        }

        $lowerMap = collect($decoded)->keyBy(fn ($v, $k) => strtolower((string) $k));

        $parts = collect($preferredKeys)
            ->map(fn ($key) => $lowerMap->get(strtolower($key)))
            ->filter(fn ($v) => ! empty($v) && is_string($v))
            ->map(fn ($v) => trim($v));

        // No preferred keys matched (TripJack's key naming isn't consistent
        // across hotels/fields) — fall back to every string value present.
        if ($parts->isEmpty()) {
            $parts = collect($decoded)->filter(fn ($v) => is_string($v) && trim($v) !== '')->map(fn ($v) => trim($v))->values();
        }

        $text = trim(implode("\n\n", $parts->unique()->all()));

        return $text !== '' ? $text : null;
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

    /**
     * Room-type images.links is keyed by pixel width ("70px", "200px", ...)
     * with no guaranteed key order — picks the largest available rather than
     * assuming any particular key exists or that array order is meaningful.
     *
     * @param  array<string, array{href?:string}>  $links
     */
    protected function largestImageUrl(array $links): ?string
    {
        return collect($links)
            ->sortByDesc(fn ($link, $key) => (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT))
            ->pluck('href')
            ->filter()
            ->first();
    }

    /**
     * TripJack's docs claim bed_config.description is a ready-made summary
     * string ("1 King Bed, 2 Twin Beds"), but real responses only include
     * bed_count/bedroom_count/configuration[] with no description field —
     * build the summary ourselves from configuration[] instead.
     *
     * @param  array{configuration?: array<int, array{type?:string, quantity?:int}>}  $bedConfig
     */
    protected function bedTypeSummary(array $bedConfig): ?string
    {
        if (! empty($bedConfig['description'])) {
            return $bedConfig['description'];
        }

        $summary = collect($bedConfig['configuration'] ?? [])
            ->filter(fn ($c) => ! empty($c['type']))
            ->map(fn ($c) => (int) ($c['quantity'] ?? 1).' '.$c['type'])
            ->implode(', ');

        return $summary !== '' ? $summary : null;
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
                'mandatory_fees' => $this->unwrapJsonBlob($detail['policies']['mandatory_fees'] ?? null, ['mandatory']),
                'chain_name' => $detail['chain']['name'] ?? null,
                'house_rules' => ! empty($detail['policies']['houseRules']) ? $detail['policies']['houseRules'] : null,
                'special_instructions' => $this->unwrapJsonBlob($detail['policies']['special_instructions'] ?? null, ['special instructions', 'instructions']),
                'know_before_you_go' => $this->unwrapJsonBlob($detail['policies']['know_before_you_go'] ?? null, ['know_before_you_go', 'house_rules']),
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

        $this->syncAmenities($hotel, $detail['amenities'] ?? []);
        $this->syncRoomTypes($hotel, $detail['rooms'] ?? []);
    }

    /**
     * Amenity names are deduped globally by name (the amenities table has a
     * unique constraint on it) — a TripJack amenity that already exists as a
     * manually-added one (e.g. "Free Wi-Fi") is reused rather than duplicated.
     *
     * @param  array<string, array{id:string, name:string}>  $amenitiesMap
     */
    protected function syncAmenities(Hotel $hotel, array $amenitiesMap): void
    {
        if (empty($amenitiesMap)) {
            return;
        }

        $amenityIds = collect($amenitiesMap)
            ->pluck('name')
            ->filter()
            ->unique(fn ($name) => strtolower(trim($name)))
            ->map(function ($name) {
                $amenity = Amenity::where('type', 'hotel')
                    ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
                    ->first();

                return $amenity?->id ?? Amenity::create(['name' => trim($name), 'type' => 'hotel'])->id;
            })
            ->values();

        $hotel->amenities()->sync($amenityIds);
    }

    /**
     * Cap on how many static room-type entries to sync per hotel. Live data
     * showed a large property can list 400+ entries — mostly rate-plan
     * variants of the same physical room ("Taj Club, Lounge Access" / "Taj
     * Club, 2 Single" / "Taj Club, Extra Bed" ...), not distinct room types.
     * Syncing all of them per hotel across the full catalogue would bloat
     * the table with low display-value rows and slow every hotel page. This
     * is a deliberate trade-off, not an oversight — raise it if you need
     * full fidelity for a specific hotel.
     */
    protected const MAX_ROOM_TYPES_PER_HOTEL = 40;

    /**
     * Syncs TripJack's static room-type catalogue (bed config, room photos,
     * occupancy, room-level amenities) into `room_types`. This is catalogue
     * content only — price/availability/cancellation always come from the
     * live Pricing/Review call, never from here (per TripJack's own docs:
     * static content "can be stale or incomplete for booking").
     *
     * @param  array<string, array>  $roomsMap
     */
    protected function syncRoomTypes(Hotel $hotel, array $roomsMap): void
    {
        if (empty($roomsMap)) {
            return;
        }

        if (count($roomsMap) > self::MAX_ROOM_TYPES_PER_HOTEL) {
            Log::channel('tripjack')->info('room_types_capped', [
                'hotel_id' => $hotel->id, 'available' => count($roomsMap), 'synced' => self::MAX_ROOM_TYPES_PER_HOTEL,
            ]);
            $roomsMap = array_slice($roomsMap, 0, self::MAX_ROOM_TYPES_PER_HOTEL, true);
        }

        $seenCodes = [];

        foreach ($roomsMap as $room) {
            $tripjackRoomCode = (string) ($room['id'] ?? '');
            if ($tripjackRoomCode === '') {
                continue;
            }
            $seenCodes[] = $tripjackRoomCode;

            $images = collect($room['images'] ?? [])
                ->map(fn ($image) => $this->largestImageUrl($image['links'] ?? []))
                ->filter()
                ->values();

            $heroImage = collect($room['images'] ?? [])->firstWhere('hero_image', true);
            $heroUrl = $heroImage ? $this->largestImageUrl($heroImage['links'] ?? []) : null;
            $heroUrl ??= $images->first();

            $roomAmenities = collect($room['amenities'] ?? [])->pluck('name')->filter()->values()->all();

            $occupancy = $room['occupancy']['max_allowed'] ?? [];
            // 0 here means "not provided by TripJack for this entry", not
            // "sleeps zero" — floor to a sane minimum rather than store it raw.
            $occupancyAdults = (int) ($occupancy['adults'] ?? 0);

            RoomType::updateOrCreate(
                ['hotel_id' => $hotel->id, 'tripjack_room_code' => $tripjackRoomCode],
                [
                    'name' => $room['name'] ?? 'Room',
                    'image_path' => $heroUrl,
                    'images' => $images->isNotEmpty() ? $images->all() : null,
                    'description' => $room['descriptions']['overview'] ?? null,
                    'occupancy_adults' => $occupancyAdults > 0 ? $occupancyAdults : 2,
                    'occupancy_children' => (int) ($occupancy['children'] ?? 0),
                    'room_size' => isset($room['area']['square_feet']) ? $room['area']['square_feet'].' sq ft' : null,
                    'bed_type' => $this->bedTypeSummary($room['bed_config'] ?? []),
                    'inclusions' => ! empty($roomAmenities) ? $roomAmenities : null,
                    'is_active' => true,
                ]
            );
        }

        // Deactivate room types no longer present in TripJack's catalogue
        // rather than deleting — preserves any admin overrides/history.
        if (! empty($seenCodes)) {
            RoomType::where('hotel_id', $hotel->id)
                ->whereNotNull('tripjack_room_code')
                ->whereNotIn('tripjack_room_code', $seenCodes)
                ->update(['is_active' => false]);
        }
    }
}
