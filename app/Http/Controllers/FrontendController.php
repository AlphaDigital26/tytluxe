<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Cruise;
use App\Models\Setting;
use App\Models\Offer;
use App\Models\Package;
use App\Models\Payment;
use App\Jobs\SyncHotelRoomTypes;
use App\Services\HotelPricingService;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackClient;
use App\Services\TripJack\TripJackErrorCatalog;
use App\Services\TripJack\TripJackListingSearch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FrontendController extends Controller
{
    public function index()
    {
        return view('pages.home');
    }

    public function hotels(Request $request, TripJackListingSearch $listingSearch, TripJackClient $client)
    {
        $input = $this->hotelSearchInput($request);
        [$checkIn, $checkOut] = [$input['checkIn'], $input['checkOut']];
        $destinationQuery = trim((string) $request->query('destination', ''));
        // A specific property picked from the search bar's autocomplete
        // (see hotelSearchSuggestions()) — shows just that one hotel as a
        // listing card rather than jumping straight to its detail page, so
        // the guest still lands on a search result they can click into.
        $hotelSlug = trim((string) $request->query('hotel', ''));
        $adults = $input['adults'];
        $children = $input['children'];
        $roomCount = $input['roomCount'];
        $nationality = $input['nationality'];
        // Accepts a single value (pre-search "More Options" pill), a
        // comma-separated string (the post-search sidebar's multi-select
        // checkboxes sync into one hidden field), or an array — star rating
        // filtering itself happens entirely client-side (see hotels.blade.php),
        // this is only used to restore checked/selected state on page load.
        $minRatingRaw = $request->query('min_rating', []);
        $minRatings = collect(is_array($minRatingRaw) ? $minRatingRaw : explode(',', (string) $minRatingRaw))
            ->map(fn ($v) => (int) trim((string) $v))
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
        $minRating = $minRatings[0] ?? 0;
        $childAges = $input['childAges'];

        $hasSearched = $request->has('destination') || $request->has('hotel') || $request->has('check_in') || $request->has('min_rating');
        $searchActive = ($destinationQuery !== '' || $hotelSlug !== '') && $checkIn && $checkOut;
        $searchDestination = null;
        $liveOptions = collect();
        $searchError = null;

        $hotels = collect();
        $hotelsPage = null;

        if ($hasSearched) {
            if ($destinationQuery !== '') {
                $searchDestination = Destination::active()
                    ->where(function ($q) use ($destinationQuery) {
                        $q->where('slug', Str::slug($destinationQuery))
                            ->orWhere('name', 'LIKE', "%{$destinationQuery}%");
                    })
                    ->first();

                if (! $searchDestination) {
                    $searchError = "We don't have hotels in \"{$destinationQuery}\" yet.";
                }
            } else {
                $searchError = 'Please choose a destination to see hotels.';
            }
        }

        // TripJack-style search: price the whole city once, keep only hotels with
        // live availability, then filter/sort/paginate that full result server-side
        // so every filter spans all pages. Only one page of models is ever loaded —
        // big cities have thousands of hotels, and loading them all with their
        // images exhausts PHP's 512MB memory limit.
        $filters = [
            'name' => trim((string) $request->query('name', '')),
            'free_cancel' => $request->boolean('free_cancel'),
            'max_price' => (int) $request->query('max_price', 0),
            'meals' => collect(explode(',', (string) $request->query('meal', '')))
                ->map(fn ($v) => trim($v))
                ->intersect(['room', 'breakfast', 'half', 'full'])
                ->values()
                ->all(),
        ];
        $sort = in_array($request->query('sort'), ['popular', 'price_asc', 'price_desc', 'stars'], true)
            ? $request->query('sort')
            : 'popular';
        $starCounts = collect();
        $livePriced = false;

        if ($searchDestination) {
            if ($input['error'] && ($searchActive || $request->has('check_in'))) {
                // Invalid search (past date, too many guests per room, missing
                // child age, …): say exactly what to change, and don't send
                // TripJack a request it would reject. The destination's hotels
                // are still listed below.
                $searchError = $input['error'];
            } elseif ($searchActive) {
                try {
                    $rooms = $this->distributeGuestsAcrossRooms($adults, $children, $roomCount, $childAges);
                    $result = $this->cityListingSearch($listingSearch, $searchDestination, $checkIn, $checkOut, $rooms, $nationality);
                    $liveOptions = $result['options'];
                    $livePriced = $liveOptions->isNotEmpty();

                    if (! $livePriced) {
                        // TripJack returned a normal 200 with zero priced
                        // options across the whole city — not a request
                        // failure (that's caught below), just no live
                        // inventory for this exact search right now. Logged
                        // distinctly so a full outage is visible in monitoring,
                        // and the visitor is told plainly.
                        Log::channel('tripjack')->info('listing_search_zero_priced', [
                            'destination_id' => $searchDestination->id,
                            'check_in' => $checkIn,
                            'check_out' => $checkOut,
                            'correlationId' => $result['correlationId'],
                        ]);
                        $searchError = 'Live pricing is temporarily unavailable for these dates. The properties below are available to book — enquire and our team will confirm the best rate for you.';
                    }

                    session(['tripjack_search' => [
                        'correlationId' => $result['correlationId'],
                        'destination_id' => $searchDestination->id,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'adults' => $adults,
                        'children' => $children,
                        'rooms' => $roomCount,
                        'nationality' => $nationality,
                        'child_ages' => $childAges,
                    ]]);
                } catch (TripJackException $e) {
                    Log::channel('tripjack')->warning('listing_search_failed', ['message' => $e->getMessage()]);
                    // A 400 means TripJack rejected the search itself (e.g. a
                    // restricted nationality) — tell the guest what was wrong
                    // instead of implying an outage.
                    $searchError = $e instanceof TripJackApiException && (int) $e->status === 400
                        ? 'We couldn’t search with these details: '.preg_replace('/^.*?:\s*/', '', $e->getMessage())
                        : 'Live pricing is temporarily unavailable for this search. Showing our curated listing instead — enquire for the latest rates.';
                }
            }

            $query = Hotel::query()
                ->visibleOnWebsite()
                ->where('destination_id', $searchDestination->id)
                ->when($filters['name'] !== '', fn ($q) => $q->where('title', 'LIKE', '%'.$filters['name'].'%'));

            // Price/meal/cancellation only exist on live options, so they narrow
            // the hid set; without live prices the whole catalogue is listed.
            if ($livePriced) {
                $matchingHids = $liveOptions->filter(function (array $option) use ($filters) {
                    $price = (float) ($option['customerPrice'] ?? 0);
                    $meal = Str::slug($option['mealBasis'] ?? 'none');

                    return ($filters['max_price'] <= 0 || ($price > 0 && $price <= $filters['max_price']))
                        && (! $filters['free_cancel'] || ($option['isRefundable'] ?? false))
                        && (empty($filters['meals']) || collect($filters['meals'])->contains(fn ($m) => str_contains($meal, $m)));
                })->keys()->all();

                $query->whereIn('tripjack_hotel_id', $matchingHids);
            }

            $starCounts = (clone $query)
                ->selectRaw('star_rating, COUNT(*) AS total')
                ->groupBy('star_rating')
                ->pluck('total', 'star_rating');

            $query->when($minRatings, fn ($q) => $q->whereIn('star_rating', $minRatings));
            $withCardData = ['destination', 'amenities', 'images' => Hotel::visibleImagesConstraint()];

            if (in_array($sort, ['price_asc', 'price_desc'], true) && $livePriced) {
                // Prices live in the cached TripJack result, not the DB, so order
                // the lightweight id list in PHP and load just this page's models.
                $ordered = (clone $query)->get(['id', 'tripjack_hotel_id'])
                    ->sortBy(fn ($h) => (float) ($liveOptions->get((string) $h->tripjack_hotel_id)['customerPrice'] ?? 0), SORT_REGULAR, $sort === 'price_desc')
                    ->pluck('id')
                    ->values();
                $perPage = 30;
                $page = \Illuminate\Pagination\Paginator::resolveCurrentPage();
                $pageIds = $ordered->slice(($page - 1) * $perPage, $perPage)->values();
                $pageHotels = Hotel::with($withCardData)->whereIn('id', $pageIds)->get()
                    ->sortBy(fn ($h) => $pageIds->search($h->id))
                    ->values();

                $hotelsPage = new \Illuminate\Pagination\LengthAwarePaginator($pageHotels, $ordered->count(), $perPage, $page, [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]);
            } else {
                if ($sort === 'stars') {
                    $query->orderByDesc('star_rating')->orderByDesc('rating_score')->orderByDesc('review_count');
                } else {
                    // Most Popular: most booked on this site. Cancelled bookings still
                    // count (they were paid for); failed or abandoned checkouts don't.
                    $query->withCount(['bookings as times_booked' => fn ($q) => $q->whereIn('status', ['confirmed', 'cancelled'])])
                        ->orderByDesc('times_booked')
                        ->orderByDesc('star_rating')
                        ->orderByDesc('rating_score')
                        ->orderByDesc('review_count');
                }

                $hotelsPage = $query->with($withCardData)
                    ->orderBy('id')
                    ->paginate(30)
                    ->withQueryString();
            }

            if ($hotelsPage->currentPage() > $hotelsPage->lastPage()) {
                return redirect($hotelsPage->url($hotelsPage->lastPage()));
            }

            $hotels = $hotelsPage->getCollection();
        }
        $nationalities = $this->tripjackNationalities($client);
        $destinations = $this->hotelSearchDestinations();

        return view('pages.hotels', compact(
            'hotels', 'hotelsPage', 'liveOptions', 'searchActive', 'hasSearched', 'searchError',
            'destinationQuery', 'checkIn', 'checkOut', 'adults', 'children', 'roomCount', 'childAges',
            'nationality', 'nationalities', 'minRating', 'minRatings', 'destinations',
            'filters', 'starCounts', 'livePriced', 'sort', 'searchDestination'
        ));
    }

    /**
     * Whole-city live pricing, cached per visitor so filtering, paging and
     * infinite scroll reuse one TripJack search. Per visitor, not shared: the
     * cached correlationId carries through that guest's Detail/Review/Book calls.
     *
     * @return array{correlationId: string, options: \Illuminate\Support\Collection<string, array>}
     */
    private function cityListingSearch(TripJackListingSearch $listingSearch, Destination $destination, string $checkIn, string $checkOut, array $rooms, string $nationality): array
    {
        $cacheKey = 'hotel_city_search:'.session()->getId().':'.md5(json_encode([$destination->id, $checkIn, $checkOut, $rooms, $nationality]));

        if ($cached = Cache::get($cacheKey)) {
            return ['correlationId' => $cached['correlationId'], 'options' => collect($cached['options'])];
        }

        $hids = Hotel::visibleOnWebsite()
            ->where('destination_id', $destination->id)
            ->whereNotNull('tripjack_hotel_id')
            ->pluck('tripjack_hotel_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $result = $listingSearch->searchCity($hids, $checkIn, $checkOut, $rooms, nationality: $nationality);

        if ($result['batches'] > 0 && $result['failedBatches'] === $result['batches']) {
            $first = $result['firstError'] ?? null;
            if ($first) {
                // Carries TripJack's own status/reason (e.g. a 400 for a search
                // it rejects) so hotels() can tell the guest what to change.
                throw new TripJackApiException("Listing rejected: {$first['message']}", status: (int) $first['status'], errorCode: $first['errorCode'] ? (string) $first['errorCode'] : null);
            }
            throw new TripJackException('Every listing batch failed for destination '.$destination->id);
        }

        if ($result['failedBatches'] > 0) {
            Log::channel('tripjack')->warning('city_listing_partial_failure', [
                'destination_id' => $destination->id,
                'batches' => $result['batches'],
                'failed' => $result['failedBatches'],
                'correlationId' => $result['correlationId'],
            ]);
        }

        // A partial result is cached only briefly so missing batches get retried soon.
        Cache::put($cacheKey, [
            'correlationId' => $result['correlationId'],
            'options' => $result['options']->all(),
        ], $result['failedBatches'] > 0 ? now()->addMinutes(2) : now()->addMinutes(15));

        return ['correlationId' => $result['correlationId'], 'options' => $result['options']];
    }

    /**
     * Powers the search bar's "city, area or property" autocomplete — matching
     * hotel titles as the guest types, alongside the destinations list already
     * rendered server-side. Kept separate from hotels() (and only queried via
     * AJAX once the guest has typed 2+ characters) rather than shipping every
     * hotel's title into the page up front, which would bloat every page load
     * just to support an autocomplete that's rarely all scrolled through.
     */
    public function hotelSearchSuggestions(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['hotels' => []]);
        }

        // Matches on the hotel's own name, its full address (covers a
        // locality/area the guest types, e.g. "Business Bay"), or its
        // destination's city/state/country — so typing "Maharashtra" or
        // "Rajasthan" surfaces properties in that state, not just an exact
        // city/hotel name match.
        $hotels = Hotel::visibleOnWebsite()
            ->with('destination')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', '%'.$query.'%')
                    ->orWhere('address', 'LIKE', '%'.$query.'%')
                    ->orWhereHas('destination', function ($dq) use ($query) {
                        $dq->where('name', 'LIKE', '%'.$query.'%')
                            ->orWhere('state', 'LIKE', '%'.$query.'%')
                            ->orWhere('country', 'LIKE', '%'.$query.'%');
                    });
            })
            ->orderBy('title')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'destination_id'])
            ->map(fn ($hotel) => [
                'title' => $hotel->title,
                'slug' => $hotel->slug,
                'city' => $hotel->destination?->name,
                'url' => route('hotel.details', $hotel->slug),
            ])
            ->values();

        return response()->json(['hotels' => $hotels]);
    }

    /**
     * Destinations for the search bar's dropdown — only ones with synced,
     * visible hotels, so every suggestion leads to real results rather than
     * a dead-end "no hotels found" page. Carries `state`/`country` alongside
     * `name` so the dropdown can match "Maharashtra" against Mumbai/Pune/etc,
     * not just an exact city name (see hotelSearchSuggestions() for the same
     * on the live property-search side).
     *
     * @return \Illuminate\Support\Collection<int, array{name:string, state:?string, country:?string}>
     */
    protected function hotelSearchDestinations(): \Illuminate\Support\Collection
    {
        return Destination::active()->whereHas('hotelsOnWebsite')->orderBy('name')
            ->get(['name', 'state', 'country'])
            ->map(fn ($d) => [
                'name' => trim($d->name),
                'state' => $d->state ? trim($d->state) : null,
                'country' => $d->country ? trim($d->country) : null,
            ])
            ->filter(fn ($d) => $d['name'] !== '')
            ->unique(fn ($d) => strtolower($d['name']))
            ->values();
    }

    /**
     * Nationality list: fresh copy cached 24h, plus a last-known-good copy
     * kept indefinitely. When TripJack fails (or returns nothing) the
     * last-known-good list is served and a 1h back-off flag stops every
     * page load from re-calling a down API. India-only is the last resort.
     */
    protected function tripjackNationalities(TripJackClient $client): array
    {
        $cache = \Illuminate\Support\Facades\Cache::store();
        $fallback = fn () => $cache->get('tripjack_nationalities_last_good') ?: [['countryId' => '106', 'countryName' => 'India']];

        if ($fresh = $cache->get('tripjack_nationalities')) {
            return $fresh;
        }
        if ($cache->has('tripjack_nationalities_backoff')) {
            return $fallback();
        }

        try {
            $list = collect($client->nationalityInfo()['nationalityInfos'] ?? [])
                ->sortBy('countryName')
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::channel('tripjack')->warning('nationality_info_failed', ['message' => $e->getMessage()]);
            $list = [];
        }

        if (empty($list)) {
            $cache->put('tripjack_nationalities_backoff', true, now()->addHour());

            return $fallback();
        }

        $cache->put('tripjack_nationalities', $list, now()->addDay());
        $cache->forever('tripjack_nationalities_last_good', $list);

        return $list;
    }

    /** TripJack's documented hotel search limits (Listing/Detail). */
    protected const HOTEL_MAX_ROOMS = 9;

    protected const HOTEL_MAX_ADULTS_PER_ROOM = 6;

    protected const HOTEL_MAX_CHILDREN_PER_ROOM = 4;

    /** Re-search prompt after a Detail/Review (TripJack best practice: ~12 of its ~15 minutes). */
    protected const HOTEL_SESSION_SECONDS = 720;

    /** PAN: AAAAA9999A where the 4th letter is a valid holder type (TripJack error 1092 otherwise). */
    public const PAN_REGEX = 'regex:/^[A-Za-z]{3}[ABCFGHJLPTKEabcfghjlptke][A-Za-z][0-9]{4}[A-Za-z]$/';

    /**
     * A dialling code (digits only, e.g. "91", "44") that appears in
     * TripJack's Nationalities list; anything else falls back to India.
     */
    protected function dialCodeFromInput(string $dialCode): string
    {
        $dialCode = ltrim(preg_replace('/[^\d]/', '', $dialCode), '0');
        if ($dialCode === '91' || $dialCode === '') {
            return '91'; // the default — no list lookup needed
        }
        $known = collect($this->tripjackNationalities(app(TripJackClient::class)))
            ->pluck('dialCode')->filter()->map(fn ($c) => (string) $c);

        return $known->contains($dialCode) ? $dialCode : '91';
    }

    /**
     * The guest's number without its country code — digits only. A number
     * typed with its code ("+91 98765 43210" with code 91) would otherwise
     * be sent to TripJack as "919876543210" alongside code +91.
     */
    protected function localPhoneDigits(string $phone, string $dialCode = '91'): string
    {
        // Digits only, without a leading trunk "0" (e.g. 09876… → 9876…).
        $digits = ltrim(preg_replace('/\D/', '', $phone), '0');
        $minLocal = $dialCode === '91' ? 10 : 6;
        if (str_starts_with($digits, $dialCode) && strlen($digits) - strlen($dialCode) >= $minLocal) {
            $digits = substr($digits, strlen($dialCode));
        }

        return $digits;
    }

    /**
     * One validated hotel search — shared by search, hotel detail and the
     * review/back links, so all three always send TripJack the same,
     * documented-valid request. Query parameters win; $fallback (the
     * session's last search) fills anything missing.
     *
     * Nothing is guessed: an invalid search (past date, too many people per
     * room, a child without an age, …) comes back with `error` set and must
     * not be sent to TripJack — previously these went through and TripJack
     * either rejected them (shown as "pricing unavailable") or, worse,
     * priced the wrong guests.
     *
     * @return array{checkIn:?string, checkOut:?string, adults:int, children:int, roomCount:int, childAges:int[], nationality:string, error:?string}
     */
    protected function hotelSearchInput(Request $request, array $fallback = []): array
    {
        $param = fn (string $key, $default = null) => $request->query($key, $fallback[$key] ?? $default);

        [$checkIn, $checkOut, $error] = $this->normalizeStayDates($param('check_in'), $param('check_out'));

        $roomCount = max(1, min(self::HOTEL_MAX_ROOMS, (int) $param('rooms', 1)));
        $adults = max(1, (int) $param('adults', 2));
        $children = max(0, (int) $param('children', 0));
        $childAges = $request->has('child_ages')
            ? $this->parseChildAges((string) $request->query('child_ages', ''))
            : array_values(array_map('intval', (array) ($fallback['child_ages'] ?? [])));

        $error ??= match (true) {
            (int) $param('rooms', 1) > self::HOTEL_MAX_ROOMS => 'You can book up to '.self::HOTEL_MAX_ROOMS.' rooms at a time.',
            $adults < $roomCount => 'Each room needs at least one adult — please add adults or choose fewer rooms.',
            $adults > $roomCount * self::HOTEL_MAX_ADULTS_PER_ROOM => 'A room can have at most '.self::HOTEL_MAX_ADULTS_PER_ROOM.' adults — please add another room.',
            $children > $roomCount * self::HOTEL_MAX_CHILDREN_PER_ROOM => 'A room can have at most '.self::HOTEL_MAX_CHILDREN_PER_ROOM.' children — please add another room.',
            $children > 0 && count($childAges) !== $children => 'Please select the age of each child — hotels price children by age.',
            default => null,
        };

        return [
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'adults' => $adults,
            'children' => $children,
            'roomCount' => $roomCount,
            'childAges' => array_slice($childAges, 0, $children),
            'nationality' => $this->validNationality((string) $param('nationality', '106')),
            'error' => $error,
        ];
    }

    /**
     * A countryId from TripJack's Nationalities list, or India (106). Any
     * other value would be rejected by TripJack.
     */
    protected function validNationality(string $nationality): string
    {
        if ($nationality === '106' || $nationality === '') {
            return '106'; // the default — no list lookup needed
        }
        $known = collect($this->tripjackNationalities(app(TripJackClient::class)))->pluck('countryId')->map(fn ($id) => (string) $id);

        return $known->contains($nationality) ? $nationality : '106';
    }

    /**
     * Normalises stay dates to TripJack's required YYYY-MM-DD and checks
     * them: check-in must be today or later (IST — TripJack rejects
     * "earlier than today"), and a stay needs at least one night (a
     * check-out on/before check-in is nudged to the next day, as the date
     * pickers would). Returns an error message instead of passing a bad
     * date through.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string} [checkIn, checkOut, error]
     */
    protected function normalizeStayDates(?string $checkIn, ?string $checkOut): array
    {
        if (! $checkIn || ! $checkOut) {
            return [$checkIn ?: null, $checkOut ?: null, null];
        }

        // Strict: the date must read back exactly as given, so overflow like
        // "2026-02-31" (which PHP would roll into March) is rejected.
        $parse = function (string $value): ?\Illuminate\Support\Carbon {
            $value = trim($value);
            foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
                try {
                    $date = \Illuminate\Support\Carbon::createFromFormat('!'.$format, $value, 'Asia/Kolkata');
                } catch (\Throwable) {
                    continue;
                }
                if ($date && $date->format($format) === $value) {
                    return $date;
                }
            }

            return null;
        };

        $in = $parse($checkIn);
        $out = $parse($checkOut);

        if (! $in || ! $out) {
            return [null, null, 'Please choose valid check-in and check-out dates.'];
        }

        if ($in->lt(now('Asia/Kolkata')->startOfDay())) {
            return [$in->format('Y-m-d'), $out->format('Y-m-d'), 'Check-in can’t be in the past — please choose a date from today onwards.'];
        }

        if ($out->lessThanOrEqualTo($in)) {
            $out = $in->copy()->addDay();
        }

        return [$in->format('Y-m-d'), $out->format('Y-m-d'), null];
    }

    protected function parseChildAges(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($v) => trim($v))
            ->filter(fn ($v) => $v !== '' && is_numeric($v))
            ->map(fn ($v) => max(0, min(17, (int) $v)))
            ->values()
            ->all();
    }

    /**
     * @param  int[]  $childAges  Flat pool of real child ages (0-17), consumed
     *                            in order as rooms are filled. TripJack requires
     *                            a real age per child (errors 6528-6530 otherwise)
     *                            and child-rate eligibility can depend on it —
     *                            never guess/hardcode an age.
     * @return array<int, array{adults:int, children?:int, childAge?:int[]}>
     */
    protected function distributeGuestsAcrossRooms(int $adults, int $children, int $roomCount, array $childAges = []): array
    {
        $rooms = [];
        $remainingAdults = $adults;
        $remainingChildren = $children;
        $ageQueue = array_values($childAges);

        for ($i = 0; $i < $roomCount; $i++) {
            $roomsLeft = $roomCount - $i;
            $roomAdults = max(1, (int) ceil($remainingAdults / $roomsLeft));
            $roomChildren = (int) floor($remainingChildren / $roomsLeft);

            $room = ['adults' => $roomAdults];
            if ($roomChildren > 0) {
                $room['children'] = $roomChildren;
                $ages = array_splice($ageQueue, 0, $roomChildren);
                // Ages are never guessed: hotelSearchInput() rejects a search
                // without one age per child before anything reaches TripJack.
                // Without ages (only the guest-form slot counts need this),
                // childAge is left off rather than invented.
                if (count($ages) === $roomChildren) {
                    $room['childAge'] = array_map('intval', $ages);
                }
            }

            $rooms[] = $room;
            $remainingAdults -= $roomAdults;
            $remainingChildren -= $roomChildren;
        }

        return $rooms;
    }

    /**
     * Derives per-room {adults, children} slots for rendering the guest form
     * and building the Book request. Prefers TripJack's own confirmed
     * roomInfo[] from the reviewed option — the source of truth for exactly
     * how many travellers each room expects — over recomputing our own
     * even-split from the stored adults/children/rooms counts, which could
     * drift from what was actually reviewed and trigger
     * TRAVELLER_COUNT_MISMATCH (6509) at Book time.
     *
     * @return array<int, array{adults:int, children?:int}>
     */
    protected function roomSlotsFromDraft(array $draft): array
    {
        $roomInfo = $draft['option']['roomInfo'] ?? [];

        $hasReliableCounts = ! empty($roomInfo) && collect($roomInfo)->every(
            fn ($room) => array_key_exists('adults', $room) && (int) $room['adults'] > 0
        );

        if ($hasReliableCounts) {
            return collect($roomInfo)->map(function ($room) {
                $slot = ['adults' => (int) $room['adults']];
                if (! empty($room['children'])) {
                    $slot['children'] = (int) $room['children'];
                }

                return $slot;
            })->all();
        }

        // TripJack's roomInfo didn't include per-room adults/children (some
        // response variants omit it) — fall back to our own even split.
        return $this->distributeGuestsAcrossRooms($draft['adults'], $draft['children'], $draft['rooms']);
    }

    /**
     * Fills blanks on the logged-in guest's profile from freshly-validated
     * booking details — never overwrites anything they've already set
     * themselves, and never lets a profile hiccup (e.g. a duplicate phone
     * number already claimed by another account) break the booking itself.
     */
    protected function syncProfileFromBooking(\App\Models\User $user, array $validated, bool $panRequired): void
    {
        try {
            $dirty = false;

            if (empty($user->phone) && ! \App\Models\User::where('phone', $validated['contact_phone'])->where('id', '!=', $user->id)->exists()) {
                $user->phone = $validated['contact_phone'];
                $dirty = true;
            }

            if ($panRequired && ! empty($validated['pan_number'])) {
                $govtIds = $user->govt_ids ?? [];
                $hasPan = collect($govtIds)->contains(fn ($id) => ($id['type'] ?? null) === 'PAN Card');
                if (! $hasPan) {
                    $govtIds[] = ['type' => 'PAN Card', 'number' => strtoupper($validated['pan_number']), 'name' => $validated['pan_name'] ?? null];
                    $user->govt_ids = $govtIds;
                    $dirty = true;
                }
            }

            if ($dirty) {
                $user->save();
            }
        } catch (\Throwable $e) {
            Log::warning('profile_sync_from_booking_failed', ['user_id' => $user->id, 'message' => $e->getMessage()]);
        }
    }

    public function wishlist(Request $request)
    {
        $featuredHotels = Hotel::with(['destination', 'images' => Hotel::visibleImagesConstraint()])
            ->visibleOnWebsite()
            ->where('is_featured', true)
            ->take(4)
            ->get();

        if ($featuredHotels->isEmpty()) {
            $featuredHotels = Hotel::with(['destination', 'images' => Hotel::visibleImagesConstraint()])
                ->visibleOnWebsite()
                ->latest()
                ->take(4)
                ->get();
        }

        $destinations = Destination::active()->orderBy('name')->take(8)->get();

        return view('pages.wishlist', compact('featuredHotels', 'destinations'));
    }

    public function wishlistLookup(Request $request)
    {
        $slugs = (array) $request->input('slugs', []);
        if (empty($slugs)) {
            return response()->json(['hotels' => []]);
        }

        $hotels = Hotel::with(['destination', 'images' => Hotel::visibleImagesConstraint()])
            ->visibleOnWebsite()
            ->whereIn('slug', $slugs)
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'slug' => $h->slug,
                    'title' => $h->title,
                    'destination' => $h->destination?->name ?? $h->address ?? '',
                    'stars' => (int) ($h->star_rating ?? 5),
                    'price' => $h->price_from ? '₹'.number_format($h->price_from) : 'Price on Request',
                    'image' => $h->featured_image ?: ($h->images->first()?->image_url ?? ''),
                    'url' => route('hotel.details', $h->slug),
                ];
            });

        return response()->json(['hotels' => $hotels]);
    }

    public function hotelDetails($slug, Request $request, TripJackClient $client)
    {
        $hotel = Hotel::with(['destination', 'amenities', 'images' => Hotel::visibleImagesConstraint(), 'roomTypes', 'reviews'])
            ->visibleOnWebsite()
            ->where('slug', $slug)
            ->firstOrFail();

        // Room-type data now gets queued automatically as soon as a hotel is
        // created/updated (see TripJackHotelSync::upsertHotel()) and kept
        // fresh by a rolling resync command — this is just a safety net for
        // any hotel that slipped through both. Dispatches a background job
        // instead of fetching inline, so this guest's own page load never
        // waits on a TripJack call; they'll just see it on their next visit.
        if ($hotel->roomTypes->isEmpty()) {
            SyncHotelRoomTypes::dispatchIfNeeded($hotel);
        }

        $sessionSearch = session('tripjack_search') ?? [];
        $input = $this->hotelSearchInput($request, $sessionSearch);
        [$checkIn, $checkOut] = [$input['checkIn'], $input['checkOut']];
        $adults = $input['adults'];
        $children = $input['children'];
        $roomCount = $input['roomCount'];
        $childAges = $input['childAges'];
        $nationality = $input['nationality'];

        $liveOptions = collect();
        $pricingError = null;
        $correlationId = null;

        if ($hotel->source === 'tripjack' && $hotel->tripjack_hotel_id && $input['error'] && ($checkIn || $request->has('check_in'))) {
            // Same validation as the search page — never price an invalid
            // search (it would be rejected, or price the wrong guests).
            $pricingError = $input['error'];
        } elseif ($hotel->source === 'tripjack' && $hotel->tripjack_hotel_id && $checkIn && $checkOut) {
            // TripJack: Detail must carry the Listing's correlationId, and its
            // dates/rooms/nationality must match that Listing call. Reuse it
            // only when this is still exactly the search that found the
            // hotel; any change (dates, guests, ages, nationality, another
            // destination) is a new search journey with a fresh id.
            $sameSearch = ! empty($sessionSearch['correlationId'])
                && ($sessionSearch['destination_id'] ?? null) === $hotel->destination_id
                && ($sessionSearch['check_in'] ?? null) === $checkIn
                && ($sessionSearch['check_out'] ?? null) === $checkOut
                && (int) ($sessionSearch['adults'] ?? 0) === $adults
                && (int) ($sessionSearch['children'] ?? 0) === $children
                && (int) ($sessionSearch['rooms'] ?? 0) === $roomCount
                && array_values(array_map('intval', $sessionSearch['child_ages'] ?? [])) === $childAges
                && (string) ($sessionSearch['nationality'] ?? '106') === $nationality;
            $correlationId = $sameSearch ? $sessionSearch['correlationId'] : TripJackClient::newCorrelationId();

            try {
                $rooms = $this->distributeGuestsAcrossRooms($adults, $children, $roomCount, $childAges);
                $response = $client->pricing($hotel->tripjack_hotel_id, $checkIn, $checkOut, $rooms, $correlationId, nationality: $nationality);
                // Add the customer-facing price (TripJack's raw totalPrice + TYTLUXE markup)
                // to every option here, once, so every view downstream reads it rather than
                // re-deriving it from the raw TripJack price.
                $liveOptions = collect($response['options'] ?? [])->map(fn ($option) => $this->withCustomerPricing($option));
                $reviewHash = $response['reviewHash'] ?? null;

                session(["tripjack_pricing.{$hotel->tripjack_hotel_id}" => [
                    'correlationId' => $correlationId,
                    'reviewHash' => $reviewHash,
                    'hid' => $hotel->tripjack_hotel_id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'adults' => $adults,
                    'children' => $children,
                    'rooms' => $roomCount,
                    // Carried into Review / the back link so a retry re-prices
                    // the same guests (H3-6/H3-7) instead of defaults.
                    'child_ages' => $childAges,
                    'nationality' => $nationality,
                    // The price each option was shown at, so Review can tell
                    // the guest if it moved since they picked it.
                    'option_prices' => $liveOptions->mapWithKeys(fn ($o) => [($o['optionId'] ?? '') => $o['pricing']['totalPrice'] ?? null])->filter()->all(),
                    // TripJack's own docs: a search/pricing session is valid
                    // for ~15 minutes. Drives the on-page countdown so a
                    // guest never books off a price TripJack would silently
                    // reject as stale at Review time.
                    'fetched_at' => now()->timestamp,
                ]]);
            } catch (TripJackException $e) {
                Log::channel('tripjack')->warning('pricing_failed', ['hid' => $hotel->tripjack_hotel_id, 'message' => $e->getMessage()]);
                $pricingError = 'Live rates are temporarily unavailable for this hotel right now. Please try again in a moment.';
            }
        }

        $destinations = $this->hotelSearchDestinations();

        // Drives the on-page price-freshness countdown — null when there's
        // no live pricing session to time out in the first place. TripJack's
        // session lasts ~15 min; their best practice is to prompt a re-search
        // at ~12 so a guest never picks a rate that then expires at Review.
        $pricingExpiresAt = $liveOptions->isNotEmpty()
            ? session("tripjack_pricing.{$hotel->tripjack_hotel_id}.fetched_at", now()->timestamp) + self::HOTEL_SESSION_SECONDS
            : null;

        return view('pages.hotel-details', compact(
            'hotel', 'liveOptions', 'pricingError', 'checkIn', 'checkOut', 'adults', 'children', 'roomCount', 'childAges', 'nationality', 'destinations', 'pricingExpiresAt'
        ));
    }

    /**
     * Adds TYTLUXE's customer price (TripJack's totalPrice + markup) and the
     * breakdown to one Detail/Review option, once, so every view reads it
     * rather than re-deriving it. mf/mft are carried for TripJack's
     * "show them as separate line items" rule — already inside totalPrice,
     * so display-only; they never change the markup.
     *
     * Also normalises deadlineDateTime: the docs put it under `cancellation`,
     * live Review responses put it on the option itself.
     */
    protected function withCustomerPricing(array $option): array
    {
        if (isset($option['pricing']['totalPrice'])) {
            $breakdown = HotelPricingService::price((float) $option['pricing']['totalPrice']);
            $breakdown['tripjack_mf'] = round((float) ($option['pricing']['mf'] ?? 0), 2);
            $breakdown['tripjack_mft'] = round((float) ($option['pricing']['mft'] ?? 0), 2);
            $option['pricing']['pricingBreakdown'] = $breakdown;
            $option['pricing']['customerPrice'] = $breakdown['customer_price'];

            // The strikethrough is in TripJack's raw terms — mark it up the
            // same way, or it's compared against (and shown below) a price
            // that already includes our margin.
            $strike = (float) ($option['pricing']['strikethrough'] ?? 0);
            $option['pricing']['customerStrikethrough'] = $strike > (float) $option['pricing']['totalPrice']
                ? HotelPricingService::price($strike)['customer_price']
                : null;
        }

        $option['deadlineDateTime'] ??= $option['cancellation']['deadlineDateTime'] ?? null;

        // Penalty slab times are IST with no offset; the app runs in UTC,
        // so parse them explicitly as IST. Free cancellation only counts if
        // its zero-penalty slab hasn't already ended.
        $now = now();
        $freeUntil = null;
        foreach ($option['cancellation']['penalties'] ?? [] as $slab) {
            $to = $this->parseIst($slab['to'] ?? null);
            if ((float) ($slab['amount'] ?? 1) <= 0 && $to && $to->isAfter($now)) {
                $freeUntil = $to;
                break;
            }
        }
        $option['cancellation']['freeCancelUntil'] = $freeUntil?->toIso8601String();

        return $option;
    }

    /** Parses a TripJack timestamp (IST, no offset) — null if unparseable. */
    protected function parseIst(?string $value): ?\Illuminate\Support\Carbon
    {
        if (! $value) {
            return null;
        }
        try {
            return \Illuminate\Support\Carbon::parse($value, 'Asia/Kolkata');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Re-runs Review for the draft's option right before the guest is sent
     * to pay (TripJack: Review immediately before Book). Returns the
     * refreshed draft — with Review's NEW bookingId, which is what Book must
     * use — or a redirect:
     *  - sold out / expired / not found → back to the hotel to choose again;
     *  - price changed → back to the review page showing the new total, so
     *    the guest confirms it before paying (never charged a price they
     *    didn't see).
     * A TripJack outage doesn't block payment: Book re-validates after
     * payment anyway, and a failed Book is refunded automatically.
     */
    protected function refreshHotelReview(TripJackClient $client, Hotel $hotel, array $draft): array|\Illuminate\Http\RedirectResponse
    {
        $optionId = $draft['option']['optionId'] ?? null;
        if (empty($draft['reviewHash']) || ! $optionId) {
            return $draft; // a draft from before this check existed
        }

        $backToHotel = fn (string $message) => redirect()
            ->route('hotel.details', $this->hotelDetailsParams($hotel->slug, $draft))
            ->with('booking_error', $message);

        try {
            $response = $client->review($draft['correlationId'], $optionId, $draft['reviewHash'], (string) $hotel->tripjack_hotel_id);
        } catch (TripJackApiException $e) {
            $described = TripJackErrorCatalog::describe($e->errorCode, 'This room is no longer available at this rate. Please choose again.');
            $this->logTripjackFailure($described['logLevel'], 'rereview_failed', ['hid' => $hotel->tripjack_hotel_id, 'optionId' => $optionId, 'errorCode' => $e->errorCode, 'message' => $e->getMessage()]);

            return $backToHotel($described['message']);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('rereview_skipped', ['hid' => $hotel->tripjack_hotel_id, 'message' => $e->getMessage()]);

            return $draft;
        }

        if (! ($response['status']['success'] ?? false) || empty($response['bookingId'])) {
            $described = TripJackErrorCatalog::describe(TripJackErrorCatalog::codeFromResponse($response), 'This room is no longer available at this rate. Please choose again.');

            return $backToHotel($described['message']);
        }

        $oldTotal = (float) ($draft['option']['pricing']['totalPrice'] ?? 0);
        $oldCustomerPrice = (float) ($draft['option']['pricing']['customerPrice'] ?? HotelPricingService::price($oldTotal)['customer_price']);
        $option = $this->withCustomerPricing($response['option'] ?? []);
        $newTotal = (float) ($option['pricing']['totalPrice'] ?? 0);

        $draft['bookingId'] = $response['bookingId'];
        $draft['option'] = $option;
        $draft['reviewed_at'] = now()->timestamp;

        if (abs($newTotal - $oldTotal) >= 0.01) {
            $draft['price_change'] = [
                'old' => $oldCustomerPrice,
                'new' => $option['pricing']['customerPrice'],
            ];
            session(['tripjack_booking_draft' => $draft]);

            return redirect()->route('hotel.review.show', $hotel->slug)
                ->withInput()
                ->with('booking_error', sprintf('The hotel just changed this rate from ₹%s to ₹%s. Please check the new total and confirm again to continue.', number_format($draft['price_change']['old'], 2), number_format($draft['price_change']['new'], 2)));
        }

        session(['tripjack_booking_draft' => $draft]);

        return $draft;
    }

    /**
     * Query parameters that reproduce a hotel detail search — dates, guests,
     * child ages and nationality — so a "back to the hotel" redirect
     * re-prices the same guests instead of falling back to defaults.
     */
    protected function hotelDetailsParams(string $slug, array $context, ?Request $request = null): array
    {
        $params = [
            'slug' => $slug,
            'check_in' => $context['check_in'] ?? $request?->input('check_in'),
            'check_out' => $context['check_out'] ?? $request?->input('check_out'),
            'adults' => $context['adults'] ?? $request?->input('adults'),
            'children' => $context['children'] ?? $request?->input('children'),
            'rooms' => $context['rooms'] ?? $request?->input('rooms'),
            'child_ages' => ! empty($context['child_ages']) ? implode(',', $context['child_ages']) : $request?->input('child_ages'),
            'nationality' => $context['nationality'] ?? $request?->input('nationality'),
        ];

        return array_filter($params, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Phase 6 — Review: re-validates the selected option's price/availability
     * immediately before booking and hands back the bookingId Phase 7 needs.
     * Never book off stale listing/pricing data — this call is mandatory.
     */
    public function reviewRoom($slug, Request $request, TripJackClient $client)
    {
        $hotel = Hotel::visibleOnWebsite()->where('slug', $slug)->firstOrFail();

        if ($hotel->source !== 'tripjack' || ! $hotel->tripjack_hotel_id) {
            abort(404);
        }

        $request->validate(['option_id' => 'required|string']);
        $optionId = $request->input('option_id');

        $pricingContext = session("tripjack_pricing.{$hotel->tripjack_hotel_id}");
        $backToDetails = redirect()->route('hotel.details', $this->hotelDetailsParams($slug, $pricingContext ?? [], $request));
        if (! $pricingContext || empty($pricingContext['reviewHash'])) {
            return $backToDetails->with('booking_error', 'Your search session has expired. Please select your dates again.');
        }

        try {
            $response = $client->review(
                $pricingContext['correlationId'],
                $optionId,
                $pricingContext['reviewHash'],
                $hotel->tripjack_hotel_id
            );
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackErrorCatalog::describe($errorCode);
            $this->logTripjackFailure($described['logLevel'], 'review_failed', ['hid' => $hotel->tripjack_hotel_id, 'optionId' => $optionId, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);

            return $backToDetails->with('booking_error', $described['message']);
        }

        if (! ($response['status']['success'] ?? false) || empty($response['bookingId'])) {
            $errorCode = TripJackErrorCatalog::codeFromResponse($response);
            $described = TripJackErrorCatalog::describe($errorCode, 'This room is no longer available. Please pick another option.');
            $this->logTripjackFailure($described['logLevel'], 'review_unsuccessful', ['hid' => $hotel->tripjack_hotel_id, 'optionId' => $optionId, 'errorCode' => $errorCode, 'response' => $response]);

            return $backToDetails->with('booking_error', $described['message']);
        }

        // onholdAllowed is irrelevant since Phase 8: we never request a HOLD
        // any more — Book is always called with paymentInfos (payment
        // happens first), so TripJack's HOLD_NOT_ALLOWED (6537) restriction
        // doesn't apply here. Rejecting on it was a leftover from the old
        // HOLD-only flow that incorrectly blocked perfectly payable options.

        // Review always returns a freshly re-validated totalPrice — the price
        // can have moved since the guest first saw it at Listing/Detail time.
        // Recalculate the TYTLUXE markup here, from this price, and never
        // reuse a markup figure computed at an earlier step (per founder's
        // pricing rules) — this recalculated value is what the guest sees on
        // the review/checkout page and what ultimately gets booked/charged.
        $option = $this->withCustomerPricing($response['option'] ?? []);

        // Detail → Review can move the price. The guest picked a room at the
        // Detail price, so tell them on the review page rather than silently
        // showing a different total.
        $pickedTotal = $pricingContext['option_prices'][$optionId] ?? null;
        $reviewedTotal = $option['pricing']['totalPrice'] ?? null;
        $priceChange = ($pickedTotal !== null && $reviewedTotal !== null && abs((float) $reviewedTotal - (float) $pickedTotal) >= 0.01)
            ? ['old' => HotelPricingService::price((float) $pickedTotal)['customer_price'], 'new' => $option['pricing']['customerPrice']]
            : null;

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id,
            'hid' => $hotel->tripjack_hotel_id,
            'bookingId' => $response['bookingId'],
            'option' => $option,
            'correlationId' => $pricingContext['correlationId'],
            // Kept so the rate can be re-reviewed right before payment
            // (TripJack: call Review immediately before Book).
            'reviewHash' => $pricingContext['reviewHash'],
            'reviewed_at' => now()->timestamp,
            'price_change' => $priceChange,
            'check_in' => $pricingContext['check_in'],
            'check_out' => $pricingContext['check_out'],
            'adults' => $pricingContext['adults'],
            'children' => $pricingContext['children'],
            'rooms' => $pricingContext['rooms'],
            'child_ages' => $pricingContext['child_ages'] ?? [],
            'nationality' => $pricingContext['nationality'] ?? '106',
        ]]);

        // Post/Redirect/Get: never render this page as a direct POST response —
        // a refresh, bookmark, or revisit would otherwise re-send the POST body
        // (or 405, since only POST is routed) instead of just re-showing the
        // already-reviewed draft.
        return redirect()->route('hotel.review.show', $slug);
    }

    /**
     * GET counterpart of reviewRoom() — renders the confirmed option already
     * stored in session by the POST above. No TripJack call here; refreshing
     * this page is free and safe.
     */
    public function showReview($slug)
    {
        $hotel = Hotel::visibleOnWebsite()->where('slug', $slug)->firstOrFail();
        $draft = session('tripjack_booking_draft');

        if (! $draft || $draft['hotel_id'] !== $hotel->id) {
            return redirect()->route('hotel.details', $slug)->with('booking_error', 'Your booking session has expired. Please select a room again.');
        }

        $roomSlots = $this->roomSlotsFromDraft($draft);
        $option = $draft['option'] ?? [];
        $panRequired = $option['compliance']['panRequired'] ?? $option['ipr'] ?? false;
        $passportRequired = $option['compliance']['passportRequired'] ?? $option['ipm'] ?? false;

        // Same TripJack-room-id-first, name-fallback match used on the room
        // selection page (hotel-details.blade.php) so the photo the guest
        // picked a room by carries over to this confirm/pay step instead of
        // vanishing — this page previously showed no room photo at all.
        $localRoom = null;
        $optionRoomCodes = collect($option['roomInfo'] ?? [])->pluck('id')->filter()->unique();
        if ($optionRoomCodes->isNotEmpty()) {
            $localRoom = $hotel->roomTypes->firstWhere(fn ($rt) => $optionRoomCodes->contains($rt->tripjack_room_code));
        }
        if (! $localRoom) {
            $roomNameForMatch = collect($option['roomInfo'] ?? [])->pluck('name')->unique()->implode(' + ');
            $localRoom = $hotel->roomTypes->first(
                fn ($rt) => $roomNameForMatch && (str_contains(strtolower($roomNameForMatch), strtolower($rt->name)) || str_contains(strtolower($rt->name), strtolower($roomNameForMatch)))
            );
        }
        $roomImage = match (true) {
            ! empty($localRoom?->image_path) && Str::startsWith($localRoom->image_path, ['http://', 'https://']) => $localRoom->image_path,
            ! empty($localRoom?->image_path) => Storage::disk('public')->url($localRoom->image_path),
            default => $hotel->images->first()?->path,
        };
        if ($roomImage && ! Str::startsWith($roomImage, ['http://', 'https://'])) {
            $roomImage = Storage::disk('public')->url($roomImage);
        }

        return view('pages.hotel-review', [
            'hotel' => $hotel,
            'option' => $option,
            'bookingId' => $draft['bookingId'],
            'roomSlots' => $roomSlots,
            'panRequired' => $panRequired,
            'passportRequired' => $passportRequired,
            'roomImage' => $roomImage,
            'draft' => $draft,
            // Lets a returning guest pick a saved co-traveller instead of
            // retyping name/passport for every room — see the picker in the
            // traveler fields.
            'savedTravellers' => auth()->user()->savedTravellers,
            'gstRequired' => in_array($option['compliance']['gstType'] ?? null, ['PASSTHROUGH', 'RESELLER'], true),
            'dialCodes' => $this->dialCodeOptions(),
            'sessionSeconds' => self::HOTEL_SESSION_SECONDS,
        ]);
    }

    /**
     * Country-code picker options from TripJack's Nationalities list
     * (dialCode), India first: ["91" => "IN +91", ...].
     *
     * @return array<string, string>
     */
    protected function dialCodeOptions(): array
    {
        $options = ['91' => 'IN +91'];
        foreach ($this->tripjackNationalities(app(TripJackClient::class)) as $n) {
            $code = ltrim((string) ($n['dialCode'] ?? ''), '+');
            if ($code !== '' && ! isset($options[$code])) {
                $options[$code] = ($n['code'] ?? '').' +'.$code;
            }
        }

        return $options;
    }

    /**
     * Phase 8 — guest submits details; we create the local Booking +
     * Razorpay order and send them to pay. TripJack's Book API is NOT called
     * here — it's only called once payment is captured (see
     * confirmBookingAfterPayment()), with paymentInfos, for a real/instant
     * booking rather than a HOLD.
     */
    public function submitBooking($slug, Request $request, RazorpayService $razorpay, TripJackClient $client)
    {
        $hotel = Hotel::visibleOnWebsite()->where('slug', $slug)->firstOrFail();
        $draft = session('tripjack_booking_draft');

        if (! $draft || $draft['hotel_id'] !== $hotel->id) {
            return redirect()->route('hotel.details', $slug)->with('booking_error', 'Your booking session has expired. Please select a room again.');
        }

        $option = $draft['option'] ?? [];
        // TripJack's docs are inconsistent about field naming across endpoints
        // (compliance.panRequired in Detail/Review vs. compact ipr/ipm in
        // Booking Details) — accept either so we're not blindsided again.
        $panRequired = $option['compliance']['panRequired'] ?? $option['ipr'] ?? false;
        $passportRequired = $option['compliance']['passportRequired'] ?? $option['ipm'] ?? false;
        // TripJack: for PASSTHROUGH / RESELLER GST rates, Book must carry
        // gstInfo {gstNumber, registeredName}. TripJack never returns those
        // details itself (only the gstType flag), so the guest provides them
        // on the review page — without them Book is rejected after payment.
        $gstType = $option['compliance']['gstType'] ?? null;
        $gstRequired = in_array($gstType, ['PASSTHROUGH', 'RESELLER'], true);
        $roomSlots = $this->roomSlotsFromDraft($draft);

        // A real PAN: 5 letters, 4 digits, 1 letter — and the 4th letter is
        // the holder type (P individual, C company, H HUF, F firm, A AOP,
        // T trust, B BOI, L local authority, J juridical person, G govt,
        // K/E). TripJack rejects anything else with 1092 "enter valid PAN"
        // — previously only AFTER the guest paid (every live attempt failed
        // this way with the shape-only check).
        $panRegex = self::PAN_REGEX;
        $nameRegex = "regex:/^[A-Za-z\\s.'-]+$/"; // letters/spaces/., ' and - only — rejects digits and stray symbols
        $dialCode = $this->dialCodeFromInput((string) $request->input('contact_dial_code', '91'));
        $phoneRule = function ($attribute, $value, $fail) use ($dialCode) {
            $digits = $this->localPhoneDigits((string) $value, $dialCode);
            // Indian numbers are 10 digits; other countries vary (E.164
            // allows up to 15 including the country code).
            $valid = $dialCode === '91'
                ? (bool) preg_match('/^\d{10}$/', $digits)
                : (bool) preg_match('/^\d{6,'.(15 - strlen($dialCode)).'}$/', $digits);
            if (! $valid) {
                $fail($dialCode === '91' ? 'Please enter a valid 10-digit mobile number.' : 'Please enter a valid mobile number for +'.$dialCode.'.');
            }
        };

        $rules = [
            'contact_email' => 'required|email|max:255',
            'contact_phone' => ['required', 'string', 'max:20', $phoneRule],
            'contact_dial_code' => 'nullable|string|max:6',
            'pan_name' => [$panRequired ? 'required' : 'nullable', 'string', 'max:255', $nameRegex],
            'pan_number' => [$panRequired ? 'required' : 'nullable', 'string', $panRegex],
            'gst_number' => [$gstRequired ? 'required' : 'nullable', 'string', 'regex:/^[0-9]{2}[A-Za-z]{5}[0-9]{4}[A-Za-z][1-9A-Za-z]Z[0-9A-Za-z]$/'],
            'gst_registered_name' => [$gstRequired ? 'required' : 'nullable', 'string', 'max:100'],
            'special_requests' => 'nullable|string|max:500',
            'rooms' => 'required|array',
        ];
        foreach ($roomSlots as $ri => $slot) {
            $count = $slot['adults'] + ($slot['children'] ?? 0);
            for ($ti = 0; $ti < $count; $ti++) {
                // TripJack accepts Mr/Mrs/Ms/Miss/Master only; an adult can't
                // be "Master"/"Miss" or a child "Mr" (same split the form offers).
                $allowedTitles = $ti < $slot['adults'] ? 'Mr,Mrs,Ms' : 'Master,Miss';
                $rules["rooms.{$ri}.travelers.{$ti}.title"] = 'required|string|in:'.$allowedTitles;
                $rules["rooms.{$ri}.travelers.{$ti}.first_name"] = ['required', 'string', 'max:100', $nameRegex];
                $rules["rooms.{$ri}.travelers.{$ti}.last_name"] = ['required', 'string', 'max:100', $nameRegex];
                $rules["rooms.{$ri}.travelers.{$ti}.save_to_list"] = 'nullable|boolean';
                if ($passportRequired) {
                    $rules["rooms.{$ri}.travelers.{$ti}.passport_number"] = ['required', 'string', 'regex:/^[A-Za-z0-9]{6,20}$/'];
                }
            }
        }
        $validated = $request->validate($rules, [
            'pan_number.regex' => 'Please enter a valid PAN, e.g. ABCPE1234F — the 4th letter is the holder type (P for an individual).',
            'gst_number.regex' => 'Please enter a valid 15-character GSTIN.',
        ]);
        $gstInfo = $gstRequired ? [
            'gstNumber' => strtoupper($validated['gst_number']),
            'registeredName' => $validated['gst_registered_name'],
        ] : null;

        // TripJack requires the lead (first) traveler's name to be unique
        // across rooms — reject before calling Book, not after it fails there.
        $leadNames = collect($validated['rooms'])->map(
            fn ($room) => strtolower(trim(($room['travelers'][0]['first_name'] ?? '').' '.($room['travelers'][0]['last_name'] ?? '')))
        );
        if ($leadNames->duplicates()->isNotEmpty()) {
            return back()->withInput()->withErrors(['rooms' => 'Each room\'s primary guest must have a different name. Please vary the lead guest name per room.']);
        }

        $roomTravellerInfo = [];
        foreach ($roomSlots as $ri => $slot) {
            $travellerInfo = [];
            $adultCount = $slot['adults'];
            $totalCount = $adultCount + ($slot['children'] ?? 0);
            for ($ti = 0; $ti < $totalCount; $ti++) {
                $t = $validated['rooms'][$ri]['travelers'][$ti];
                $entry = [
                    'ti' => $t['title'],
                    'pt' => $ti < $adultCount ? 'ADULT' : 'CHILD',
                    'fN' => $t['first_name'],
                    'lN' => $t['last_name'],
                ];
                if ($panRequired) {
                    $entry['pan'] = $validated['pan_number'];
                    $entry['panName'] = $validated['pan_name'];
                }
                if ($passportRequired) {
                    $entry['pNum'] = $t['passport_number'] ?? null;
                }
                $travellerInfo[] = $entry;
            }
            $roomTravellerInfo[] = ['travellerInfo' => $travellerInfo];
        }

        // A re-review (below) mints a new bookingId each time, so the unique
        // tripjack_hold_id no longer stops a double-submit on its own — this
        // short lock on the review the guest is completing does.
        if (! Cache::lock('hotel_submit:'.$draft['bookingId'], 60)->get()) {
            return redirect()->route('hotel.review.show', $slug)->with('booking_error', 'Your booking is already being processed — please wait a moment.');
        }

        // TripJack: "Always call Review immediately before Book." Book can
        // only run after payment, so the closest we can get is re-reviewing
        // right before sending the guest to pay: a sold-out or expired rate
        // is caught here instead of failing Book after the guest has paid.
        $refreshed = $this->refreshHotelReview($client, $hotel, $draft);
        if ($refreshed instanceof \Illuminate\Http\RedirectResponse) {
            return $refreshed;
        }
        $draft = $refreshed;
        $option = $draft['option'];

        $pricing = $option['pricing'] ?? [];
        // pricingBreakdown comes from the review just above — the exact rate
        // the guest is shown and charged (any change sent them back to
        // confirm the new total first).
        $breakdown = $pricing['pricingBreakdown'] ?? null;
        $customerPrice = $pricing['customerPrice'] ?? ($pricing['totalPrice'] ?? 0);
        $basePrice = $pricing['basePrice'] ?? 0;
        // No local RoomType row exists for a live TripJack option (that
        // catalog is only for the manually-added "Request Price" rooms), so
        // room_type_id stays null below — this is the only place the room
        // name is available at all, straight from TripJack's review response.
        $roomName = \App\Support\RoomLabel::forOption($option) ?: null;

        // No separate "Lead Guest" field is collected any more — the first
        // traveler on the first room stands in as the booking's primary
        // guest name (used on the invoice, admin panel, etc.).
        $leadGuestName = trim(
            ($validated['rooms'][0]['travelers'][0]['first_name'] ?? '').' '.($validated['rooms'][0]['travelers'][0]['last_name'] ?? '')
        );

        try {
            $booking = Booking::create([
                'reference' => 'TYT'.strtoupper(Str::random(8)),
                'user_id' => $request->user()->id,
                'guest_email' => $validated['contact_email'],
                // Local digits only + the country code separately — Book
                // sends them as contacts/code, and a number typed with its
                // code ("+91 98…") must not become "9198…" + "+91".
                'guest_phone' => $this->localPhoneDigits($validated['contact_phone'], $dialCode),
                'guest_phone_code' => $dialCode,
                'vertical' => 'hotel',
                'hotel_id' => $hotel->id,
                'room_name' => $roomName,
                'meal_basis' => $option['mealBasis'] ?? null,
                'tripjack_gst_type' => $gstType,
                'tripjack_gst_info' => $gstInfo,
                // tripjack_booking_id stays null until Book is actually called,
                // post-payment. tripjack_hold_id is Review's bookingId — the
                // identifier Book() itself needs, kept regardless of payment.
                // Unique-indexed at the DB level: it's one-per-Review, so it
                // doubles as an idempotency key against a double form-submit
                // racing this same request before the session draft is cleared.
                'tripjack_hold_id' => $draft['bookingId'],
                'tripjack_option_id' => $option['optionId'] ?? null,
                'tripjack_hold_expires_at' => $option['deadlineDateTime'] ?? null,
                // The exact Book-API payload, persisted so the post-payment Book
                // call — which may run from a Razorpay webhook with no session —
                // can reconstruct it from the DB alone.
                'tripjack_room_traveller_payload' => $roomTravellerInfo,
                'check_in' => $draft['check_in'],
                'check_out' => $draft['check_out'],
                'pax_adults' => $draft['adults'],
                'pax_children' => $draft['children'],
                'lead_guest_name' => $leadGuestName,
                'special_requests' => $validated['special_requests'] ?? null,
                'base_amount' => $basePrice,
                // Mirrors the customer-facing "Taxes & Fees" line on the review
                // page: everything between TripJack's base price and our final
                // customer price, so base_amount + tax_amount = total_amount.
                'tax_amount' => $customerPrice - $basePrice,
                'total_amount' => $customerPrice,
                'tripjack_total_price' => $breakdown['tripjack_total_price'] ?? ($pricing['totalPrice'] ?? null),
                'gst_slab' => $breakdown['gst_slab'] ?? null,
                'margin_amount' => $breakdown['margin_amount'] ?? null,
                'gst_on_margin' => $breakdown['gst_on_margin'] ?? null,
                'razorpay_recovery' => $breakdown['razorpay_recovery'] ?? null,
                'tripjack_mf' => $breakdown['tripjack_mf'] ?? null,
                'tripjack_mft' => $breakdown['tripjack_mft'] ?? null,
                'currency' => $pricing['currency'] ?? 'INR',
                'status' => 'pending_payment', // awaiting Razorpay payment — Book hasn't been called yet
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique constraint on tripjack_hold_id — a racing double-submit
            // already created this exact booking a moment ago. Send the guest
            // to its payment page rather than creating a second Booking +
            // Razorpay order for the same room hold.
            if ((int) $e->getCode() === 23000) {
                $existing = Booking::where('tripjack_hold_id', $draft['bookingId'])->first();
                if ($existing) {
                    session()->forget('tripjack_booking_draft');

                    return redirect()->route('hotel.payment.show', $existing->reference);
                }
            }

            throw $e;
        }

        foreach ($roomSlots as $ri => $slot) {
            $adultCount = $slot['adults'];
            $totalCount = $adultCount + ($slot['children'] ?? 0);
            for ($ti = 0; $ti < $totalCount; $ti++) {
                $t = $validated['rooms'][$ri]['travelers'][$ti];
                $booking->travelers()->create([
                    'title' => $t['title'],
                    'full_name' => trim($t['first_name'].' '.$t['last_name']),
                    'traveler_type' => $ti < $adultCount ? 'adult' : 'child',
                    'passport_number' => $t['passport_number'] ?? null,
                    'pan_number' => $panRequired ? $validated['pan_number'] : null,
                    'pan_name' => $panRequired ? $validated['pan_name'] : null,
                ]);

                // "Add this guest to my guest list" — save (or refresh) this
                // traveler as a UserTraveller so it shows up in the "Fill
                // From Saved Traveller" picker on a future booking.
                if (! empty($t['save_to_list'])) {
                    $request->user()->savedTravellers()->updateOrCreate(
                        ['first_name' => $t['first_name'], 'last_name' => $t['last_name']],
                        ['passport_number' => $t['passport_number'] ?? null]
                    );
                }
            }
        }

        $this->syncProfileFromBooking($request->user(), $validated, $panRequired);

        // Everything needed to complete the booking now lives on the Booking
        // row itself — the review draft has served its purpose.
        session()->forget('tripjack_booking_draft');

        $order = $razorpay->createOrder((float) $booking->total_amount, $booking->reference);
        Payment::create([
            'booking_id' => $booking->id,
            'razorpay_order_id' => $order['id'],
            'amount' => $booking->total_amount,
            'currency' => $booking->currency ?? 'INR',
            'status' => 'created',
        ]);

        return redirect()->route('hotel.payment.show', $booking->reference);
    }

    /**
     * TripJack's own docs: poll bookingDetails every 5s for up to 180s until
     * a terminal status is reached. A synchronous request can't literally
     * block that long, so each page load takes one live reading and the view
     * meta-refreshes every 5s (tracked via ?polling_since=) until terminal or
     * the 180s window elapses.
     */
    protected function isTerminalTripjackStatus(?string $status): bool
    {
        return in_array($status, ['SUCCESS', 'ON_HOLD', 'ABORTED', 'FAILED', 'CANCELLED'], true);
    }

    /**
     * Logs at the severity TripJackErrorCatalog assigned the error code, so
     * operationally-critical failures (wallet balance, suspended key) stand
     * out from routine ones (sold out, expired session) instead of all
     * flattening into the same "warning" bucket.
     */
    protected function logTripjackFailure(string $logLevel, string $event, array $context): void
    {
        $logger = Log::channel('tripjack');
        match ($logLevel) {
            'critical' => $logger->critical($event, $context),
            'error' => $logger->error($event, $context),
            'info' => $logger->info($event, $context),
            default => $logger->warning($event, $context),
        };
    }

    /**
     * Only reached for statuses discovered *after* the payment-captured
     * refund guard in confirmBookingAfterPayment()/bookingConfirmation()
     * already had a chance to intercept ABORTED/FAILED — so in normal
     * operation this only ever needs to express the "good" outcomes.
     */
    protected function mapTripjackBookingStatus(?string $tripjackStatus): string
    {
        return match ($tripjackStatus) {
            'CANCELLED' => 'cancelled',
            'ABORTED', 'FAILED' => 'failed_needs_review',
            'SUCCESS' => 'confirmed',
            // PENDING/IN_PROGRESS/ON_HOLD, or unreadable: paid and accepted
            // but not yet confirmed by the hotel — PollHotelBookingStatusJob
            // keeps checking until it settles.
            default => 'pending_confirmation',
        };
    }

    /**
     * A Book failure whose outcome we can't know: the request timed out, or
     * TripJack answered with a 5xx (gateway/server error mid-request). A
     * business rejection (4xx, success:false) is a definite "not booked".
     */
    protected function isUnknownBookOutcome(TripJackException $e): bool
    {
        return $e instanceof \App\Services\TripJack\Exceptions\TripJackTimeoutException
            || ($e instanceof TripJackApiException && $e->status >= 500);
    }

    /** How long an unknown-outcome Book is given to appear at TripJack before we refund. */
    protected const UNKNOWN_BOOK_GRACE_MINUTES = 30;

    /**
     * Finished bookings never change on TripJack's side again, so no live
     * Booking Details read is needed (a pending cancellation still needs
     * one, and a confirmed booking until its hotel confirmation number lands).
     */
    public function isFinishedHotelBooking(Booking $booking): bool
    {
        return in_array($booking->status, ['refunded', 'failed_needs_review', 'cancelled', 'payment_failed'], true)
            || ($booking->status === 'confirmed' && $booking->cancellation_requested_at === null && $booking->hotel_confirmation_number);
    }

    /**
     * One live Booking Details read for a hotel booking, applying whatever
     * it shows (late failure → refund, ON_HOLD → confirm-book, offline
     * cancellation → finalize, PENDING → SUCCESS → confirmed). Shared by the
     * confirmation page and the tripjack:refresh-hotel-bookings command.
     * Returns TripJack's raw order status, or null if it couldn't be read.
     */
    public function refreshHotelBookingStatus(Booking $booking, TripJackClient $client, RazorpayService $razorpay): ?string
    {
        if (! $booking->tripjack_booking_id) {
            return null;
        }

        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('booking_details_failed', ['bookingId' => $booking->tripjack_booking_id, 'message' => $e->getMessage()]);

            // A Book whose outcome was unknown, and TripJack still answers
            // "no such booking" (a 4xx, not an outage) well after it was
            // sent: it never went through, so refund the guest.
            if ($e instanceof TripJackApiException && $e->status < 500
                && $booking->status === 'pending_confirmation'
                && $booking->updated_at->lt(now()->subMinutes(self::UNKNOWN_BOOK_GRACE_MINUTES))) {
                $payment = $booking->payments()->where('status', 'captured')->latest()->first();
                if ($payment) {
                    $this->refundAndMarkFailed($booking, $payment, $razorpay, 'The hotel booking could not be completed, so your payment has been refunded in full.');
                    $booking->refresh();
                }
            }

            return null;
        }

        $liveStatus = $details['order']['status'] ?? null;
        $this->storeHotelConfirmationNumber($booking, $details);
        if ($liveStatus === 'SUCCESS' && ! $booking->hotel_confirmation_number) {
            // Not yet seen in any real response (sandbox never sends one);
            // log so the first production booking shows where it lives.
            Log::channel('tripjack')->info('hotel_confirmation_number_missing', ['bookingId' => $booking->tripjack_booking_id, 'top_keys' => array_keys($details), 'op_keys' => array_keys($details['itemInfos']['HOTEL']['hInfo']['ops'][0] ?? [])]);
        }

        if (in_array($liveStatus, ['ABORTED', 'FAILED'], true) && in_array($booking->status, ['confirmed', 'pending_confirmation'], true)) {
            // The guest was already charged — a late-discovered failure
            // needs the same refund as one caught in confirmBookingAfterPayment().
            $payment = $booking->payments()->where('status', 'captured')->latest()->first();
            if ($payment) {
                $this->refundAndMarkFailed($booking, $payment, $razorpay, "TripJack reported this booking as {$liveStatus} during status polling.");
                $booking->refresh();
            }
        } elseif ($liveStatus === 'ON_HOLD' && $booking->tripjack_confirm_attempted_at === null) {
            // Resolve via confirm-book; the attempted_at guard means this
            // only ever fires once.
            $payment = $booking->payments()->where('status', 'captured')->latest()->first();
            if ($payment) {
                $this->resolveOnHoldBooking($booking, $payment, $client, $razorpay);
                $booking->refresh();
            }
        } elseif ($liveStatus === 'CANCELLED' && $booking->status !== 'cancelled') {
            // A cancellation (ours, still pending, or the hotel's own)
            // that has since resolved offline.
            $this->finalizeCancellation($booking, $details, $razorpay);
            $booking->refresh();
        } elseif (in_array($liveStatus, ['SUCCESS', 'PENDING', 'IN_PROGRESS'], true)
            && in_array($booking->status, ['confirmed', 'pending_confirmation'], true)) {
            // Only map statuses we understand — an unreadable or
            // CANCELLATION_PENDING status must not downgrade a confirmed booking.
            $mapped = $this->mapTripjackBookingStatus($liveStatus);
            if ($mapped !== $booking->status && ! ($booking->status === 'confirmed' && $mapped === 'pending_confirmation')) {
                $booking->update(['status' => $mapped]);
            }
        }

        return $liveStatus;
    }

    public function bookingConfirmation($reference, Request $request, TripJackClient $client, RazorpayService $razorpay)
    {
        $verticalCheck = Booking::where('reference', $reference)->value('vertical');
        if ($verticalCheck === 'flight') {
            $booking = Booking::where('reference', $reference)->firstOrFail();
            if ($booking->user_id !== auth()->id()) {
                abort(403);
            }

            $flights = app(\App\Services\FlightBookingService::class);
            $pollingSince = (int) $request->query('polling_since', now()->timestamp);
            $result = $flights->refreshLiveStatus($booking, $pollingSince, $razorpay);

            return view('pages.flight-booking-confirmation', [
                'booking' => $booking,
                'liveStatus' => $result['liveStatus'],
                'stillPolling' => $result['stillPolling'],
                'pollingSince' => $pollingSince,
            ]);
        }

        $booking = Booking::with(['hotel.images', 'hotel.amenities', 'roomType', 'travelers'])->where('reference', $reference)->firstOrFail();

        // A booking reference alone must never be enough to view someone
        // else's booking — this route sits behind 'auth', but auth alone is
        // only authentication, not authorization.
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $isFinished = $this->isFinishedHotelBooking($booking);
        $liveStatus = $isFinished ? null : $this->refreshHotelBookingStatus($booking, $client, $razorpay);

        $pollingSince = (int) $request->query('polling_since', now()->timestamp);
        // payment_failed/refunded/failed_needs_review/cancelled will never
        // get a $liveStatus (no tripjack_booking_id, or already resolved) —
        // without this guard the page would meta-refresh pointlessly for
        // the full 180s window instead of settling immediately. A pending
        // cancellation is explicitly NOT near-real-time (TripJack: poll
        // Booking Details once per day), so it never gets the 5s meta-refresh
        // either — a fresh page load is enough to check in on it.
        $stillPolling = ! $isFinished
            && ! in_array($booking->status, ['payment_failed', 'refunded', 'failed_needs_review', 'cancelled'], true)
            && $booking->cancellation_requested_at === null
            && ! $this->isTerminalTripjackStatus($liveStatus)
            && (now()->timestamp - $pollingSince) < 180;

        return view('pages.booking-confirmation', compact('booking', 'liveStatus', 'stillPolling', 'pollingSince'));
    }

    /**
     * Streams a PDF invoice for a booking. Gated to bookings that actually
     * had a payment captured at some point (pending_payment/payment_failed
     * never did) — an invoice for money that was never taken doesn't mean
     * anything.
     */
    public function downloadInvoice($reference)
    {
        $booking = Booking::with(['hotel.destination', 'roomType', 'travelers'])->where('reference', $reference)->firstOrFail();

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $booking->hasInvoice()) {
            abort(404);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("invoice-{$booking->reference}.pdf");
    }

    /**
     * GET counterpart to submitBooking()'s redirect — renders the Razorpay
     * Checkout page for a booking awaiting payment. Reuses an existing
     * uncaptured order if the guest is retrying (e.g. dismissed the modal),
     * rather than minting a fresh one every reload.
     */
    public function showPayment($reference, RazorpayService $razorpay)
    {
        // Flight bookings share this route/method rather than duplicating
        // the Razorpay order lookup-or-create logic below — see
        // FlightBookingService's docblock for why. Everything past this
        // guard is unreached for a flight booking and stays exactly as it
        // was for hotels.
        $verticalCheck = Booking::where('reference', $reference)->value('vertical');
        if ($verticalCheck === 'flight') {
            $booking = Booking::where('reference', $reference)->firstOrFail();
            if ($booking->user_id !== auth()->id()) {
                abort(403);
            }
            if (! in_array($booking->status, ['pending_payment', 'payment_failed'], true)) {
                return redirect()->route('hotel.booking.confirmation', $booking->reference);
            }

            // The reviewed fare's TripJack hold (Review conditions.st) runs
            // out mid-payment too. Past it, Book is certain to fail after the
            // guest has paid — so stop here instead of charging and then
            // refunding (a refund takes days to reach the guest). 30 s of
            // margin: a payment started that late can't finish in time.
            $fareExpiresAt = $booking->flight_segments_payload['fareExpiresAt'] ?? null;
            $resultsUrl = $booking->flight_segments_payload['resultsUrl'] ?? route('flights.search');
            if ($fareExpiresAt && now()->timestamp >= (int) $fareExpiresAt - 30) {
                return view('pages.flight-payment', [
                    'booking' => $booking,
                    'payment' => null,
                    'razorpayKeyId' => null,
                    'fareExpired' => true,
                    'fareExpiresAt' => null,
                    'resultsUrl' => $resultsUrl,
                ]);
            }

            $payment = $booking->payments()->where('status', 'created')->latest()->first();
            if (! $payment) {
                $order = $razorpay->createOrder((float) $booking->total_amount, $booking->reference);
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'razorpay_order_id' => $order['id'],
                    'amount' => $booking->total_amount,
                    'currency' => $booking->currency ?? 'INR',
                    'status' => 'created',
                ]);
            }

            return view('pages.flight-payment', [
                'booking' => $booking,
                'payment' => $payment,
                'razorpayKeyId' => config('services.razorpay.key_id'),
                'fareExpired' => false,
                'fareExpiresAt' => $fareExpiresAt,
                'resultsUrl' => $resultsUrl,
            ]);
        }

        $booking = Booking::with('hotel')->where('reference', $reference)->firstOrFail();

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (! in_array($booking->status, ['pending_payment', 'payment_failed'], true)) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }

        // The rate was re-confirmed with TripJack when this booking was
        // created (refreshHotelReview). Past the hold, Book would very likely
        // fail after the guest pays — so stop taking payment instead of
        // charging and refunding (30 s margin: a payment started that late
        // can't finish in time). Never later than TripJack's own deadline.
        $rateExpiresAt = $booking->created_at->copy()->addSeconds(self::HOTEL_SESSION_SECONDS);
        if ($booking->tripjack_hold_expires_at && $booking->tripjack_hold_expires_at->lt($rateExpiresAt)) {
            $rateExpiresAt = $booking->tripjack_hold_expires_at;
        }
        if (now()->gte($rateExpiresAt->copy()->subSeconds(30))) {
            return view('pages.hotel-payment', [
                'booking' => $booking,
                'payment' => null,
                'razorpayKeyId' => null,
                'rateExpired' => true,
                'rateExpiresAt' => null,
            ]);
        }

        $payment = $booking->payments()->where('status', 'created')->latest()->first();

        if (! $payment) {
            $order = $razorpay->createOrder((float) $booking->total_amount, $booking->reference);
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'razorpay_order_id' => $order['id'],
                'amount' => $booking->total_amount,
                'currency' => $booking->currency ?? 'INR',
                'status' => 'created',
            ]);
        }

        return view('pages.hotel-payment', [
            'booking' => $booking,
            'payment' => $payment,
            'razorpayKeyId' => config('services.razorpay.key_id'),
            'rateExpired' => false,
            'rateExpiresAt' => $rateExpiresAt->timestamp,
        ]);
    }

    /**
     * Hit by Checkout.js's client-side success handler after the guest pays.
     * Fast-path confirmation — the webhook (razorpayWebhook()) is the
     * authoritative one, since this never fires if the guest closes the
     * browser right after paying.
     */
    public function razorpayCallback(Request $request, TripJackClient $client, RazorpayService $razorpay)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $payment = Payment::where('razorpay_order_id', $request->input('razorpay_order_id'))->firstOrFail();
        $booking = $payment->booking;

        // Defense in depth: a valid signature already proves this is a real
        // Razorpay payment, but it doesn't prove the caller is the guest who
        // made it — nothing stops another logged-in user from replaying a
        // captured order_id/payment_id/signature trio they happened to see.
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $razorpay->verifyPaymentSignature($request->only(['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature']))) {
            Log::channel('tripjack')->warning('razorpay_signature_invalid', ['booking_id' => $booking->id, 'order_id' => $request->input('razorpay_order_id')]);

            return redirect()->route('hotel.payment.show', $booking->reference)->with('booking_error', 'We could not verify your payment. Please try again.');
        }

        $payment->update([
            'razorpay_payment_id' => $request->input('razorpay_payment_id'),
            'razorpay_signature' => $request->input('razorpay_signature'),
        ]);

        $this->confirmBookingAfterPayment($payment, $client, $razorpay);

        if ($payment->purpose === 'flight_ssr') {
            return redirect()->route('flights.extras.show', $booking->reference);
        }

        return redirect()->route('hotel.booking.confirmation', $booking->reference);
    }

    /**
     * Razorpay's server-to-server webhook — the authoritative confirmation
     * path. Verified via the webhook secret (separate from the per-payment
     * signature the client-side callback uses) against the raw request body.
     */
    public function razorpayWebhook(Request $request, TripJackClient $client, RazorpayService $razorpay)
    {
        $body = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        if ($signature === '' || ! $razorpay->verifyWebhookSignature($body, $signature)) {
            Log::channel('tripjack')->warning('razorpay_webhook_signature_invalid');

            return response()->json(['status' => 'invalid signature'], 400);
        }

        $payload = json_decode($body, true) ?? [];
        $event = $payload['event'] ?? null;
        $entity = $payload['payload']['payment']['entity'] ?? null;
        $orderId = $entity['order_id'] ?? null;

        if (! $entity || ! $orderId || ! in_array($event, ['payment.captured', 'payment.failed'], true)) {
            return response()->json(['status' => 'ignored']);
        }

        $payment = Payment::where('razorpay_order_id', $orderId)->first();
        if (! $payment) {
            return response()->json(['status' => 'unknown order']);
        }

        if ($event === 'payment.captured') {
            $payment->update([
                'razorpay_payment_id' => $entity['id'] ?? $payment->razorpay_payment_id,
                'raw_response' => $entity,
            ]);
            $this->confirmBookingAfterPayment($payment, $client, $razorpay);
        } elseif ($payment->status === 'created') {
            $payment->update(['status' => 'failed', 'raw_response' => $entity]);
            $payment->booking->update(['status' => 'payment_failed']);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Marks the payment captured and calls TripJack's Book API — with
     * paymentInfos this time, for a real/instant booking, not a HOLD. Row
     * locks + status guards make this safe to call twice for the same
     * payment (the client callback and the webhook can both fire).
     */
    protected function confirmBookingAfterPayment(Payment $payment, TripJackClient $client, RazorpayService $razorpay): void
    {
        // A flight-extras (seat/meal/baggage) payment on an ALREADY
        // confirmed booking must never reach FlightBookingService below —
        // that path calls TripJack's Book API, which must only ever run
        // once per booking. This purpose check is the only thing that
        // routes it correctly; every other payment keeps purpose='booking'
        // (the column default), so this branch is unreachable for them.
        if ($payment->purpose === 'flight_ssr') {
            app(\App\Services\FlightAncillaryService::class)->confirmAfterPayment($payment, $razorpay);

            return;
        }

        if ($payment->purpose === 'flight_confirm_book') {
            app(\App\Services\FlightBookingService::class)->confirmHoldAfterPayment($payment, $razorpay);

            return;
        }

        if ($payment->purpose === 'flight_reissue') {
            app(\App\Services\FlightReissueService::class)->confirmAfterPayment($payment, $razorpay);

            return;
        }

        // Flight bookings never hold (Phase 1 is Instant Book only), so this
        // is a single delegated call, not a parallel copy of the
        // transaction/locking logic below — see FlightBookingService.
        if ($payment->booking?->vertical === 'flight') {
            app(\App\Services\FlightBookingService::class)->confirmAfterPayment($payment, $razorpay);

            return;
        }

        $needsHoldResolution = DB::transaction(function () use ($payment, $client, $razorpay) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->first();

            if (in_array($payment->status, ['captured', 'refunded', 'partially_refunded'], true)
                || in_array($booking->status, ['confirmed', 'refunded', 'failed_needs_review'], true)) {
                return null; // already processed by a racing callback/webhook
            }

            $payment->update(['status' => 'captured']);

            // The guest's own country code (was hard-coded +91), and the
            // number without it — also cleans older rows saved as typed.
            $guestCode = $booking->guest_phone_code ?: '91';
            $dialCode = '+'.$guestCode;
            $phoneDigits = $this->localPhoneDigits((string) $booking->guest_phone, $guestCode);

            try {
                $response = $client->book(
                    $booking->tripjack_hold_id,
                    $booking->tripjack_room_traveller_payload ?? [],
                    [$booking->guest_email],
                    [$phoneDigits],
                    [$dialCode],
                    amount: (float) $booking->tripjack_total_price, // TripJack's raw price, not the marked-up customer price
                    gstInfo: $booking->tripjack_gst_info,
                );
            } catch (TripJackException $e) {
                // Outcome unknown (timeout, or TripJack's side erroring
                // mid-request): the room may well be booked and paid from
                // the wallet. Don't refund — Book uses the Review bookingId,
                // so track it under that id and let Booking Details settle
                // it (refreshHotelBookingStatus / the background command).
                if ($this->isUnknownBookOutcome($e)) {
                    Log::channel('tripjack')->critical('book_outcome_unknown', ['booking_id' => $booking->id, 'bookingId' => $booking->tripjack_hold_id, 'message' => $e->getMessage()]);
                    $booking->update([
                        'tripjack_booking_id' => $booking->tripjack_hold_id,
                        'status' => 'pending_confirmation',
                    ]);

                    return null;
                }

                $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
                $described = TripJackErrorCatalog::describe($errorCode);
                $this->logTripjackFailure($described['logLevel'], 'book_after_payment_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
                $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

                return null;
            }

            if (! ($response['status']['success'] ?? false)) {
                $errorCode = TripJackErrorCatalog::codeFromResponse($response);
                $described = TripJackErrorCatalog::describe($errorCode, 'The hotel declined this booking after payment.');
                $this->logTripjackFailure($described['logLevel'], 'book_after_payment_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
                $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

                return null;
            }

            // Book's own response only confirms the request was *received* —
            // take one immediate reading now so a booking that's already
            // ABORTED/FAILED doesn't get shown to the guest as confirmed;
            // bookingConfirmation()'s own polling catches anything later.
            $tripjackStatus = null;
            try {
                $details = $client->bookingDetails($response['bookingId']);
                $tripjackStatus = $details['order']['status'] ?? null;
            } catch (TripJackException $e) {
                Log::channel('tripjack')->warning('booking_details_failed', ['bookingId' => $response['bookingId'], 'message' => $e->getMessage()]);
            }

            $booking->update(['tripjack_booking_id' => $response['bookingId']]);
            if (isset($details)) {
                $this->storeHotelConfirmationNumber($booking, $details);
            }

            if (in_array($tripjackStatus, ['ABORTED', 'FAILED'], true)) {
                $this->refundAndMarkFailed($booking, $payment, $razorpay, "TripJack reported this booking as {$tripjackStatus} immediately after confirmation.");

                return null;
            }

            // ON_HOLD means TripJack only reserved the option despite us
            // requesting Instant Booking — it still needs a separate
            // confirm-book call before the deadline. Resolve that outside
            // this transaction (it does its own locking) rather than hold
            // this one open across another network call.
            if ($tripjackStatus === 'ON_HOLD') {
                return $booking->id;
            }

            $booking->update(['status' => $this->mapTripjackBookingStatus($tripjackStatus)]);

            return null;
        });

        if ($needsHoldResolution) {
            $booking = Booking::findOrFail($needsHoldResolution);
            $payment = $booking->payments()->where('status', 'captured')->latest()->first();
            if ($payment) {
                $this->resolveOnHoldBooking($booking, $payment, $client, $razorpay);
            }
        }
    }

    /**
     * ON_HOLD is not actually confirmed — TripJack requires a separate
     * confirm-book call (with paymentInfos again) before the option's
     * deadline, or it auto-cancels. Since payment was already captured,
     * failing to confirm would mean charging the guest for a room that
     * silently expires, so this is attempted immediately rather than left
     * for a human to notice. Safe to call from any context (confirmBookingAfterPayment
     * or bookingConfirmation()'s polling) — the attempted_at guard, claimed
     * before the HTTP call, ensures confirm-book is never called twice for
     * the same booking (which could double-deduct the TripJack wallet).
     */
    protected function resolveOnHoldBooking(Booking $booking, Payment $payment, TripJackClient $client, RazorpayService $razorpay): void
    {
        $claimed = DB::transaction(function () use ($booking) {
            $fresh = Booking::whereKey($booking->id)->lockForUpdate()->first();
            if ($fresh->tripjack_confirm_attempted_at !== null) {
                return false;
            }
            $fresh->update(['tripjack_confirm_attempted_at' => now()]);

            return true;
        });

        if (! $claimed) {
            return;
        }

        try {
            $response = $client->confirmBook($booking->tripjack_booking_id, (float) $booking->tripjack_total_price);
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackErrorCatalog::describe($errorCode);
            $this->logTripjackFailure($described['logLevel'], 'confirm_book_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
            $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

            return;
        }

        if (! ($response['status']['success'] ?? false)) {
            $errorCode = TripJackErrorCatalog::codeFromResponse($response);
            $described = TripJackErrorCatalog::describe($errorCode, 'The hotel could not confirm this hold booking.');
            $this->logTripjackFailure($described['logLevel'], 'confirm_book_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
            $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

            return;
        }

        // confirm-book succeeded — like Book itself, this only confirms the
        // request was received, so re-read the live status rather than
        // assuming it's now terminal.
        $tripjackStatus = null;
        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
            $tripjackStatus = $details['order']['status'] ?? null;
            $this->storeHotelConfirmationNumber($booking, $details);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('booking_details_failed', ['bookingId' => $booking->tripjack_booking_id, 'message' => $e->getMessage()]);
        }

        if (in_array($tripjackStatus, ['ABORTED', 'FAILED'], true)) {
            $this->refundAndMarkFailed($booking, $payment, $razorpay, "TripJack reported this booking as {$tripjackStatus} after confirm-book.");

            return;
        }

        $booking->update(['status' => $this->mapTripjackBookingStatus($tripjackStatus)]);
    }

    /**
     * The guest has already paid but TripJack's Book call failed (or later
     * reported ABORTED/FAILED) — refund automatically rather than leaving
     * them charged with no room, since nobody is watching this in real time.
     * If the refund call itself errors, that's escalated to failed_needs_review
     * (a human must intervene) rather than silently swallowed.
     */
    protected function refundAndMarkFailed(Booking $booking, Payment $payment, RazorpayService $razorpay, string $reason): void
    {
        try {
            $razorpay->refund($payment->razorpay_payment_id, (float) $payment->amount);
            $payment->update([
                'status' => 'refunded',
                'refund_amount' => $payment->amount,
                'refund_reason' => $reason,
            ]);
            $booking->update(['status' => 'refunded']);
            Log::channel('tripjack')->critical('booking_refunded_after_payment', [
                'booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $reason,
            ]);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed', 'refund_reason' => $reason]);
            $booking->update(['status' => 'failed_needs_review']);
            Log::channel('tripjack')->critical('refund_after_booking_failure_errored', [
                'booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $reason, 'refund_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Saves TripJack's hotelConfirmationNumber (the hotel's own reference,
     * needed at check-in) the first time a Booking Details read carries it.
     * Searched for anywhere in the response since its nesting varies.
     */
    protected function storeHotelConfirmationNumber(Booking $booking, array $bookingDetails): void
    {
        if ($booking->hotel_confirmation_number) {
            return;
        }

        $found = null;
        array_walk_recursive($bookingDetails, function ($value, $key) use (&$found) {
            if ($found === null && $key === 'hotelConfirmationNumber' && is_scalar($value) && trim((string) $value) !== '') {
                $found = trim((string) $value);
            }
        });

        if ($found !== null) {
            $booking->update(['hotel_confirmation_number' => mb_substr($found, 0, 100)]);
        }
    }

    /**
     * Reads the cancellation-penalty slab that applies right now from a
     * bookingDetails() response's option-level cnp object (same structure
     * Review/Detail expose, embedded here under itemInfos.HOTEL.hInfo.ops[0]).
     * Returns null when no slab matches "now" or the shape is missing —
     * treated as "unknown" by callers, never assumed to mean free cancellation.
     *
     * @return array{amount: float, isRefundable: bool}|null
     */
    protected function currentCancellationPenalty(array $bookingDetails, ?\DateTimeInterface $at = null): ?array
    {
        return app(\App\Services\Booking\BookingCancellationService::class)->penaltyFor($bookingDetails, $at);
    }

    /**
     * GET /booking/{reference}/cancel — shows the guest what cancelling
     * right now would cost before they confirm. Fetches bookingDetails()
     * live rather than reusing anything cached, since the penalty schedule
     * is time-based and only accurate as of "right now".
     */
    public function showCancellation($reference, TripJackClient $client)
    {
        $verticalCheck = Booking::where('reference', $reference)->value('vertical');
        if ($verticalCheck === 'flight') {
            $booking = Booking::where('reference', $reference)->firstOrFail();
            if ($booking->user_id !== auth()->id()) {
                abort(403);
            }
            if ($booking->cancellation_requested_at !== null || $booking->status !== 'confirmed' || ! $booking->tripjack_booking_id) {
                return redirect()->route('hotel.booking.confirmation', $booking->reference);
            }
            if (! \App\Support\FlightSettings::allowGuestCancel()) {
                return redirect()->route('hotel.booking.confirmation', $booking->reference)->with('booking_error', \App\Support\FlightSettings::contactUsMessage());
            }

            // No pre-cancel charge quote for a normal cancellation (optional
            // per TripJack's docs) — the guest sees the actual charge/refund
            // only after submitting, via bookingConfirmation()'s eventual
            // cancellation_reason note once the amendment resolves. Auto
            // Void IS probed here though — it's a bonus, better-than-normal
            // option that only sometimes applies (see checkVoidEligibility's
            // docblock), so the guest needs to see it's available upfront.
            $flights = app(\App\Services\FlightBookingService::class);
            $voidEligibility = $flights->checkVoidEligibility($booking);
            $travellers = $booking->flight_segments_payload['travellerInfo'] ?? [];
            $legs = $booking->flight_legs ?? [];

            return view('pages.flight-booking-cancel', compact('booking', 'voidEligibility', 'travellers', 'legs'));
        }

        $booking = Booking::with('hotel')->where('reference', $reference)->firstOrFail();

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->cancellation_requested_at !== null || $booking->status !== 'confirmed' || ! $booking->tripjack_booking_id) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }
        if (! \App\Support\HotelSettings::allowGuestCancel()) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference)->with('booking_error', \App\Support\HotelSettings::contactUsMessage());
        }

        $penalty = null;
        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
            $penalty = $this->currentCancellationPenalty($details);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('cancellation_policy_lookup_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);
        }

        // The penalty TripJack quotes is in their raw price terms — scale it
        // by the same ratio against what the guest actually paid us, so the
        // estimate shown here is in the guest's own currency of "what I paid".
        $estimatedRefund = null;
        if ($penalty !== null && (float) $booking->tripjack_total_price > 0) {
            $penaltyRatio = min(1, $penalty['amount'] / (float) $booking->tripjack_total_price);
            $estimatedRefund = round((float) $booking->total_amount * (1 - $penaltyRatio), 2);
        }

        return view('pages.booking-cancel', compact('booking', 'penalty', 'estimatedRefund'));
    }

    /**
     * POST /booking/{reference}/cancel — actually requests the cancellation.
     * Claims cancellation_requested_at before calling TripJack (same
     * claim-then-call idempotency pattern as resolveOnHoldBooking) so a
     * double form-submit can't fire two cancel requests for one booking.
     */
    public function submitCancellation($reference, Request $request, TripJackClient $client, RazorpayService $razorpay)
    {
        $verticalCheck = Booking::where('reference', $reference)->value('vertical');
        if ($verticalCheck === 'flight') {
            $booking = Booking::where('reference', $reference)->firstOrFail();
            if ($booking->user_id !== auth()->id()) {
                abort(403);
            }
            if ($booking->status !== 'confirmed' || ! $booking->tripjack_booking_id) {
                return redirect()->route('hotel.booking.confirmation', $booking->reference);
            }
            if (! \App\Support\FlightSettings::allowGuestCancel()) {
                return redirect()->route('hotel.booking.confirmation', $booking->reference)->with('booking_error', \App\Support\FlightSettings::contactUsMessage());
            }

            $flights = app(\App\Services\FlightBookingService::class);
            $scope = (string) $request->input('cancel_scope', 'full');
            $trips = [];
            $type = 'CANCELLATION';

            if ($scope === 'void') {
                // Re-verify eligibility server-side rather than trusting the
                // page the guest saw — it may be stale (e.g. window closed
                // between page load and submit).
                if (! $flights->checkVoidEligibility($booking)) {
                    return redirect()->route('hotel.booking.cancel.show', $booking->reference)
                        ->with('booking_error', 'Same-day free cancellation is no longer available for this booking. Please use the normal cancellation option instead.');
                }
                $type = 'VOIDED';
            } elseif (in_array($scope, ['travellers', 'trip'], true)) {
                $resolved = $flights->cancellationScope(
                    $booking,
                    $scope,
                    (array) $request->input('leg_indexes', []),
                    (array) $request->input('traveller_indexes', []),
                );

                if (! $resolved) {
                    return redirect()->route('hotel.booking.cancel.show', $booking->reference)
                        ->with('booking_error', $scope === 'travellers' ? 'Please select at least one traveller to cancel.' : 'Please select at least one flight to cancel.');
                }
                $trips = $resolved['trips'];
            }

            $result = $flights->submitCancellation($booking, $trips, $type);
            if (! $result['success']) {
                return redirect()->route('hotel.booking.cancel.show', $booking->reference)
                    ->with('booking_error', $result['message'] ?? 'This request could not be processed.');
            }

            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }

        $booking = Booking::where('reference', $reference)->firstOrFail();

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'confirmed' || ! $booking->tripjack_booking_id) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }
        if (! \App\Support\HotelSettings::allowGuestCancel()) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference)->with('booking_error', \App\Support\HotelSettings::contactUsMessage());
        }

        $claimed = DB::transaction(function () use ($booking) {
            $fresh = Booking::whereKey($booking->id)->lockForUpdate()->first();
            if ($fresh->cancellation_requested_at !== null) {
                return false;
            }
            $fresh->update(['cancellation_requested_at' => now()]);

            return true;
        });

        if (! $claimed) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }

        // The claim above wrote cancellation_requested_at via a separate
        // $fresh instance inside the transaction — $booking's in-memory copy
        // is still stale (cached as null). Without this refresh, releasing
        // the claim below via $booking->update(['cancellation_requested_at'
        // => null]) would be a no-op: Eloquent's dirty-checking sees
        // null-to-null and skips writing it, even though the DB row itself
        // holds a real timestamp.
        $booking->refresh();

        try {
            $response = $client->cancelBooking($booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackErrorCatalog::describe($errorCode, 'We couldn\'t process your cancellation right now. Please try again.');
            $this->logTripjackFailure($described['logLevel'], 'cancel_booking_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
            // Release the claim — the request never reached TripJack (or
            // TripJack rejected it outright), so the guest should be able to
            // retry rather than being stuck showing "pending" forever.
            $booking->update(['cancellation_requested_at' => null]);

            return redirect()->route('hotel.booking.cancel.show', $booking->reference)->with('booking_error', $described['message']);
        }

        if (! ($response['status']['success'] ?? false)) {
            $errorCode = TripJackErrorCatalog::codeFromResponse($response);
            $described = TripJackErrorCatalog::describe($errorCode, 'This booking could not be cancelled.');
            $this->logTripjackFailure($described['logLevel'], 'cancel_booking_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
            $booking->update(['cancellation_requested_at' => null]);

            return redirect()->route('hotel.booking.cancel.show', $booking->reference)->with('booking_error', $described['message']);
        }

        // Acknowledgement only — the real outcome (CANCELLED now, or
        // CANCELLATION_PENDING for TripJack Ops to process offline, per
        // their docs) comes from a bookingDetails() read, same as Book's
        // own "request received" response.
        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
            $this->finalizeCancellation($booking, $details, $razorpay);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('booking_details_failed', ['bookingId' => $booking->tripjack_booking_id, 'message' => $e->getMessage()]);
        }

        return redirect()->route('hotel.booking.confirmation', $booking->reference);
    }

    /**
     * Applies the outcome of a cancellation once bookingDetails() confirms
     * it: marks the booking cancelled and auto-refunds the guest's payment
     * scaled by the penalty's share of TripJack's price (in full when the
     * penalty is zero). An unreadable penalty is left for manual review.
     * Callable from both the immediate post-cancel-request check and the
     * routine bookingConfirmation() poll; guarded so it only ever acts once.
     */
    protected function finalizeCancellation(Booking $booking, array $bookingDetails, RazorpayService $razorpay): void
    {
        $liveStatus = $bookingDetails['order']['status'] ?? null;
        if ($liveStatus !== 'CANCELLED' || $booking->status === 'cancelled') {
            return;
        }

        // The penalty in force when the guest asked to cancel, not when
        // TripJack finished processing it (CANCELLATION_PENDING can take days).
        $penalty = $this->currentCancellationPenalty($bookingDetails, $booking->cancellation_requested_at ?? now());
        $payment = $booking->payments()->where('status', 'captured')->latest()->first();

        // Refund what the guest paid, minus the same share TripJack keeps:
        // the penalty's fraction of TripJack's price applied to our price.
        $refundAmount = null;
        if ($payment && $penalty !== null && (float) $booking->tripjack_total_price > 0) {
            $penaltyRatio = min(1, max(0, $penalty['amount'] / (float) $booking->tripjack_total_price));
            $refundAmount = round((float) $payment->amount * (1 - $penaltyRatio), 2);
        }

        if ($payment && $refundAmount !== null && $refundAmount > 0) {
            $isFull = $refundAmount >= (float) $payment->amount;
            try {
                $razorpay->refund($payment->razorpay_payment_id, $refundAmount);
                $payment->update([
                    'status' => $isFull ? 'refunded' : 'partially_refunded',
                    'refund_amount' => $refundAmount,
                    'refund_reason' => $isFull ? 'Free cancellation — full refund.' : 'Cancellation — refund after the hotel\'s cancellation penalty.',
                ]);
                $booking->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $isFull
                        ? 'Cancelled within the free-cancellation window. Refunded in full automatically.'
                        : sprintf('Cancelled with a cancellation penalty. Refunded %s %.2f automatically.', $booking->currency, $refundAmount),
                ]);
                Log::channel('tripjack')->info('booking_cancelled_and_refunded', ['booking_id' => $booking->id, 'amount' => $refundAmount, 'penalty' => $penalty]);

                return;
            } catch (\Throwable $e) {
                Log::channel('tripjack')->critical('cancellation_refund_failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
                $booking->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Cancelled — the automatic refund failed and needs manual processing: '.$e->getMessage(),
                ]);

                return;
            }
        }

        $note = $penalty === null
            ? 'Cancelled. Cancellation penalty could not be determined automatically — refund needs manual review.'
            : sprintf('Cancelled with a cancellation penalty of %s %.2f — no refund due.', $booking->currency, $penalty['amount']);

        $booking->update(['status' => 'cancelled', 'cancellation_reason' => $note]);
        Log::channel('tripjack')->warning('booking_cancelled_needs_manual_refund', ['booking_id' => $booking->id, 'penalty' => $penalty]);
    }

    public function cruises()
    {
        // ── Page-level text settings ───────────────────────────────────────
        $s = function (string $key, mixed $default = '') {
            return Setting::get($key, $default);
        };
        $j = function (string $key, mixed $default = []) {
            return Setting::getJson($key, $default);
        };

        // Hero
        $heroEyebrow  = $s('cruise_page.hero_eyebrow',  "Cordelia Cruises · India's Premium Cruise Line");
        $heroTitle    = $s('cruise_page.hero_title',    'Destination of <br><em>Your Dreams</em>');
        $heroSubtitle = $s('cruise_page.hero_subtitle', 'Mumbai &bull; Goa &bull; Kochi &bull; Lakshadweep &bull; Chennai &bull; Sri Lanka');
        $heroCtaText  = $s('cruise_page.hero_cta_text', 'Enquire Now');

        // Ship stats
        $shipStats = $j('cruise_page.ship_stats', [
            ['value' => 'All-Inclusive', 'label' => 'Dining & Entertainment'],
            ['value' => '48,563 GT',     'label' => 'Gross Tonnage'],
            ['value' => '6 Ports',       'label' => 'Mumbai to Sri Lanka'],
            ['value' => '24/7',          'label' => 'Onboard Support'],
        ]);

        // Destinations
        $destinationsLabel   = $s('cruise_page.destinations_label',   'Where We Sail');
        $destinationsHeading = $s('cruise_page.destinations_heading', 'Six Stunning Destinations');
        $destinationCards    = $j('cruise_page.destination_cards', []);

        // Resolve destination card images (uploaded file takes priority)
        $destinationCards = array_map(function ($card) {
            $card['resolved_image'] = (!empty($card['image_path']) && Storage::disk('public')->exists($card['image_path']))
                ? Storage::disk('public')->url($card['image_path'])
                : ($card['image_url'] ?? null);
            return $card;
        }, $destinationCards);

        // Experience Tabs
        $diningIntro        = $s('cruise_page.dining_intro');
        $diningItems        = $j('cruise_page.dining_items', []);
        $entertainmentIntro = $s('cruise_page.entertainment_intro');
        $entertainmentItems = $j('cruise_page.entertainment_items', []);
        $barsIntro          = $s('cruise_page.bars_intro');
        $barsItems          = $j('cruise_page.bars_items', []);
        $indulgenceIntro    = $s('cruise_page.indulgence_intro');
        $indulgenceItems    = $j('cruise_page.indulgence_items', []);
        $eventsItems        = $j('cruise_page.events_items', []);

        // Resolve dining item images
        $diningItems = array_map(function ($item) {
            $item['resolved_image'] = (!empty($item['image_path']) && Storage::disk('public')->exists($item['image_path']))
                ? Storage::disk('public')->url($item['image_path'])
                : ($item['image_url'] ?? null);
            return $item;
        }, $diningItems);

        // Trust strip
        $trustItems = $j('cruise_page.trust_items', []);

        // Booking form options
        $bookingPorts        = array_filter(array_map('trim', explode("\n", $s('cruise_page.booking_ports', "Mumbai\nChennai\nKochi"))));
        $bookingDestinations = array_filter(array_map('trim', explode("\n", $s('cruise_page.booking_destinations', "Goa\nLakshadweep\nSri Lanka"))));

        // ── Cruise record — cabin types from the featured active cruise ───
        $cruise    = Cruise::where('is_active', true)->with(['cabinTypes', 'images'])->first();
        $cabinTypes = $cruise ? $cruise->cabinTypes->map(function ($c) { $c->resolved_image = $c->resolved_image; return $c; }) : collect([]);

        // Hero carousel images from the cruise's images relation
        $heroImages = [];
        if ($cruise && $cruise->images->isNotEmpty()) {
            $heroImages = $cruise->images->sortBy('sort_order')->map(fn($img) => $img->resolved_image)->filter()->values()->toArray();
        }
        // Fallback to hardcoded Unsplash images if none set in DB
        if (empty($heroImages)) {
            $heroImages = [
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80',
                'https://images.unsplash.com/photo-1512100356356-de1b84283e18?auto=format&fit=crop&w=1800&q=80',
                'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1800&q=80',
                'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1800&q=80',
            ];
        }

        return view('pages.cruises', compact(
            'heroEyebrow', 'heroTitle', 'heroSubtitle', 'heroCtaText', 'heroImages',
            'shipStats',
            'destinationsLabel', 'destinationsHeading', 'destinationCards',
            'diningIntro', 'diningItems',
            'entertainmentIntro', 'entertainmentItems',
            'barsIntro', 'barsItems',
            'indulgenceIntro', 'indulgenceItems',
            'eventsItems',
            'trustItems',
            'bookingPorts', 'bookingDestinations',
            'cabinTypes'
        ));
    }

    public function packages()
    {
        $packages = \App\Models\Package::with(['destination', 'images', 'inclusions'])
            ->where('is_active', true)
            ->get();
        
        return view('pages.packages', compact('packages'));
    }

    public function packageDetails($slug)
    {
        $package = \App\Models\Package::with([
            'destination',
            'images',
            'inclusions',
            'exclusions',
            'itineraryDays',
            'highlights',
            'reviews' => fn ($q) => $q->where('is_published', true),
        ])->where('slug', $slug)->firstOrFail();

        return view('pages.package-details', compact('package'));
    }

    public function offers()
    {
        $s = fn (string $key, mixed $default = '') => Setting::get($key, $default);
        $j = fn (string $key, mixed $default = []) => Setting::getJson($key, $default);

        if ($s('offers_page.is_visible', '1') !== '1') {
            abort(404);
        }

        // ── Hero ──────────────────────────────────────────────────────────
        $heroEyebrow  = $s('offers_page.hero_eyebrow',  'Limited Time Deals');
        $heroTitle    = $s('offers_page.hero_title',    'Exclusive Deals. <em>Unforgettable</em> Experiences.');
        $heroSubtitle = $s('offers_page.hero_subtitle', 'Handpicked offers on flights, hotels, cruises & packages — updated regularly');

        $heroImages = array_values(array_filter(array_map(function ($img) {
            if (!empty($img['image_path']) && Storage::disk('public')->exists($img['image_path'])) {
                return Storage::disk('public')->url($img['image_path']);
            }
            return $img['image_url'] ?? null;
        }, $j('offers_page.hero_images', []))));

        if (empty($heroImages)) {
            $heroImages = [
                'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1400&q=85',
                'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1400&q=85',
                'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1400&q=85',
                'https://images.unsplash.com/photo-1548574505-5e239809ee19?w=1400&q=85',
            ];
        }

        // ── 4 Canonical Filter Tabs (always fixed for a travel agency) ───
        $filterTabs = [
            ['key' => 'all',      'label' => 'All Offers'],
            ['key' => 'flights',  'label' => 'Flights'],
            ['key' => 'hotels',   'label' => 'Hotels'],
            ['key' => 'cruises',  'label' => 'Cruises'],
            ['key' => 'packages', 'label' => 'Packages'],
        ];

        // ── Auto section headings per category ──────────────────────
        // Admin no longer needs to fill slider_label/slider_title — they
        // are derived automatically from the offer's category.
        $categoryMeta = [
            'flights'  => ['slider_label' => 'Flight Deals',     'slider_title' => 'Exclusive <em>Flight Offers</em>',  'order' => 1],
            'hotels'   => ['slider_label' => 'Hotel Deals',      'slider_title' => 'Luxury <em>Hotel Escapes</em>',      'order' => 2],
            'cruises'  => ['slider_label' => 'Cruise Deals',     'slider_title' => 'Sail in <em>Style</em>',             'order' => 3],
            'packages' => ['slider_label' => 'Holiday Packages', 'slider_title' => 'Handpicked <em>Journeys</em>',       'order' => 4],
        ];

        // ── Offer Cards from Database ───────────────────────────────
        $dbOffers = Offer::active()
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        $categories = $dbOffers
            ->groupBy('category_key')
            ->map(function ($offers, $catKey) use ($categoryMeta) {
                $meta = $categoryMeta[$catKey] ?? [
                    'slider_label' => ucfirst($catKey) . ' Deals',
                    'slider_title' => 'Special <em>' . ucfirst($catKey) . ' Offers</em>',
                    'order'        => 99,
                ];

                $cards = $offers->map(fn ($offer) => [
                    'name'           => $offer->title,
                    'destination'    => $offer->destination,
                    'duration'       => $offer->duration,
                    'subtitle'       => $offer->subtitle,
                    'description'    => $offer->description,
                    'terms'          => $offer->terms_and_conditions,
                    'price'          => $offer->display_price,
                    'enquire_link'   => $offer->enquire_link,
                    'badge_label'    => $offer->badge_label,
                    'badge_type'     => $offer->badge_type ?? 'badge-gold',
                    'coming_soon'    => (bool) $offer->coming_soon,
                    'resolved_image' => $offer->resolvedImage,
                    'promo_code'     => $offer->promo_code,
                    'discount_label' => $offer->discount_value ? $offer->discountLabel : null,
                    'valid_to'       => $offer->valid_to?->format('d M Y'),
                ])->values()->toArray();

                return [
                    'category_key' => $catKey,
                    'slider_label' => $meta['slider_label'],
                    'slider_title' => $meta['slider_title'],
                    'order'        => $meta['order'],
                    'cards'        => $cards,
                ];
            })
            ->sortBy('order')
            ->values()
            ->toArray();

        // ── Bottom CTA ────────────────────────────────────────────────
        $ctaTag        = $s('offers_page.cta_tag',         'Stay Ahead');
        $ctaHeading    = $s('offers_page.cta_heading',     'Be the First to <em>Know</em>');
        $ctaBody       = $s('offers_page.cta_body',        "Drop your WhatsApp number and we'll notify you the moment a new deal goes live — no spam, ever.");
        $ctaNotifyNote = $s('offers_page.cta_notify_note', "WhatsApp only. We won't call unless you ask.");
        $ctaWhatsapp   = $s('offers_page.cta_whatsapp',    'https://wa.me/9875073788');
        $ctaWaLabel    = $s('offers_page.cta_wa_label',    'Ask for Latest Deals on WhatsApp');

        return view('pages.offers', compact(
            'heroEyebrow', 'heroTitle', 'heroSubtitle', 'heroImages',
            'filterTabs', 'categories',
            'ctaTag', 'ctaHeading', 'ctaBody', 'ctaNotifyNote', 'ctaWhatsapp', 'ctaWaLabel'
        ));
    }

    public function blog(\Illuminate\Http\Request $request)
    {
        $categories = \App\Models\BlogCategory::where('is_active', true)->orderBy('sort_order')->get();
        $trendingPosts = \App\Models\BlogPost::with('category')->where('is_active', true)->where('is_trending', true)->orderBy('sort_order')->get();

        $activeCategory = $request->query('category');

        $postsQuery = \App\Models\BlogPost::with('category')->where('is_active', true)->orderBy('sort_order');
        if ($activeCategory) {
            $postsQuery->whereHas('category', fn ($q) => $q->where('slug', $activeCategory));
        }
        $posts = $postsQuery->paginate(9)->withQueryString();

        $destinations = \App\Models\FeaturedBlogDestination::orderBy('sort_order')->take(4)->get();

        return view('pages.blog', compact('categories', 'trendingPosts', 'posts', 'destinations', 'activeCategory'));
    }

    public function blogDetails($slug)
    {
        $post = \App\Models\BlogPost::with('category')->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $relatedPosts = \App\Models\BlogPost::with('category')
            ->where('is_active', true)
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        return view('pages.blog-details', compact('post', 'relatedPosts'));
    }
    public function downloadItinerary($slug)
    {
        $package = \App\Models\Package::with([
            'destination', 'images', 'inclusions', 'exclusions', 'itineraryDays', 'departures'
        ])->where('slug', $slug)->firstOrFail();

        try {
            $filename = ($package->slug ?? 'package') . '-itinerary.pdf';

            $pdfPath = $this->ensurePdfPathForPackage($package);

            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Itinerary PDF generation failed: ' . $e->getMessage());
            return back()->with('error', 'Sorry, the itinerary PDF could not be generated right now. Please try again shortly.');
        }
    }

    /**
     * Render the itinerary PDF using headless Chrome (Browsershot/Puppeteer),
     * as a single continuous page with the footer flush at the bottom.
     *
     * Two-pass: first render off-screen to measure the real laid-out content
     * height (Chrome does real layout, so this is exact — no DomPDF-style
     * approximation needed), then render again at that exact page height.
     */
    private function renderItineraryPdf(string $view, array $data): string
    {
        $html = view($view, $data)->render();

        // Body is authored at a fixed 794px width (A4 width @ 96dpi).
        $widthPx = 794;
        $pxPerMm = 96 / 25.4;
        $widthMm = round($widthPx / $pxPerMm, 2);

        // Per-render profile dir: a shared one gets locked by concurrent renders and
        // becomes unwritable for www-data once a root-run artisan command creates it.
        $userDataDir = rtrim(config('browsershot.user_data_dir', '/tmp/chrome-userdata'), '/')
            . '-' . \Illuminate\Support\Str::random(12);

        $newBrowsershot = function () use ($html, $userDataDir) {
            $b = \Spatie\Browsershot\Browsershot::html($html);

            // Use config() — NOT env() — so values work even when config is cached
            // (php artisan optimize caches config; direct env() calls return null in production)
            if ($nodeBinary = config('browsershot.node_binary')) {
                $b->setNodeBinary($nodeBinary);
            }
            if ($chromePath = config('browsershot.chrome_path')) {
                $b->setChromePath($chromePath);
            }

            return $b
                ->timeout(120)
                ->setOption('protocolTimeout', 90000)
                ->noSandbox()
                ->showBackground()
                ->newHeadless()
                ->addChromiumArguments([
                    'headless'              => 'new',  // --headless=new
                    'disable-gpu',                      // --disable-gpu
                    'disable-extensions',               // --disable-extensions
                    'disable-dev-shm-usage',            // prevents /dev/shm crashes on VPS
                    'disable-crash-reporter',           // stops crashpad trying to write files
                    'no-first-run',                     // skips first-run setup dialogs
                    'no-zygote',                        // needed in some containerised envs
                    'user-data-dir' => $userDataDir,
                ]);
        };

        try {
            // ── Pass 1: measure the exact bottom of the .footer element ──────────
            // Using getBoundingClientRect().bottom instead of scrollHeight ensures
            // the PDF is trimmed precisely at the footer's last pixel — no trailing
            // whitespace, no matter how long or short the content is.
            $heightPx = (int) $newBrowsershot()
                ->windowSize($widthPx, 200)
                ->evaluate('Math.ceil(document.querySelector(".footer").getBoundingClientRect().bottom)');

            $heightMm = round(max($heightPx, 200) / $pxPerMm, 2);

            // ── Pass 2: render the final PDF at the exact height ─────────────────
            return $newBrowsershot()
                ->windowSize($widthPx, $heightPx)
                ->paperSize($widthMm, $heightMm, 'mm')
                ->margins(0, 0, 0, 0)
                ->pdf();
        } finally {
            \Illuminate\Support\Facades\File::deleteDirectory($userDataDir);
        }
    }

    public function guestDownloadItinerary(Request $request, $slug)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255'
        ]);

        $package = \App\Models\Package::with([
            'destination', 'images', 'inclusions', 'exclusions', 'itineraryDays', 'departures'
        ])->where('slug', $slug)->firstOrFail();

        \App\Models\ItineraryDownload::create([
            'package_id' => $package->id,
            'name'       => $request->name,
            'phone'      => $request->phone,
            'email'      => $request->email,
        ]);

        try {
            $filename = ($package->slug ?? 'package') . '-itinerary.pdf';

            $pdfPath = $this->ensurePdfPathForPackage($package);

            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Itinerary PDF generation failed: ' . $e->getMessage());

            // AJAX request (fetch from the modal) — return HTTP 500 so JS catch() handles it
            if ($request->expectsJson() || $request->ajax()) {
                return response('PDF generation failed. Please try again shortly.', 500);
            }

            return back()->with('error', 'Sorry, the itinerary PDF could not be generated right now. Please try again shortly.');
        }
    }

    /**
     * Ensure the package PDF exists on disk and return its full absolute path.
     * Caching makes downloads instantaneous (<50ms) directly from disk.
     */
    public function ensurePdfPathForPackage(\App\Models\Package $package, bool $forceRegenerate = false): string
    {
        $cacheDir = storage_path('app/itineraries');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $cacheFile = $cacheDir . '/' . $package->id . '.pdf';
        $packageTime = $package->updated_at ? $package->updated_at->timestamp : 0;

        // If file exists, is not empty, and was generated after the package's last update
        if (!$forceRegenerate && file_exists($cacheFile) && filesize($cacheFile) > 0 && filemtime($cacheFile) >= $packageTime) {
            return $cacheFile;
        }

        $pdf = $this->renderItineraryPdf('pdf.sample-itinerary', compact('package'));
        file_put_contents($cacheFile, $pdf);

        return $cacheFile;
    }

    /**
     * Retrieve the cached itinerary PDF binary data.
     */
    public function getPdfForPackage(\App\Models\Package $package, bool $forceRegenerate = false): string
    {
        $path = $this->ensurePdfPathForPackage($package, $forceRegenerate);
        return file_get_contents($path);
    }

    public function storeReview(Request $request, $slug)
    {
        $package = \App\Models\Package::where('slug', $slug)->firstOrFail();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'body'   => 'required|string|max:1000',
            'rating_guide' => 'nullable|integer|min:1|max:5',
            'rating_accommodation' => 'nullable|integer|min:1|max:5',
            'rating_value' => 'nullable|integer|min:1|max:5',
            'rating_itinerary' => 'nullable|integer|min:1|max:5',
            'images.*' => 'nullable|image|max:2048', // up to 2MB per image
        ]);

        $hasBooked = \App\Models\Booking::where('user_id', auth()->id())
            ->where('vertical', 'package')
            ->where('package_id', $package->id)
            ->where('status', 'confirmed')
            ->exists();

        if (!$hasBooked) {
            return back()->with('error', 'You can only review packages you have booked and completed.');
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('reviews', 'public');
            }
        }

        \App\Models\Review::create([
            'user_id'      => auth()->id(),
            'vertical'     => 'package',
            'reference_id' => $package->id,
            'author_name'  => auth()->user()->name,
            'title'        => $request->title,
            'rating'       => $request->rating,
            'rating_guide' => $request->rating_guide,
            'rating_accommodation' => $request->rating_accommodation,
            'rating_value' => $request->rating_value,
            'rating_itinerary' => $request->rating_itinerary,
            'images'       => $imagePaths,
            'body'         => $request->body,
            'is_published' => true,
        ]);

        return back()->with('success', 'Your review has been submitted successfully.');
    }

    public function storeEnquiry(Request $request)
    {
        $request->validate([
            'vertical'     => 'required|string',
            'reference_id' => 'required|integer',
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'email'        => 'nullable|email|max:255',
            'checkin'      => 'nullable|string',
            'checkout'     => 'nullable|string',
            'guest_data'   => 'nullable|string',
            'message'      => 'nullable|string|max:1000',
            'rooms_summary'=> 'nullable|string|max:1000',
        ]);

        $travelDateFrom = null;
        $travelDateTo = null;
        
        // Basic parsing for dates if they are in 'Y-m-d' format or we can just leave it if they are text
        if (!empty($request->checkin) && strtotime($request->checkin)) {
            $travelDateFrom = date('Y-m-d', strtotime($request->checkin));
        }
        if (!empty($request->checkout) && strtotime($request->checkout)) {
            $travelDateTo = date('Y-m-d', strtotime($request->checkout));
        }

        // Parse pax_adults and pax_children from guest_data JSON if possible
        $paxAdults = 2;
        $paxChildren = 0;
        $guestNotes = [];
        if (!empty($request->guest_data)) {
            $guestData = json_decode($request->guest_data, true);
            if (is_array($guestData)) {
                $paxAdults = array_sum(array_column($guestData, 'adults'));
                $paxChildren = array_reduce($guestData, function($carry, $room) {
                    return $carry + count($room['children'] ?? []);
                }, 0);
            }
        }

        // Build notes field — lead with the per-room breakdown so admins see
        // the same detail the WhatsApp message shows, even when the guest
        // leaves "Additional Requirements" blank.
        $notesParts = [];
        if (!empty($request->rooms_summary)) {
            $notesParts[] = 'Rooms: ' . trim($request->rooms_summary);
        }
        if (!empty($request->message)) {
            $notesParts[] = trim($request->message);
        }
        $notesStr = !empty($notesParts) ? implode("\n", $notesParts) : null;
        if ($notesStr && strlen($notesStr) > 500) $notesStr = substr($notesStr, 0, 497) . '...';

        \App\Models\Enquiry::create([
            'user_id'          => auth()->id(),
            'vertical'         => $request->vertical,
            'reference_id'     => $request->reference_id,
            'name'             => $request->name,
            'phone'            => $request->phone,
            'email'            => $request->email,
            'travel_date_from' => $travelDateFrom,
            'travel_date_to'   => $travelDateTo,
            'pax_adults'       => $paxAdults,
            'pax_children'     => $paxChildren,
            'notes'            => $notesStr,
            'source'           => 'web',
            'status'           => 'new'
        ]);

        return response()->json(['success' => true]);
    }
}
