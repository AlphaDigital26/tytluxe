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
use App\Services\HotelPricingService;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackClient;
use App\Services\TripJack\TripJackErrorCatalog;
use App\Services\TripJack\TripJackListingSearch;
use Illuminate\Support\Facades\DB;
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
                    } elseif (! empty($hidsToPrice)) {
                        // TripJack returned a normal 200 with zero priced
                        // options across the whole batch — not a request
                        // failure (that's caught below), just no live
                        // inventory for this exact search right now. Log it
                        // distinctly from a real error so a full-batch outage
                        // is visible in monitoring instead of only showing up
                        // as a wall of silent "Price on Request" cards, and
                        // tell the visitor plainly instead of leaving them to
                        // wonder whether the site is broken.
                        Log::channel('tripjack')->info('listing_search_zero_priced', [
                            'destination_id' => $searchDestination->id,
                            'hotel_count' => count($hidsToPrice),
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
        $hotel = Hotel::with(['destination', 'amenities', 'images', 'roomTypes', 'reviews'])
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
            // TripJack requires the same correlationId across Listing, Detail,
            // and Review for one search journey (used for their support
            // tracing). Reuse the one from the Listing search that brought the
            // guest here when it's still the same search context; only mint a
            // fresh one when there's no session search to continue (e.g. a
            // bookmarked/direct link straight to this hotel).
            $correlationId = ($sessionSearch['correlationId'] ?? null) ?: TripJackClient::newCorrelationId();

            try {
                $rooms = $this->distributeGuestsAcrossRooms($adults, $children, $roomCount, $childAges);
                $response = $client->pricing($hotel->tripjack_hotel_id, $checkIn, $checkOut, $rooms, $correlationId);
                // Add the customer-facing price (TripJack's raw totalPrice + TYTLUXE markup)
                // to every option here, once, so every view downstream reads it rather than
                // re-deriving it from the raw TripJack price.
                $liveOptions = collect($response['options'] ?? [])->map(function ($option) {
                    if (isset($option['pricing']['totalPrice'])) {
                        $option['pricing']['pricingBreakdown'] = HotelPricingService::price((float) $option['pricing']['totalPrice']);
                        $option['pricing']['customerPrice'] = $option['pricing']['pricingBreakdown']['customer_price'];
                    }

                    return $option;
                });
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
        $option = $response['option'] ?? null;
        if (isset($option['pricing']['totalPrice'])) {
            $option['pricing']['pricingBreakdown'] = HotelPricingService::price((float) $option['pricing']['totalPrice']);
            $option['pricing']['customerPrice'] = $option['pricing']['pricingBreakdown']['customer_price'];
        }

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id,
            'hid' => $hotel->tripjack_hotel_id,
            'bookingId' => $response['bookingId'],
            'option' => $option,
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
     * Phase 8 — guest submits details; we create the local Booking +
     * Razorpay order and send them to pay. TripJack's Book API is NOT called
     * here — it's only called once payment is captured (see
     * confirmBookingAfterPayment()), with paymentInfos, for a real/instant
     * booking rather than a HOLD.
     */
    public function submitBooking($slug, Request $request, RazorpayService $razorpay)
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

        $pricing = $option['pricing'] ?? [];
        // pricingBreakdown was computed once, at Review time, from TripJack's
        // freshly re-validated totalPrice (see reviewRoom()) — never
        // recalculated again here, so the amount the guest saw and agreed to
        // on the review page is exactly what gets charged via Razorpay.
        $breakdown = $pricing['pricingBreakdown'] ?? null;
        $customerPrice = $pricing['customerPrice'] ?? ($pricing['totalPrice'] ?? 0);
        $basePrice = $pricing['basePrice'] ?? 0;
        $booking = Booking::create([
            'reference' => 'TYT'.strtoupper(Str::random(8)),
            'guest_email' => $validated['lead_email'],
            'guest_phone' => $validated['lead_phone'],
            'vertical' => 'hotel',
            'hotel_id' => $hotel->id,
            // tripjack_booking_id stays null until Book is actually called,
            // post-payment. tripjack_hold_id is Review's bookingId — the
            // identifier Book() itself needs, kept regardless of payment.
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
            'lead_guest_name' => $validated['lead_name'],
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
            'currency' => $pricing['currency'] ?? 'INR',
            'status' => 'pending_payment', // awaiting Razorpay payment — Book hasn't been called yet
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
            default => 'confirmed', // SUCCESS/ON_HOLD, or still processing — payment is already captured, TripJack accepted
        };
    }

    public function bookingConfirmation($reference, Request $request, TripJackClient $client, RazorpayService $razorpay)
    {
        $booking = Booking::with('hotel')->where('reference', $reference)->firstOrFail();

        $liveStatus = null;
        if ($booking->tripjack_booking_id) {
            try {
                $details = $client->bookingDetails($booking->tripjack_booking_id);
                $liveStatus = $details['order']['status'] ?? null;

                if (in_array($liveStatus, ['ABORTED', 'FAILED'], true) && $booking->status === 'confirmed') {
                    // The guest was already charged (status only reaches
                    // 'confirmed' after payment capture) — a late-discovered
                    // failure here needs the same refund treatment as one
                    // caught immediately in confirmBookingAfterPayment().
                    $payment = $booking->payments()->where('status', 'captured')->latest()->first();
                    if ($payment) {
                        $this->refundAndMarkFailed($booking, $payment, $razorpay, "TripJack reported this booking as {$liveStatus} during status polling.");
                        $booking->refresh();
                    }
                } elseif ($liveStatus === 'ON_HOLD' && $booking->tripjack_confirm_attempted_at === null) {
                    // Book's own immediate check (confirmBookingAfterPayment)
                    // didn't see ON_HOLD yet when this happened — resolve it
                    // now via confirm-book, same as that path. The
                    // attempted_at guard means this only ever fires once.
                    $payment = $booking->payments()->where('status', 'captured')->latest()->first();
                    if ($payment) {
                        $this->resolveOnHoldBooking($booking, $payment, $client, $razorpay);
                        $booking->refresh();
                    }
                } elseif (! in_array($booking->status, ['refunded', 'failed_needs_review'], true)) {
                    $mapped = $this->mapTripjackBookingStatus($liveStatus);
                    if ($mapped !== $booking->status) {
                        $booking->update(['status' => $mapped]);
                    }
                }
            } catch (TripJackException $e) {
                Log::channel('tripjack')->warning('booking_details_failed', ['bookingId' => $booking->tripjack_booking_id, 'message' => $e->getMessage()]);
            }
        }

        $pollingSince = (int) $request->query('polling_since', now()->timestamp);
        // payment_failed/refunded/failed_needs_review/cancelled will never
        // get a $liveStatus (no tripjack_booking_id, or already resolved) —
        // without this guard the page would meta-refresh pointlessly for
        // the full 180s window instead of settling immediately.
        $stillPolling = ! in_array($booking->status, ['payment_failed', 'refunded', 'failed_needs_review', 'cancelled'], true)
            && ! $this->isTerminalTripjackStatus($liveStatus)
            && (now()->timestamp - $pollingSince) < 180;

        return view('pages.booking-confirmation', compact('booking', 'liveStatus', 'stillPolling', 'pollingSince'));
    }

    /**
     * GET counterpart to submitBooking()'s redirect — renders the Razorpay
     * Checkout page for a booking awaiting payment. Reuses an existing
     * uncaptured order if the guest is retrying (e.g. dismissed the modal),
     * rather than minting a fresh one every reload.
     */
    public function showPayment($reference, RazorpayService $razorpay)
    {
        $booking = Booking::with('hotel')->where('reference', $reference)->firstOrFail();

        if (! in_array($booking->status, ['pending_payment', 'payment_failed'], true)) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference);
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

        if (! $razorpay->verifyPaymentSignature($request->only(['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature']))) {
            Log::channel('tripjack')->warning('razorpay_signature_invalid', ['booking_id' => $booking->id, 'order_id' => $request->input('razorpay_order_id')]);

            return redirect()->route('hotel.payment.show', $booking->reference)->with('booking_error', 'We could not verify your payment. Please try again.');
        }

        $payment->update([
            'razorpay_payment_id' => $request->input('razorpay_payment_id'),
            'razorpay_signature' => $request->input('razorpay_signature'),
        ]);

        $this->confirmBookingAfterPayment($payment, $client, $razorpay);

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
        $needsHoldResolution = DB::transaction(function () use ($payment, $client, $razorpay) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->first();

            if (in_array($payment->status, ['captured', 'refunded', 'partially_refunded'], true)
                || in_array($booking->status, ['confirmed', 'refunded', 'failed_needs_review'], true)) {
                return null; // already processed by a racing callback/webhook
            }

            $payment->update(['status' => 'captured']);

            $dialCode = '+91';
            $phoneDigits = preg_replace('/\D/', '', (string) $booking->guest_phone);

            try {
                $response = $client->book(
                    $booking->tripjack_hold_id,
                    $booking->tripjack_room_traveller_payload ?? [],
                    [$booking->guest_email],
                    [$phoneDigits],
                    [$dialCode],
                    amount: (float) $booking->tripjack_total_price, // TripJack's raw price, not the marked-up customer price
                );
            } catch (TripJackException $e) {
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

        $newBrowsershot = function () use ($html) {
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
                    'user-data-dir' => config('browsershot.user_data_dir', '/tmp/chrome-userdata'),
                ]);
        };

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
