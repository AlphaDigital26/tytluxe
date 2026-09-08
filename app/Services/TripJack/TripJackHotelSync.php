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
     * Canonical sub-section keys (lowercase, as they appear in TripJack's
     * JSON-encoded descriptions.default blob, or as inline "Label:" markers
     * in plain-text descriptions — both formats have been observed) mapped
     * to the heading shown under "About This Hotel". Order is display order.
     */
    protected const DESCRIPTION_SECTION_LABELS = [
        'location' => 'Location',
        'amenities' => 'Amenities',
        'rooms' => 'Rooms',
        'dining' => 'Dining',
        'business_amenities' => 'Business Amenities',
        'onsite_payments' => 'Onsite Payments',
        'spoken_languages' => 'Spoken Languages',
    ];

    /**
     * Same idea as DESCRIPTION_SECTION_LABELS, but these render as their own
     * full-width sections at the bottom of the hotel page instead of under
     * "About This Hotel" — they're property-wide notices, not part of the
     * hotel's description. Multiple candidate keys per label since TripJack's
     * naming isn't consistent across hotels (singular/plural, etc.).
     */
    protected const BOTTOM_SECTION_LABELS = [
        'attractions' => 'Attractions',
        'renovations' => 'Renovations',
    ];

    protected const OVERVIEW_KEYS = ['overview', 'general description', 'snippet', 'headline'];

    /**
     * TripJack's descriptions.default (and some policies fields) come in
     * three different shapes for the same logical content — confirmed across
     * real responses:
     *   1. Plain prose with no sub-sections at all.
     *   2. A JSON-encoded object, e.g. {"location": "...", "rooms": "...", ...}.
     *   3. Plain text with sub-sections concatenated inline as "Label: text"
     *      runs with no separator ("Location: ...Rooms: ...Attractions: ...") —
     *      seen on apartment-style listings; parsing this wrong is what caused
     *      the huge "Attractions" distance list to show up glued onto the end
     *      of the About This Hotel paragraph instead of its own section.
     * This is the single place that understands all three, splitting into an
     * overview paragraph + "About This Hotel" sub-sections + bottom-of-page
     * notices so every caller sees a consistent, already-separated result.
     *
     * @return array{overview: ?string, sections: array<string,string>, bottom: array<string,string>}
     */
    public function parseDescriptionBlob(?string $value): array
    {
        $result = ['overview' => null, 'sections' => [], 'bottom' => []];

        if (! is_string($value) || trim($value) === '') {
            return $result;
        }
        $value = trim($value);

        // Shape 2: JSON-encoded object of sub-sections.
        if (str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $lowerMap = collect($decoded)->keyBy(fn ($v, $k) => strtolower((string) $k));

                foreach (self::DESCRIPTION_SECTION_LABELS as $key => $label) {
                    $text = $lowerMap->get($key);
                    if (! empty($text) && is_string($text)) {
                        $result['sections'][$label] = trim($text);
                    }
                }

                foreach (self::BOTTOM_SECTION_LABELS as $key => $label) {
                    $text = $lowerMap->get($key);
                    if (! empty($text) && is_string($text)) {
                        $result['bottom'][$label] = trim($text);
                    }
                }

                $overview = collect(self::OVERVIEW_KEYS)
                    ->map(fn ($key) => $lowerMap->get($key))
                    ->first(fn ($v) => ! empty($v) && is_string($v));

                // No dedicated overview key — fall back to any remaining
                // string value not already used above, rather than losing
                // the content entirely.
                if (! $overview && empty($result['sections']) && empty($result['bottom'])) {
                    $overview = collect($decoded)
                        ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                        ->map(fn ($v) => trim($v))
                        ->first();
                }

                // TripJack sometimes wraps the *entire* inline-labelled blob
                // (Location: ...Rooms: ...Attractions: ...) inside a single
                // "General Description"/"overview" key instead of splitting
                // it into real JSON keys — re-run the plain-text splitter on
                // whatever overview text we found so those still come out as
                // their own sections instead of one giant paragraph.
                if ($overview) {
                    $this->mergePlainText(trim($overview), $result);
                }

                return $result;
            }

            // Undecodable JSON-looking string — fall through to plain-text
            // handling below rather than lose it.
        }

        // Shape 1 & 3: plain prose, optionally with inline "Label: ..." markers.
        $this->mergePlainText($value, $result);

        return $result;
    }

    /**
     * Splits plain text into labelled sub-sections (if any are present) and
     * merges them into $result in place; falls back to using the whole text
     * as the overview when no labels are found. Shared by both the JSON
     * "General Description" wrapper case and genuinely plain-text blobs.
     *
     * @param  array{overview: ?string, sections: array<string,string>, bottom: array<string,string>}  $result
     */
    protected function mergePlainText(string $text, array &$result): void
    {
        $inline = $this->splitInlineLabeledSections($text);

        if (empty($inline)) {
            $result['overview'] = $result['overview'] ?? $text;

            return;
        }

        foreach ($inline as $label => $sectionText) {
            $lowerLabel = strtolower($label);
            if (in_array($lowerLabel, self::OVERVIEW_KEYS, true)) {
                $result['overview'] = $result['overview'] ?? $sectionText;
            } elseif ($mapped = self::BOTTOM_SECTION_LABELS[$lowerLabel] ?? null) {
                $result['bottom'][$mapped] = $result['bottom'][$mapped] ?? $sectionText;
            } elseif ($mapped = self::DESCRIPTION_SECTION_LABELS[str_replace(' ', '_', $lowerLabel)] ?? null) {
                $result['sections'][$mapped] = $result['sections'][$mapped] ?? $sectionText;
            }
        }
    }

    /**
     * Splits text where TripJack has concatenated named sections inline with
     * no separator, e.g. "Location: ...Rooms: ...Attractions: ...", into a
     * label => text map (using each label's original casing as the key).
     * Returns [] when none of the known labels appear in the text.
     *
     * @return array<string, string>
     */
    protected function splitInlineLabeledSections(string $text): array
    {
        $labels = array_merge(
            array_map(fn ($l) => Str::title(str_replace('_', ' ', $l)), array_keys(self::DESCRIPTION_SECTION_LABELS)),
            array_map(fn ($l) => Str::title($l), array_keys(self::BOTTOM_SECTION_LABELS)),
            array_map(fn ($l) => Str::title($l), self::OVERVIEW_KEYS),
        );
        // Longest first, so "Business Amenities" matches before "Amenities" does.
        usort($labels, fn ($a, $b) => strlen($b) <=> strlen($a));

        $pattern = '/(?<![A-Za-z])('.implode('|', array_map(fn ($l) => preg_quote($l, '/'), $labels)).'):\s*/';
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($parts) < 3) {
            return [];
        }

        $sections = [];
        // $parts[0] is any text before the first label — usually empty; if
        // there's real content there it has no home, so it's dropped rather
        // than mislabeled.
        for ($i = 1; $i < count($parts); $i += 2) {
            $label = trim($parts[$i]);
            $content = trim($parts[$i + 1] ?? '');
            if ($content !== '') {
                $sections[$label] = $content;
            }
        }

        return $sections;
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
     * TripJack's free-text policy/description fields routinely concatenate
     * separate sentences or fee line-items with no separator at all —
     * confirmed on real responses like "...per stayA tax is imposed..." and
     * "...are:Al Maktoum Intl. Airport...". Rendered verbatim this reads as
     * one run-on wall of text. Inserts the missing boundary rather than
     * leaving guests to parse it themselves; a plain string in, so it's safe
     * to run on anything before it's stored.
     */
    protected function tidyGluedText(string $text): string
    {
        $text = trim($text);
        // Missing space after a colon that's immediately followed by text
        // (but not ":// " style URLs, which don't occur in this content).
        $text = preg_replace('/:(?=\S)/', ': ', $text);
        // A lowercase/digit character directly followed by an uppercase
        // letter starting a new word almost always means two sentences got
        // glued together with no punctuation between them at all.
        $text = preg_replace('/([a-z0-9])([A-Z][a-z])/', '$1. $2', $text);
        // Same glue, but the new sentence starts with a single-letter word
        // ("...per stayA tax is imposed...") — the capital letter is
        // followed by a space rather than a lowercase letter, so the rule
        // above misses it.
        $text = preg_replace('/([a-z0-9])([A-Z]) (?=[a-z])/', '$1. $2 ', $text);
        // Collapse the double/triple spaces TripJack uses as its own
        // internal separator once real punctuation exists to do that job.
        $text = preg_replace('/ {2,}/', ' ', $text);

        return trim($text);
    }

    /**
     * Fee/policy blobs (mandatory_fees, special_instructions,
     * know_before_you_go) are a run of distinct line-items glued into one
     * paragraph — reads far better as a bullet list. Splits on sentence
     * boundaries after tidying and renders as a <ul>; a single-sentence
     * blob just becomes a one-item list, which still reads fine.
     */
    protected function formatAsBulletList(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        $tidied = $this->tidyGluedText($text);
        $sentences = collect(preg_split('/(?<=[.!?])\s+(?=[A-Z0-9])/', $tidied))
            ->map(fn ($s) => trim($s))
            ->filter(fn ($s) => $s !== '')
            ->values();

        if ($sentences->count() <= 1) {
            return e($tidied);
        }

        return '<ul class="hd-fee-list">'.$sentences->map(fn ($s) => '<li>'.e($s).'</li>')->implode('').'</ul>';
    }

    /**
     * "Attractions" text is a very specific shape — an intro sentence,
     * then a long run of "Place Name - X km / Y mi" pairs, then a nearest-
     * airports sentence — that reads far better as a list than as prose.
     * Detects that shape and returns HTML with the places as a <ul>; returns
     * plain tidied text unchanged if the shape doesn't match (e.g. Renovations
     * notices, which are ordinary prose).
     */
    protected function formatPlaceList(string $text): string
    {
        $text = $this->tidyGluedText($text);

        // TripJack always opens with this exact disclaimer sentence — pull
        // it out explicitly first. Without this, the lazy place-name match
        // below (which has to allow "Dubai Intl. Airport" style periods in
        // real place names) greedily swallows this whole sentence as if it
        // were the first place's name, since both end in "...kilometer."
        // and "...mi" with no other delimiter between them.
        $leadingIntro = '';
        if (preg_match('/^(Distances are displayed to the nearest [\d.]+ miles? and kilometers?)\.\s*/i', $text, $m)) {
            $leadingIntro = trim($m[1]).'.';
            $text = trim(Str::after($text, $m[0]));
        }

        // Matches "Some Place Name - 1.2 km / 0.7 mi" repeated, stopping
        // before "The nearest airports are:" or end of string. Capped
        // length keeps a run of prose with no recognizable place boundary
        // from being swallowed whole as a single "place name".
        if (! preg_match_all('/([A-Za-z0-9][A-Za-z0-9 .,\'&()\/-]{1,70}?) - ([\d.]+ km \/ [\d.]+ mi)/', $text, $matches, PREG_SET_ORDER)) {
            $whole = trim($leadingIntro.' '.$text);

            return $whole === '' ? '' : '<p>'.e($whole).'</p>';
        }

        $intro = trim($leadingIntro.' '.trim(Str::before($text, $matches[0][0])));
        $afterPlaces = trim(Str::after($text, end($matches)[0]));

        $html = '';
        if ($intro !== '') {
            $html .= '<p style="margin-bottom:14px;">'.e($intro).'</p>';
        }

        $html .= '<ul class="hd-place-list">';
        foreach ($matches as $match) {
            $html .= '<li><span>'.e(trim($match[1])).'</span><span class="hd-place-list-dist">'.e($match[2]).'</span></li>';
        }
        $html .= '</ul>';

        if ($afterPlaces !== '') {
            // "The nearest airports are:X - .. Y - .." itself repeats the
            // same "Name - distance" shape — recurse once so it renders as
            // its own short list instead of a second run-on line.
            $html .= $this->formatPlaceList($afterPlaces);
        }

        return $html;
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

    /**
     * Upserts one hotel from a static-detail payload into an already-known
     * destination. Public so TripJackMappingSync can reuse the same
     * parsing/normalization logic for NEW/UPDATE mapping-sync rows.
     */
    public function upsertHotel(array $detail, int $destinationId): void
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
        $parsed = $this->parseDescriptionBlob($detail['descriptions']['default'] ?? null);

        // TripJack's top-level descriptions.headline is unreliable as a
        // fallback: it's sometimes a genuine short tagline, but often the
        // exact same Location/Rooms paragraph already shown elsewhere,
        // verbatim — using it wholesale would duplicate that text under
        // "About This Hotel". Only fall back to it when descriptions.default
        // gave us literally nothing (no overview, no sections, no bottom
        // notices), so there's no existing content it could duplicate.
        $description = $parsed['overview'] ?: '';
        $descriptionSections = $parsed['sections'];
        $bottomSections = $parsed['bottom'];

        if ($description === '' && empty($descriptionSections) && empty($bottomSections)) {
            $headlineParsed = $this->parseDescriptionBlob($detail['descriptions']['headline'] ?? null);
            $description = $headlineParsed['overview'] ?: '';
            $descriptionSections = $headlineParsed['sections'];
            $bottomSections = $headlineParsed['bottom'];
        }

        // Attractions/renovations notices have also been observed living in
        // policy fields instead of descriptions.default — merge those in
        // too (descriptions.default takes precedence when both have a value).
        foreach ([$detail['policies']['know_before_you_go'] ?? null, $detail['policies']['special_instructions'] ?? null] as $policyBlob) {
            $bottomSections = array_merge($this->parseDescriptionBlob($policyBlob)['bottom'], $bottomSections);
        }

        $description = $description !== '' ? $this->tidyGluedText($description) : $description;
        $descriptionSections = collect($descriptionSections)->map(fn ($text) => $this->tidyGluedText($text))->all();
        $bottomSections = collect($bottomSections)->map(
            fn ($text, $label) => $label === 'Attractions' ? $this->formatPlaceList($text) : $this->tidyGluedText($text)
        )->all();

        $hotel = Hotel::firstOrNew(['tripjack_hotel_id' => $tjHotelId]);
        $hotel->fill(
            [
                'destination_id' => $destinationId,
                'title' => $name,
                'slug' => $hotel->slug ?? Str::slug($name.'-'.$tjHotelId),
                'description' => $description ?: 'No description available.',
                'description_sections' => ! empty($descriptionSections) ? $descriptionSections : null,
                'bottom_sections' => ! empty($bottomSections) ? $bottomSections : null,
                'category' => 'city_luxury',
                'address' => $address,
                'lat' => $lat,
                'lng' => $lng,
                'star_rating' => max(1, min(5, $starRating ?: 3)),
                'price_from' => 0,
                'source' => 'tripjack',
                'is_active' => (bool) ($detail['is_active'] ?? true),
                'check_in_time' => $this->formatClockTime($detail['policies']['checkInCheckOut']['checkin_from'] ?? null) ?? $hotel->check_in_time ?? '2:00 PM',
                'check_out_time' => $this->formatClockTime($detail['policies']['checkInCheckOut']['checkout_from'] ?? null) ?? $hotel->check_out_time ?? '11:00 AM',
                'mandatory_fees' => $this->formatAsBulletList($this->unwrapJsonBlob($detail['policies']['mandatory_fees'] ?? null, ['mandatory'])),
                'chain_name' => $detail['chain']['name'] ?? null,
                'house_rules' => ! empty($detail['policies']['houseRules']) ? $detail['policies']['houseRules'] : null,
                'special_instructions' => $this->formatAsBulletList($this->unwrapJsonBlob($detail['policies']['special_instructions'] ?? null, ['special instructions', 'instructions'])),
                'know_before_you_go' => $this->formatAsBulletList($this->unwrapJsonBlob($detail['policies']['know_before_you_go'] ?? null, ['know_before_you_go', 'house_rules'])),
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
