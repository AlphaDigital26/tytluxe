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
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackClient;
use App\Services\TripJack\TripJackErrorCatalog;
use App\Services\TripJack\TripJackListingSearch;
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
        $checkIn = $request->query('check_in');
        $checkOut = $request->query('check_out');
        $destinationQuery = trim((string) $request->query('destination', ''));
        $adults = max(1, (int) $request->query('adults', 2));
        $children = max(0, (int) $request->query('children', 0));
        $roomCount = max(1, (int) $request->query('rooms', 1));
        $nationality = (string) $request->query('nationality', '106');
        $minRating = (int) $request->query('min_rating', 0);
        $childAges = $this->parseChildAges($request->query('child_ages', ''));

        $hasSearched = $request->has('destination') || $request->has('check_in') || $request->has('min_rating');
        $searchActive = $destinationQuery !== '' && $checkIn && $checkOut;
        $searchDestination = null;
        $liveOptions = collect();
        $searchError = null;

        if (!$hasSearched) {
            $hotels = collect();
        } else {
            $hotelsQuery = Hotel::with(['destination', 'amenities', 'images'])->where('is_active', true);

            if ($destinationQuery !== '') {
                $searchDestination = Destination::where('slug', Str::slug($destinationQuery))
                    ->orWhere('name', 'LIKE', "%{$destinationQuery}%")
                    ->first();

                if ($searchDestination) {
                    $hotelsQuery->where('destination_id', $searchDestination->id);
                } else {
                    $searchError = "We don't have hotels in \"{$destinationQuery}\" yet.";
                }
            }

            if ($minRating > 0) {
                $hotelsQuery->where('star_rating', '=', $minRating);
            }

            $hotels = $hotelsQuery->latest()->get();

            if ($searchActive && $searchDestination) {
                try {
                    $rooms = $this->distributeGuestsAcrossRooms($adults, $children, $roomCount, $childAges);
                    $hidsToPrice = $hotels->pluck('tripjack_hotel_id')->filter()->map(fn($id) => (int)$id)->values()->all();

                    $result = $listingSearch->searchByHids($hidsToPrice, $checkIn, $checkOut, $rooms, nationality: $nationality);
                    $liveOptions = $result['options'];

                    if ($liveOptions->isNotEmpty()) {
                        $hotels = $hotels->sortByDesc(function ($hotel) use ($liveOptions) {
                            return $liveOptions->has((string) $hotel->tripjack_hotel_id) ? 1 : 0;
                        })->values();
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
                    $searchError = 'Live pricing is temporarily unavailable for this search. Showing our curated listing instead — enquire for the latest rates.';
                }
            }
        }
        $nationalities = $this->tripjackNationalities($client);
        $destinations = Destination::orderBy('name')->pluck('name');

        return view('pages.hotels', compact(
            'hotels', 'liveOptions', 'searchActive', 'hasSearched', 'searchError',
            'destinationQuery', 'checkIn', 'checkOut', 'adults', 'children', 'roomCount', 'childAges',
            'nationality', 'nationalities', 'minRating', 'destinations'
        ));
    }

    /**
     * Fetch nationality list with a short-lived cache.
     *
     * The nationality dropdown is best-effort \u2014 if TripJack is unreachable we
     * fall back to India-only and cache *that* result for 1 hour so the API is
     * not hammered on every page load while it is down.
     */
    protected function tripjackNationalities(TripJackClient $client): array
    {
        // Use a separate key for the "failed" fallback so it expires faster (1 h)
        // while a successful list is kept for 24 h.
        try {
            return \Illuminate\Support\Facades\Cache::remember('tripjack_nationalities', now()->addDay(), function () use ($client) {
                $response = $client->nationalityInfo();

                return collect($response['nationalityInfos'] ?? [])
                    ->sortBy('countryName')
                    ->values()
                    ->all();
            });
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('nationality_info_failed', ['message' => $e->getMessage()]);

            // Cache the fallback for 1 hour \u2014 avoids hammering a down API on every request
            return \Illuminate\Support\Facades\Cache::remember('tripjack_nationalities_fallback', now()->addHour(), function () {
                return [['countryId' => '106', 'countryName' => 'India']];
            });
        } catch (\Throwable $e) {
            // Safety net for any other unexpected failure (connection errors, etc.)
            Log::channel('tripjack')->error('nationality_info_unexpected', ['message' => $e->getMessage()]);

            return [['countryId' => '106', 'countryName' => 'India']];
        }
    }

    /**
     * Parses the comma-separated "child_ages" query/form value into a flat
     * int[] of real ages (0-17), dropping anything unparsable rather than
     * silently defaulting it.
     */
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
                if (count($ages) < $roomChildren) {
                    // Ages weren't supplied (e.g. a stale link built before the
                    // age picker existed) — log it rather than silently booking
                    // wrong-rate rooms with a guessed age.
                    Log::channel('tripjack')->warning('child_ages_missing', [
                        'expected' => $roomChildren, 'provided' => count($ages),
                    ]);
                    $ages = array_pad($ages, $roomChildren, 10);
                }
                $room['childAge'] = array_map('intval', $ages);
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

    public function hotelDetails($slug, Request $request, TripJackClient $client)
    {
        $hotel = Hotel::with(['destination', 'amenities', 'images', 'roomTypes'])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $sessionSearch = session('tripjack_search');
        $checkIn = $request->query('check_in') ?? ($sessionSearch['check_in'] ?? null);
        $checkOut = $request->query('check_out') ?? ($sessionSearch['check_out'] ?? null);
        $adults = max(1, (int) $request->query('adults', $sessionSearch['adults'] ?? 2));
        $children = max(0, (int) $request->query('children', $sessionSearch['children'] ?? 0));
        $roomCount = max(1, (int) $request->query('rooms', $sessionSearch['rooms'] ?? 1));
        $childAges = $request->has('child_ages')
            ? $this->parseChildAges((string) $request->query('child_ages', ''))
            : ($sessionSearch['child_ages'] ?? []);

        $liveOptions = collect();
        $pricingError = null;
        $correlationId = null;

        if ($hotel->source === 'tripjack' && $hotel->tripjack_hotel_id && $checkIn && $checkOut) {
            $correlationId = TripJackClient::newCorrelationId();

            try {
                $rooms = $this->distributeGuestsAcrossRooms($adults, $children, $roomCount, $childAges);
                $response = $client->pricing($hotel->tripjack_hotel_id, $checkIn, $checkOut, $rooms, $correlationId);
                $liveOptions = collect($response['options'] ?? []);
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
                ]]);
            } catch (TripJackException $e) {
                Log::channel('tripjack')->warning('pricing_failed', ['hid' => $hotel->tripjack_hotel_id, 'message' => $e->getMessage()]);
                $pricingError = 'Live rates are temporarily unavailable for this hotel right now. Please try again in a moment.';
            }
        }

        return view('pages.hotel-details', compact(
            'hotel', 'liveOptions', 'pricingError', 'checkIn', 'checkOut', 'adults', 'children', 'roomCount'
        ));
    }

    /**
     * Phase 6 — Review: re-validates the selected option's price/availability
     * immediately before booking and hands back the bookingId Phase 7 needs.
     * Never book off stale listing/pricing data — this call is mandatory.
     */
    public function reviewRoom($slug, Request $request, TripJackClient $client)
    {
        $hotel = Hotel::where('is_active', true)->where('slug', $slug)->firstOrFail();

        if ($hotel->source !== 'tripjack' || ! $hotel->tripjack_hotel_id) {
            abort(404);
        }

        $request->validate(['option_id' => 'required|string']);
        $optionId = $request->input('option_id');

        $searchParams = $request->only(['check_in', 'check_out', 'adults', 'children', 'rooms']);
        $backToDetails = redirect()->route('hotel.details', array_merge(['slug' => $slug], array_filter($searchParams)));

        $pricingContext = session("tripjack_pricing.{$hotel->tripjack_hotel_id}");
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
            $described = TripJackErrorCatalog::describe($errorCode, $e->getMessage());
            $this->logTripjackFailure($described['logLevel'], 'review_failed', ['hid' => $hotel->tripjack_hotel_id, 'optionId' => $optionId, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);

            return $backToDetails->with('booking_error', $described['message']);
        }

        if (! ($response['status']['success'] ?? false) || empty($response['bookingId'])) {
            $errorCode = TripJackErrorCatalog::codeFromResponse($response);
            $described = TripJackErrorCatalog::describe($errorCode, 'This room is no longer available. Please pick another option.');
            $this->logTripjackFailure($described['logLevel'], 'review_unsuccessful', ['hid' => $hotel->tripjack_hotel_id, 'optionId' => $optionId, 'errorCode' => $errorCode, 'response' => $response]);

            return $backToDetails->with('booking_error', $described['message']);
        }

        // We only support HOLD bookings until Phase 8 (payment) exists. Some
        // options don't allow a hold at all — TripJack's Book API would reject
        // them with HOLD_NOT_ALLOWED (6537). Catch that here instead, before
        // the guest even fills in their details.
        $onholdAllowed = filter_var($response['onholdAllowed'] ?? true, FILTER_VALIDATE_BOOLEAN);
        if (! $onholdAllowed) {
            return $backToDetails->with('booking_error', TripJackErrorCatalog::describe('6537')['message']);
        }

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id,
            'hid' => $hotel->tripjack_hotel_id,
            'bookingId' => $response['bookingId'],
            'option' => $response['option'] ?? null,
            'onholdAllowed' => $onholdAllowed,
            'correlationId' => $pricingContext['correlationId'],
            'check_in' => $pricingContext['check_in'],
            'check_out' => $pricingContext['check_out'],
            'adults' => $pricingContext['adults'],
            'children' => $pricingContext['children'],
            'rooms' => $pricingContext['rooms'],
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
        $hotel = Hotel::where('is_active', true)->where('slug', $slug)->firstOrFail();
        $draft = session('tripjack_booking_draft');

        if (! $draft || $draft['hotel_id'] !== $hotel->id) {
            return redirect()->route('hotel.details', $slug)->with('booking_error', 'Your booking session has expired. Please select a room again.');
        }

        $roomSlots = $this->roomSlotsFromDraft($draft);
        $option = $draft['option'] ?? [];
        $panRequired = $option['compliance']['panRequired'] ?? $option['ipr'] ?? false;
        $passportRequired = $option['compliance']['passportRequired'] ?? $option['ipm'] ?? false;

        return view('pages.hotel-review', [
            'hotel' => $hotel,
            'option' => $option,
            'bookingId' => $draft['bookingId'],
            'roomSlots' => $roomSlots,
            'panRequired' => $panRequired,
            'passportRequired' => $passportRequired,
            'draft' => $draft,
        ]);
    }

    /**
     * Phase 7 — Book: submits guest details and commits a HOLD booking (no
     * payment yet — that's Phase 8). Confirms TripJack booking works
     * standalone before Razorpay is wired in.
     */
    public function submitBooking($slug, Request $request, TripJackClient $client)
    {
        $hotel = Hotel::where('is_active', true)->where('slug', $slug)->firstOrFail();
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
        $roomSlots = $this->roomSlotsFromDraft($draft);

        $rules = [
            'lead_name' => 'required|string|max:255',
            'lead_email' => 'required|email|max:255',
            'lead_phone' => 'required|string|max:20',
            'pan_number' => $panRequired ? 'required|string|max:20' : 'nullable|string|max:20',
            'rooms' => 'required|array',
        ];
        foreach ($roomSlots as $ri => $slot) {
            $count = $slot['adults'] + ($slot['children'] ?? 0);
            for ($ti = 0; $ti < $count; $ti++) {
                $rules["rooms.{$ri}.travelers.{$ti}.title"] = 'required|string|max:10';
                $rules["rooms.{$ri}.travelers.{$ti}.first_name"] = 'required|string|max:100';
                $rules["rooms.{$ri}.travelers.{$ti}.last_name"] = 'required|string|max:100';
                if ($passportRequired) {
                    $rules["rooms.{$ri}.travelers.{$ti}.passport_number"] = 'required|string|max:30';
                }
            }
        }
        $validated = $request->validate($rules);

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
                }
                if ($passportRequired) {
                    $entry['pNum'] = $t['passport_number'] ?? null;
                }
                $travellerInfo[] = $entry;
            }
            $roomTravellerInfo[] = ['travellerInfo' => $travellerInfo];
        }

        $dialCode = '+91';
        $phoneDigits = preg_replace('/\D/', '', $validated['lead_phone']);

        try {
            $response = $client->book(
                $draft['bookingId'],
                $roomTravellerInfo,
                [$validated['lead_email']],
                [$phoneDigits],
                [$dialCode],
                amount: null // HOLD booking — no payment until Phase 8
            );
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackErrorCatalog::describe($errorCode, $e->getMessage());
            $this->logTripjackFailure($described['logLevel'], 'book_failed', ['bookingId' => $draft['bookingId'], 'errorCode' => $errorCode, 'message' => $e->getMessage()]);

            return back()->withInput()->with('booking_error', $described['message']);
        }

        if (! ($response['status']['success'] ?? false)) {
            $errorCode = TripJackErrorCatalog::codeFromResponse($response);
            $described = TripJackErrorCatalog::describe($errorCode, 'The hotel declined this booking request. Please try a different room or dates.');
            $this->logTripjackFailure($described['logLevel'], 'book_unsuccessful', ['bookingId' => $draft['bookingId'], 'errorCode' => $errorCode, 'response' => $response]);

            return back()->withInput()->with('booking_error', $described['message']);
        }

        // Book's own response only confirms the request was *received* — per
        // TripJack, confirmation can take up to 180s and must be polled via
        // bookingDetails(). Take one immediate reading now so we don't store a
        // misleadingly generic status; the confirmation page keeps polling.
        $tripjackStatus = null;
        try {
            $details = $client->bookingDetails($response['bookingId']);
            $tripjackStatus = $details['order']['status'] ?? null;
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('booking_details_failed', ['bookingId' => $response['bookingId'], 'message' => $e->getMessage()]);
        }

        $pricing = $option['pricing'] ?? [];
        $booking = Booking::create([
            'reference' => 'TYT'.strtoupper(Str::random(8)),
            'guest_email' => $validated['lead_email'],
            'guest_phone' => $validated['lead_phone'],
            'vertical' => 'hotel',
            'hotel_id' => $hotel->id,
            'tripjack_booking_id' => $response['bookingId'],
            'tripjack_hold_id' => $draft['bookingId'],
            'tripjack_option_id' => $option['optionId'] ?? null,
            'tripjack_hold_expires_at' => $option['deadlineDateTime'] ?? null,
            'check_in' => $draft['check_in'],
            'check_out' => $draft['check_out'],
            'pax_adults' => $draft['adults'],
            'pax_children' => $draft['children'],
            'lead_guest_name' => $validated['lead_name'],
            'base_amount' => $pricing['basePrice'] ?? 0,
            'tax_amount' => ($pricing['taxes'] ?? 0) + ($pricing['mf'] ?? 0) + ($pricing['mft'] ?? 0),
            'total_amount' => $pricing['totalPrice'] ?? 0,
            'currency' => $pricing['currency'] ?? 'INR',
            'status' => $this->mapTripjackBookingStatus($tripjackStatus),
        ]);

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
                ]);
            }
        }

        session()->forget('tripjack_booking_draft');

        return redirect()->route('hotel.booking.confirmation', $booking->reference);
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

    protected function mapTripjackBookingStatus(?string $tripjackStatus): string
    {
        return match ($tripjackStatus) {
            'ABORTED', 'FAILED' => 'failed_needs_review',
            'CANCELLED' => 'cancelled',
            default => 'pending_payment', // SUCCESS/ON_HOLD both still need our payment step (Phase 8)
        };
    }

    public function bookingConfirmation($reference, Request $request, TripJackClient $client)
    {
        $booking = Booking::with('hotel')->where('reference', $reference)->firstOrFail();

        $liveStatus = null;
        if ($booking->tripjack_booking_id) {
            try {
                $details = $client->bookingDetails($booking->tripjack_booking_id);
                $liveStatus = $details['order']['status'] ?? null;

                $mapped = $this->mapTripjackBookingStatus($liveStatus);
                if ($mapped !== $booking->status) {
                    $booking->update(['status' => $mapped]);
                }
            } catch (TripJackException $e) {
                Log::channel('tripjack')->warning('booking_details_failed', ['bookingId' => $booking->tripjack_booking_id, 'message' => $e->getMessage()]);
            }
        }

        $pollingSince = (int) $request->query('polling_since', now()->timestamp);
        $stillPolling = ! $this->isTerminalTripjackStatus($liveStatus) && (now()->timestamp - $pollingSince) < 180;

        return view('pages.booking-confirmation', compact('booking', 'liveStatus', 'stillPolling', 'pollingSince'));
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

    public function blog()
    {
        $categories = \App\Models\BlogCategory::where('is_active', true)->orderBy('sort_order')->get();
        $trendingPosts = \App\Models\BlogPost::with('category')->where('is_active', true)->where('is_trending', true)->orderBy('sort_order')->get();
        $posts = \App\Models\BlogPost::with('category')->where('is_active', true)->orderBy('sort_order')->get();
        $destinations = \App\Models\FeaturedBlogDestination::orderBy('sort_order')->take(4)->get();

        return view('pages.blog', compact('categories', 'trendingPosts', 'posts', 'destinations'));
    }
    public function downloadItinerary($slug)
    {
        $package = \App\Models\Package::with([
            'destination', 'images', 'inclusions', 'exclusions', 'itineraryDays', 'departures'
        ])->where('slug', $slug)->firstOrFail();

        try {
            $filename = ($package->slug ?? 'package') . '-itinerary.pdf';

            // Use a custom tall paper [width, height] in points (1pt = 1/72 inch).
            // Dynamically calculate required paper height based on content
            $daysCount = $package->itineraryDays->count();
            $incCount = $package->inclusions->count();
            $excCount = $package->exclusions->count();
            $textLength = strlen(strip_tags($package->description ?? ''));
            
            $aboutHeight = max(80, ($textLength / 100) * 20); // roughly 20pt per 100 chars
            $baseHeight = 1050; // hero + meta + titles + pricing + contact + margins
            $daysHeight = $daysCount * 230; // brief row + detailed card
            $incExcHeight = ($incCount + $excCount) * 26; // bullet points
            
            $totalHeight = $baseHeight + $aboutHeight + $daysHeight + $incExcHeight;
            // Add a generous safety buffer so the footer fits perfectly without spilling to page 2
            $calculatedHeight = $totalHeight * 1.25;

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sample-itinerary', compact('package'))
                ->setPaper([0, 0, 595.28, $calculatedHeight]);

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            return back()->with('error', 'PDF Error: ' . $e->getMessage());
        }
    }

    public function guestDownloadItinerary(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255'
        ]);

        $package = \App\Models\Package::with([
            'destination', 'images', 'inclusions', 'exclusions', 'itineraryDays', 'departures'
        ])->where('slug', $slug)->firstOrFail();

        \App\Models\ItineraryDownload::create([
            'package_id'   => $package->id,
            'name'         => $request->name,
            'phone'        => $request->phone,
            'email'        => $request->email,
        ]);

        try {
            $filename = ($package->slug ?? 'package') . '-itinerary.pdf';

            // Dynamically calculate required paper height based on content
            $daysCount = $package->itineraryDays->count();
            $incCount = $package->inclusions->count();
            $excCount = $package->exclusions->count();
            $textLength = strlen(strip_tags($package->description ?? ''));
            
            $aboutHeight = max(80, ($textLength / 100) * 20);
            $baseHeight = 1050;
            $daysHeight = $daysCount * 230;
            $incExcHeight = ($incCount + $excCount) * 26;
            
            $totalHeight = $baseHeight + $aboutHeight + $daysHeight + $incExcHeight;
            // Add a generous safety buffer so the footer fits perfectly without spilling to page 2
            $calculatedHeight = $totalHeight * 1.25;

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sample-itinerary', compact('package'))
                ->setPaper([0, 0, 595.28, $calculatedHeight]);

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            return back()->with('error', 'PDF Error: ' . $e->getMessage());
        }
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

        // Build notes field
        $notesStr = null;
        if (!empty($request->message)) {
            $notesStr = trim($request->message);
            if (strlen($notesStr) > 500) $notesStr = substr($notesStr, 0, 497) . '...';
        }

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
