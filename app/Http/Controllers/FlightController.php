<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\FlightBookingService;
use App\Services\FlightPricingService;
use App\Support\FlightSettings;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackFlightClient;
use App\Services\TripJack\TripJackFlightErrorCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Flights API v2.0 integration — Phase 1 (Search → Review → Instant Book).
 * Kept separate from FrontendController (already 2000+ lines) rather than
 * adding to it; only the payment/confirmation/cancellation steps that must
 * share Razorpay webhook/idempotency plumbing stay in FrontendController,
 * behind a `$booking->vertical === 'flight'` guard that delegates to
 * FlightBookingService — see that class's docblock.
 *
 * Phase 1 scope only: Oneway and Return (domestic + international), Instant
 * Book, full-booking cancellation. No Multi-City, Seat Map, SSR add-ons,
 * Hold booking, or Reissue yet — see the flight integration plan.
 */
class FlightController extends Controller
{
    /**
     * GET /flights/search — renders the results page if search params are
     * present, otherwise just the empty results shell (the actual search
     * form lives on pages.flights). GET (not POST) so results are
     * bookmarkable/shareable and a page refresh never re-submits anything.
     */
    public function search(Request $request, TripJackFlightClient $client)
    {
        $tripType = in_array($request->query('trip_type'), ['return', 'multi'], true) ? $request->query('trip_type') : 'oneway';
        $from = strtoupper((string) $request->query('from', ''));
        $to = strtoupper((string) $request->query('to', ''));
        $departDate = (string) $request->query('depart_date', '');
        $returnDate = (string) $request->query('return_date', '');
        $adults = max(1, min(9, (int) $request->query('adults', 1)));
        // TripJack FAQ: adults + children together at most 9 (infants extra, one per adult).
        $children = max(0, min(9 - $adults, (int) $request->query('children', 0)));
        $infants = max(0, min($adults, (int) $request->query('infants', 0)));
        $cabinClass = in_array($request->query('cabin_class'), ['ECONOMY', 'PREMIUM_ECONOMY', 'BUSINESS', 'FIRST'], true)
            ? $request->query('cabin_class')
            : 'ECONOMY';
        $preferredAirline = strtoupper((string) $request->query('preferred_airline', ''));
        $fareType = in_array($request->query('fare_type'), ['REGULAR', 'STUDENT', 'SENIOR_CITIZEN'], true)
            ? $request->query('fare_type')
            : 'REGULAR';
        $directFlightOnly = (bool) $request->query('direct_flight');

        // Multi-City (2-6 legs, doc-confirmed) submits its own legs[] array
        // (see partials.flight-search-widget's submit handler) rather than
        // the single from/to/depart_date/return_date fields oneway/return
        // use — from/to/departDate above stay as leg-1 fallbacks for the
        // results page header only.
        $legs = [];
        if ($tripType === 'multi') {
            foreach ((array) $request->query('legs', []) as $leg) {
                $legFrom = strtoupper(trim((string) ($leg['from'] ?? '')));
                $legTo = strtoupper(trim((string) ($leg['to'] ?? '')));
                $legDate = (string) ($leg['date'] ?? '');
                if ($legFrom !== '' && $legTo !== '' && $legDate !== '') {
                    $legs[] = ['from' => $legFrom, 'to' => $legTo, 'date' => $legDate];
                }
            }
            $legs = array_slice($legs, 0, 6);
        }

        $searchParams = compact('tripType', 'from', 'to', 'departDate', 'returnDate', 'adults', 'children', 'infants', 'cabinClass', 'preferredAirline', 'fareType', 'directFlightOnly', 'legs');

        $hasValidInput = $tripType === 'multi'
            ? count($legs) >= 2
            : ($from !== '' && $to !== '' && $departDate !== '' && (! ($tripType === 'return') || $returnDate !== ''));

        if (! $hasValidInput) {
            return view('pages.flight-results', ['results' => null, 'searchError' => null, 'searchParams' => $searchParams]);
        }

        $paxInfo = array_filter([
            'ADULT' => $adults,
            'CHILD' => $children ?: null,
            'INFANT' => $infants ?: null,
        ], fn ($v) => $v !== null);

        if ($tripType === 'multi') {
            $routeInfos = array_map(fn ($leg) => [
                'fromCityOrAirport' => ['code' => $leg['from']],
                'toCityOrAirport' => ['code' => $leg['to']],
                'travelDate' => $leg['date'],
            ], $legs);
        } else {
            $routeInfos = [[
                'fromCityOrAirport' => ['code' => $from],
                'toCityOrAirport' => ['code' => $to],
                'travelDate' => $departDate,
            ]];
            if ($tripType === 'return') {
                $routeInfos[] = [
                    'fromCityOrAirport' => ['code' => $to],
                    'toCityOrAirport' => ['code' => $from],
                    'travelDate' => $returnDate,
                ];
            }
        }

        // Confirmed live: the correct search modifier is `pfts`, sent as an
        // ARRAY of strings — not `fareType` (an earlier, unverified guess),
        // and not a plain string as the doc's own field table states.
        // Sending pfts as a string 400s with "Expected BEGIN_ARRAY but was
        // STRING". Always sent (not just for non-REGULAR) since TripJack
        // itself always echoes a `pfts` array back in its own response.
        $searchModifiers = self::searchModifiers($fareType, $directFlightOnly);
        // TODO(future): TripJack accepts up to 10 preferred airlines
        // (preferredAirline[].code); the search box currently offers one.
        // The client already sends this as a list of {code} objects.
        $preferredAirlines = $preferredAirline !== '' ? [$preferredAirline] : [];

        $results = null;
        $searchError = null;

        try {
            $response = $client->search($paxInfo, $routeInfos, $cabinClass, $searchModifiers, $preferredAirlines);

            if (! ($response['status']['success'] ?? false)) {
                $errorCode = TripJackFlightErrorCatalog::codeFromResponse($response);
                $described = TripJackFlightErrorCatalog::describe($errorCode, 'We couldn\'t search flights right now. Please try again.');
                $searchError = $described['message'];
            } else {
                $results = $response['searchResult'] ?? null;

                // TripJack already filters on isDirectFlight; this also drops
                // anything with a technical stop, which the doc's "direct"
                // doesn't promise to exclude.
                if ($directFlightOnly && $results) {
                    $results = $this->filterDirectFlightsOnly($results);
                }

                // International Return / Multi-City come back as ONE "COMBO"
                // list of whole-journey fares (confirmed live: BLR⇄DXB gave
                // only a COMBO key), so Review takes a single priceId for them.
                $searchParams['combo'] = isset($results['tripInfos']['COMBO']);

                // Review needs the same correlation/search context later —
                // stash the trip type + route so review() can validate the
                // right number of priceIds was selected (1 for oneway, 2 for
                // return) without trusting the client-submitted trip_type.
                session(['flight_search_context' => $searchParams]);
            }
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode);
            Log::channel('tripjack')->{$described['logLevel'] === 'critical' ? 'critical' : 'warning'}('flight_search_failed', ['errorCode' => $errorCode, 'message' => $e->getMessage()]);
            $searchError = $described['message'];
        }

        return $this->withoutIndentation(view('pages.flight-results', compact('results', 'searchError', 'searchParams'))->render());
    }

    /**
     * The results page repeats a deeply-indented card for every flight, and
     * that indentation alone was ~30% of the page (845 KB of 2.6 MB on a
     * 236-flight search). Leading whitespace carries no meaning here: the
     * page has no <pre>/<textarea>, and the only multi-line JS template
     * strings build HTML, where whitespace collapses anyway.
     */
    private function withoutIndentation(string $html)
    {
        return response(preg_replace('/\n[ \t]+/', "\n", $html));
    }

    /**
     * Keeps only itineraries whose first segment is non-stop. Confirmed
     * against a live sandbox response: tripInfos is a plain array of
     * itinerary objects per leg (each its own `sI` segment list + its own
     * `totalPriceList` of fares for that itinerary) — not a single object
     * holding one shared `sI`/`totalPriceList`, as earlier (unverified) code
     * here had assumed. Keyed ONWARD/RETURN for oneway/return, or 0..5 for
     * Multi-City (confirmed live) — iterating whatever keys are actually
     * present works for both without caring which.
     */
    protected function filterDirectFlightsOnly(array $results): array
    {
        foreach (array_keys($results['tripInfos'] ?? []) as $key) {
            if (! is_array($results['tripInfos'][$key])) {
                continue;
            }

            $results['tripInfos'][$key] = array_values(array_filter(
                $results['tripInfos'][$key],
                fn ($itinerary) => self::isNonStop($itinerary)
            ));
        }

        return $results;
    }

    /**
     * Search API searchModifiers. Doc: isDirectFlight (Boolean) "Return only
     * direct flights" — sent only when asked for, so a normal search is
     * unchanged and TripJack does the filtering for a direct-only one
     * (smaller, faster responses).
     */
    private static function searchModifiers(string $fareType, bool $directOnly): array
    {
        return ['pfts' => [$fareType]] + ($directOnly ? ['isDirectFlight' => true] : []);
    }

    /**
     * A connecting itinerary comes back as several `sI` segments, each with
     * `stops: 0` (confirmed live, BLR→DEL→IXB) — so the first segment's
     * `stops` alone doesn't mean the journey is non-stop.
     */
    private static function isNonStop(array $itinerary): bool
    {
        // Every leg must be a single segment with no technical stop — a
        // COMBO (international return) itinerary holds one leg per direction.
        foreach (TripJackFlightClient::itineraryLegs($itinerary['sI'] ?? []) as $leg) {
            if (count($leg) !== 1 || (int) ($leg[0]['stops'] ?? 0) !== 0) {
                return false;
            }
        }

        return ! empty($itinerary['sI']);
    }

    /**
     * GET /flights/fare-calendar (AJAX, JSON) — the "Fetch Fare" cells in the
     * results page's date strip. TripJack has no fare-calendar service, so
     * this runs an ordinary search for the other date and returns only the
     * cheapest total fare (onward + return for a return trip). Deliberately
     * doesn't touch session('flight_search_context'), which belongs to the
     * search the guest is actually looking at. Cached briefly, since guests
     * flick back and forth between the same few dates.
     */
    public function fareCalendarAjax(Request $request, TripJackFlightClient $client)
    {
        $validated = $request->validate([
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
            'depart_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'return_date' => 'nullable|date_format:Y-m-d|after_or_equal:depart_date',
            'adults' => 'required|integer|min:1|max:9',
            'children' => 'nullable|integer|min:0|max:'.(9 - min(9, max(1, (int) $request->input('adults', 1)))),
            'infants' => 'nullable|integer|min:0|max:9',
            'cabin_class' => 'nullable|in:ECONOMY,PREMIUM_ECONOMY,BUSINESS,FIRST',
            'preferred_airline' => 'nullable|string|max:3',
            'fare_type' => 'nullable|in:REGULAR,STUDENT,SENIOR_CITIZEN',
            'direct_flight' => 'nullable|boolean',
        ]);

        $from = strtoupper($validated['from']);
        $to = strtoupper($validated['to']);
        $routeInfos = [[
            'fromCityOrAirport' => ['code' => $from],
            'toCityOrAirport' => ['code' => $to],
            'travelDate' => $validated['depart_date'],
        ]];
        if (! empty($validated['return_date'])) {
            $routeInfos[] = [
                'fromCityOrAirport' => ['code' => $to],
                'toCityOrAirport' => ['code' => $from],
                'travelDate' => $validated['return_date'],
            ];
        }

        $adults = (int) $validated['adults'];
        $paxInfo = array_filter([
            'ADULT' => $adults,
            'CHILD' => (int) ($validated['children'] ?? 0) ?: null,
            'INFANT' => min($adults, (int) ($validated['infants'] ?? 0)) ?: null,
        ]);
        $cabinClass = $validated['cabin_class'] ?? 'ECONOMY';
        $airline = strtoupper((string) ($validated['preferred_airline'] ?? ''));
        $directOnly = (bool) ($validated['direct_flight'] ?? false);
        $modifiers = self::searchModifiers($validated['fare_type'] ?? 'REGULAR', $directOnly);

        $cacheKey = 'flight_fare_calendar:'.md5(json_encode([$paxInfo, $routeInfos, $cabinClass, $modifiers, $airline, $directOnly]));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return response()->json(['success' => true, 'minPrice' => $cached]);
        }

        try {
            $response = $client->search($paxInfo, $routeInfos, $cabinClass, $modifiers, $airline !== '' ? [$airline] : []);
        } catch (TripJackException $e) {
            return response()->json(['success' => false, 'message' => 'Fare unavailable right now.']);
        }

        $tripInfos = ($response['status']['success'] ?? false) ? ($response['searchResult']['tripInfos'] ?? []) : [];

        // Every requested leg must have at least one fare, otherwise there is
        // no bookable total for that date. A COMBO list (international
        // return) already prices the whole journey in one fare.
        $total = isset($tripInfos['COMBO']) || count($tripInfos) === count($routeInfos) ? 0.0 : null;
        foreach ($tripInfos as $itineraries) {
            $legMin = null;
            foreach ((array) $itineraries as $itinerary) {
                if ($directOnly && ! self::isNonStop($itinerary)) {
                    continue;
                }
                foreach ($itinerary['totalPriceList'] ?? [] as $option) {
                    $tf = (float) (($option['fd'] ?? $option['fD'] ?? [])['ADULT']['fC']['TF'] ?? 0);
                    if ($tf > 0 && ($legMin === null || $tf < $legMin)) {
                        $legMin = $tf;
                    }
                }
            }
            if ($legMin === null || $total === null) {
                $total = null;
                break;
            }
            $total += $legMin;
        }

        if ($total === null) {
            return response()->json(['success' => false, 'message' => 'No flights']);
        }

        $total = (int) round($total);
        Cache::put($cacheKey, $total, now()->addMinutes(15));

        return response()->json(['success' => true, 'minPrice' => $total]);
    }

    /**
     * POST /flights/review —revalidates the guest's selected priceId(s)
     * (1 for Oneway, 2 for Return — ONWARD + RETURN, per TripJack's docs)
     * and stores the reviewed option in session. PRG: redirects to the GET
     * counterpart rather than rendering directly, so a refresh never
     * re-submits/re-reviews.
     */
    public function review(Request $request, TripJackFlightClient $client)
    {
        $request->validate(['price_ids' => 'required|array|min:1|max:6', 'price_ids.*' => 'required|string']);
        // The form submits price_ids as an associative array (keyed
        // ONWARD/RETURN, for the "select one radio per leg" UI) — TripJack's
        // priceIds must be a plain JSON array, so re-index before sending.
        $priceIds = array_values($request->input('price_ids'));

        $context = session('flight_search_context');

        // A failed pick goes back to the SAME results list (re-searched, so
        // the guest sees fresh fares), not a blank search page. The reason
        // rides in the URL as a fixed notice code: "Select & Continue" opens
        // a new tab, and the results tab left behind keeps making background
        // requests that can consume a one-request flash message before the
        // new tab renders it — which left guests on an empty search page with
        // no explanation. The flash is kept for the code's fuller message.
        $back = function (string $notice, ?string $message = null) {
            $previous = url()->previous();
            $isResults = $previous && str_starts_with($previous, route('flights.search').'?');
            $target = $isResults ? preg_replace('/([?&])notice=[^&]*&?/', '$1', $previous) : route('flights.search');
            $target = rtrim($target, '?&').(str_contains($target, '?') ? '&' : '?').'notice='.$notice;

            return redirect()->to($target)->with('booking_error', $message);
        };

        if (! $context) {
            return $back('expired', 'Your search session has expired. Please search again.');
        }

        $expectedCount = match (true) {
            ! empty($context['combo']) => 1,
            $context['tripType'] === 'multi' => count($context['legs'] ?? []),
            $context['tripType'] === 'return' => 2,
            default => 1,
        };
        if (count($priceIds) !== $expectedCount) {
            return $back('legs', 'Please select a flight for every leg of your trip.');
        }

        try {
            $response = $client->review($priceIds);
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode);
            Log::channel('tripjack')->warning('flight_review_failed', ['errorCode' => $errorCode, 'message' => $e->getMessage()]);

            // Confirmed live: Review rejects an unmatched Special Return pair
            // (different airlines, a regular fare on one leg, or an Air India
            // fare that isn't its named partner) with "All Segments Must be
            // selected if Special Return fare." — explain it, and send the
            // guest back to the same results rather than a blank search.
            if (stripos($e->getMessage(), 'Special Return') !== false) {
                return $back('special_return', 'Special Return fares can only be booked as a matched pair. Please pick the return fare marked “Pairs with your onward fare”, or choose regular fares on both flights.');
            }

            // 1000 = "Requested flight is no longer available" (sold out
            // since the search — common, especially on the sandbox).
            return $back((string) $errorCode === '1000' ? 'unavailable' : 'review_failed', $described['message']);
        }

        if (! ($response['status']['success'] ?? false) || empty($response['bookingId'])) {
            $errorCode = TripJackFlightErrorCatalog::codeFromResponse($response);
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'This fare is no longer available. Please search again.');

            return $back((string) $errorCode === '1000' ? 'unavailable' : 'review_failed', $described['message']);
        }

        session(['flight_booking_draft' => [
            'bookingId' => $response['bookingId'],
            'response' => $response,
            'context' => $context,
            // The results page this fare was picked from, so the fare-change
            // popup's "Back" returns the guest to the same list.
            'resultsUrl' => url()->previous(),
            // conditions.st = how long (seconds) the reviewed fare and its
            // bookingId stay valid — 840 s on live fares checked. Timed on
            // our own clock from now, rather than parsing TripJack's sct
            // timestamp (whose timezone isn't stated).
            'expiresAt' => ! empty($response['conditions']['st']) ? now()->addSeconds((int) $response['conditions']['st'])->timestamp : null,
            // Markup fixed for this booking, so a Flight Settings change
            // mid-booking doesn't change the price the guest already saw.
            'marginRate' => FlightSettings::marginRate(),
        ]]);

        return redirect()->route('flights.review.show');
    }

    /**
     * Add-on (SSR) types offered at booking time, mapped to their Book API
     * field. Only types confirmed live end-to-end are listed — any other
     * ssrInfo type TripJack returns is not offered, so a guest can never pay
     * for something Book would then reject. FASTFORWARD's field is NOT the
     * doc's ssrExtraServiceInfos (confirmed live: that 400s with a generic
     * "bad data" error; ssrFastForwardInfos books SUCCESS).
     */
    private const ADDON_BOOK_FIELDS = [
        'BAGGAGE' => 'ssrBaggageInfos',
        'MEAL' => 'ssrMealInfos',
        'FASTFORWARD' => 'ssrFastForwardInfos',
    ];

    /**
     * Step 1 — Flight Itinerary: flight details, fare rules and fare summary
     * for the reviewed fare, from the session draft.
     */
    public function showReview(TripJackFlightClient $client)
    {
        $draft = session('flight_booking_draft');
        if (! $draft) {
            return redirect()->route('flights.search')->with('booking_error', 'Your booking session has expired. Please search again.');
        }

        $response = $draft['response'];
        $context = $draft['context'];
        // Confirmed against a live sandbox response: Review's tripInfos is a
        // flat numeric array of itinerary objects (each its own sI segment
        // list) — unlike Search's tripInfos, it is NOT keyed ONWARD/RETURN.
        $tripInfos = $response['tripInfos'] ?? [];
        $conditions = $response['conditions'] ?? [];
        $breakdown = $this->reviewBreakdown($response, $draft['marginRate'] ?? null);

        // Best-effort, display-only — never block the page if it fails.
        // flowType=REVIEW with the Review bookingId (confirmed live).
        $fareRules = null;
        try {
            $fareRuleResponse = $client->fareRule('REVIEW', $draft['bookingId']);
            $fareRules = $fareRuleResponse['fareRule'] ?? null;
        } catch (TripJackException $e) {
            Log::channel('tripjack')->info('flight_review_farerule_failed', ['bookingId' => $draft['bookingId'], 'message' => $e->getMessage()]);
        }

        $fareAlerts = $this->normalizeReviewAlerts($response['alerts'] ?? []);
        $resultsUrl = $draft['resultsUrl'] ?? route('flights.search');

        $sessionExpiresAt = $draft['expiresAt'] ?? null;

        return view('pages.flight-review', compact('response', 'context', 'tripInfos', 'conditions', 'breakdown', 'fareRules', 'fareAlerts', 'resultsUrl', 'sessionExpiresAt'));
    }

    /**
     * Step 2 — Passenger Details: traveller, contact and GST details plus
     * seat / baggage / meal / other-service add-ons for the reviewed fare.
     */
    public function showPassengers(Request $request, TripJackFlightClient $client)
    {
        $draft = session('flight_booking_draft');
        if (! $draft) {
            return redirect()->route('flights.search')->with('booking_error', 'Your booking session has expired. Please search again.');
        }
        // The Continue link carries the fare its Step 1 tab showed; a stale
        // tab (another fare reviewed since) is sent back to the current one.
        if ($request->query('booking') !== $draft['bookingId']) {
            return redirect()->route('flights.review.show')->withErrors([
                'review_booking_id' => 'You opened another flight in a different tab, so that page was out of date. Please check the flight and fare shown here before continuing.',
            ]);
        }

        $response = $draft['response'];
        $context = $draft['context'];
        $tripInfos = $response['tripInfos'] ?? [];
        $conditions = $response['conditions'] ?? [];
        $breakdown = $this->reviewBreakdown($response, $draft['marginRate'] ?? null);
        $addonTrips = $this->addonOptions($response);

        // Live seat maps keyed by segment id — best-effort; without one the
        // seat picker is simply hidden (unless seats are mandatory, which
        // submitBooking() then enforces with a clear error).
        $seatMaps = [];
        if ($conditions['isa'] ?? false) {
            try {
                $seatMaps = $client->seatMap($draft['bookingId'])['tripSeatMap']['tripSeat'] ?? [];
            } catch (TripJackException $e) {
                Log::channel('tripjack')->info('flight_passengers_seatmap_failed', ['bookingId' => $draft['bookingId'], 'message' => $e->getMessage()]);
            }
        }

        $savedTravellers = $request->user()->savedTravellers()->orderBy('first_name')->get();
        $resultsUrl = $draft['resultsUrl'] ?? route('flights.search');

        $sessionExpiresAt = $draft['expiresAt'] ?? null;

        // Back from Step 3 (Review): refill the form — add-on picks included,
        // the page restores them from old('travellers') — with what the guest
        // already entered, unless a failed submit just flashed newer input.
        if (! $request->session()->hasOldInput() && ! empty($draft['passengerReview']['input'])) {
            $request->session()->now('_old_input', $draft['passengerReview']['input']);
        }

        return view('pages.flight-passengers', compact('response', 'context', 'tripInfos', 'conditions', 'breakdown', 'addonTrips', 'seatMaps', 'savedTravellers', 'resultsUrl', 'sessionExpiresAt'));
    }

    /**
     * Step 3 — Review: the flight, every traveller with their seat / meal /
     * baggage picks, contact details and the final price, before the guest
     * pays or blocks the fare. Shows the details validated by Step 2's
     * submitBooking(intent=review); nothing has been booked yet.
     */
    public function showConfirm()
    {
        $draft = session('flight_booking_draft');
        if (! $draft) {
            return redirect()->route('flights.search')->with('booking_error', 'Your booking session has expired. Please search again.');
        }
        $reviewed = $draft['passengerReview'] ?? null;
        if (! $reviewed) {
            return redirect()->route('flights.passengers.show', ['booking' => $draft['bookingId']]);
        }

        $response = $draft['response'];
        $context = $draft['context'];
        $tripInfos = $response['tripInfos'] ?? [];
        $conditions = $response['conditions'] ?? [];
        $input = $reviewed['input'];
        $addonsTotal = (float) $reviewed['addonsTotal'];

        // Same formula the Passenger Details page shows live and that
        // submitBooking() charges: add-ons fold into the marked-up total.
        $fareBreakdown = $this->reviewBreakdown($response, $draft['marginRate'] ?? null);
        $priced = FlightPricingService::price($fareBreakdown['tripjack_total_price'] + $addonsTotal, $draft['marginRate'] ?? null);
        $summary = [
            'base_fare' => $fareBreakdown['base_fare'],
            'airline_taxes' => $fareBreakdown['airline_taxes'],
            'convenience_fee' => max(0, round($priced['customer_price'] - $priced['tripjack_total_price'], 2)),
            'addons' => $addonsTotal,
            // Same labels as the Passenger Details summary, in a fixed order.
            'addon_rows' => collect(['seats' => 'Seats', 'baggage' => 'Extra baggage', 'meals' => 'Meals', 'fastforward' => 'Other services'])
                ->filter(fn ($label, $kind) => ! empty($reviewed['addonTotals'][$kind]['count']))
                ->map(fn ($label, $kind) => [
                    'label' => $label,
                    'count' => (int) $reviewed['addonTotals'][$kind]['count'],
                    'amount' => round((float) $reviewed['addonTotals'][$kind]['amount'], 2),
                ])
                ->values()
                ->all(),
            'total' => $priced['customer_price'],
        ];

        $departure = self::travelDates($tripInfos)[0] ?? null;
        $travellers = collect($reviewed['travellerInfo'])->map(function ($t, $i) use ($input, $reviewed, $departure) {
            return [
                'name' => trim(($t['ti'] ?? '').' '.($t['fN'] ?? '').' '.($t['lN'] ?? '')),
                'type' => $t['pt'] ?? 'ADULT',
                'dob' => $t['dob'] ?? null,
                'passport' => $t['pNum'] ?? null,
                'passportExpiry' => $t['eD'] ?? null,
                'passportWarning' => self::passportValidityWarning($t['eD'] ?? null, $departure),
                'frequentFlyer' => $input['travellers'][$i]['frequent_flyer'] ?? null,
                'seats' => $reviewed['addonLines'][$i]['seats'] ?? [],
                'extras' => $reviewed['addonLines'][$i]['extras'] ?? [],
            ];
        })->all();

        // Hold blocks the PNR without payment, so it's only offered when the
        // airline allows it (isBA) and nothing paid was added.
        $canHold = (bool) ($conditions['isBA'] ?? false) && $addonsTotal <= 0 && FlightSettings::allowHold();
        $resultsUrl = $draft['resultsUrl'] ?? route('flights.search');
        $sessionExpiresAt = $draft['expiresAt'] ?? null;
        $bookingId = $draft['bookingId'];

        return view('pages.flight-confirm', compact('response', 'context', 'tripInfos', 'summary', 'travellers', 'input', 'canHold', 'resultsUrl', 'sessionExpiresAt', 'bookingId'));
    }

    /**
     * TYTLUXE's price for the reviewed fare, recalculated from Review's
     * freshly re-validated TF (never a figure from the Search step).
     * Confirmed: the total fare lives at totalPriceInfo.totalFareDetail.fC.
     */
    private function reviewBreakdown(array $response, ?float $marginRate = null): array
    {
        $fc = $response['totalPriceInfo']['totalFareDetail']['fC'] ?? [];
        $afc = $response['totalPriceInfo']['totalFareDetail']['afC']['TAF'] ?? [];
        $breakdown = FlightPricingService::price((float) ($fc['TF'] ?? 0), $marginRate);
        $breakdown['margin_rate'] = $marginRate;
        $breakdown['base_fare'] = round((float) ($fc['BF'] ?? 0), 2);
        $breakdown['airline_taxes'] = round((float) ($fc['TAF'] ?? 0), 2);
        $breakdown['tripjack_mf'] = round((float) ($afc['MF'] ?? 0), 2);
        $breakdown['tripjack_mft'] = round((float) ($afc['MFT'] ?? 0), 2);

        return $breakdown;
    }

    /**
     * Bookable add-ons per trip/segment, read from Review's segment ssrInfo
     * (the server-side source of truth for their prices). Confirmed live:
     * baggage is per journey — only a trip's first segment carries prices,
     * and sending it keyed to that segment applies it to the whole trip —
     * so baggage is offered on each trip's first segment only.
     *
     * @return array<int, array{label: string, seatMandatory: bool, segments: array<int, array{id: string, label: string, options: array<string, array<string, array{desc: string, amount: float}>>}>}>
     */
    private function addonOptions(array $response): array
    {
        $trips = [];
        foreach ($response['tripInfos'] ?? [] as $trip) {
            $segs = $trip['sI'] ?? [];
            if (! $segs) {
                continue;
            }
            $segments = [];
            foreach ($segs as $idx => $seg) {
                $options = [];
                foreach (array_keys(self::ADDON_BOOK_FIELDS) as $type) {
                    if ($type === 'BAGGAGE' && $idx > 0) {
                        continue;
                    }
                    foreach ($seg['ssrInfo'][$type] ?? [] as $item) {
                        if (empty($item['code'])) {
                            continue;
                        }
                        $options[$type][$item['code']] = [
                            'desc' => $item['desc'] ?? $item['code'],
                            'amount' => round((float) ($item['amount'] ?? 0), 2),
                        ];
                    }
                }
                $segments[] = [
                    'id' => (string) $seg['id'],
                    'label' => ($seg['da']['code'] ?? '').' → '.($seg['aa']['code'] ?? ''),
                    'options' => $options,
                ];
            }
            $trips[] = [
                'label' => ($segs[0]['da']['code'] ?? '').' → '.(end($segs)['aa']['code'] ?? ''),
                // Confirmed live: trip-level `ism` is set on fares whose Book
                // 400s with "Seat Selection is Mandatory" when no seat is sent.
                'seatMandatory' => (bool) ($trip['ism'] ?? false),
                'segments' => $segments,
            ];
        }

        return $trips;
    }

    /**
     * Flattens Review's `alerts` into display rows, grouped by sector.
     * Confirmed live: detail changes arrive as
     * {type: FAREALERT, miscAlert: {"BLR-DEL": [{key, oldValue, newValue}]}};
     * price changes carry top-level oldFare/newFare (TripJack's own fare,
     * same basis as the results page — not TYTLUXE's marked-up total).
     *
     * @return array<string, array<int, array{label: string, old: string, new: string}>>
     */
    private function normalizeReviewAlerts(array $alerts): array
    {
        $grouped = [];

        foreach ($alerts as $alert) {
            if (isset($alert['oldFare'], $alert['newFare']) && (float) $alert['oldFare'] !== (float) $alert['newFare']) {
                $grouped['Fare'][] = [
                    'label' => 'Fare',
                    'old' => '₹'.number_format((float) $alert['oldFare'], 2),
                    'new' => '₹'.number_format((float) $alert['newFare'], 2),
                ];
            }

            foreach ($alert['miscAlert'] ?? [] as $sector => $items) {
                foreach ((array) $items as $item) {
                    $old = trim((string) ($item['oldValue'] ?? ''));
                    $new = trim((string) ($item['newValue'] ?? ''));
                    if ($old === $new) {
                        continue;
                    }
                    $grouped[$sector][] = [
                        'label' => $item['key'] ?? 'Details',
                        'old' => $old !== '' ? $old : '—',
                        'new' => $new !== '' ? $new : '—',
                    ];
                }
            }
        }

        return $grouped;
    }

    /**
     * First departure and last flight's departure date of the journey.
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}|null
     */
    public static function travelDates(array $tripInfos): ?array
    {
        $segments = collect($tripInfos)->flatMap(fn ($trip) => $trip['sI'] ?? [])->filter(fn ($s) => ! empty($s['dt']));
        if ($segments->isEmpty()) {
            return null;
        }

        return [
            \Carbon\Carbon::parse($segments->first()['dt'])->startOfDay(),
            \Carbon\Carbon::parse($segments->last()['dt'])->startOfDay(),
        ];
    }

    /**
     * Doc (Booking API errors): adult 12–100 at departure, child 2–12,
     * infant 0–2, senior citizen over 60 at departure (2569); live FAQ:
     * senior fares are ADULT-only, minimum age 60. An infant travels on a
     * lap, so must still be under 2 on the last flight of the trip.
     *
     * @return array{min: string, max: string} Allowed date-of-birth range
     */
    public static function dobRange(string $paxType, \Carbon\Carbon $departure, \Carbon\Carbon $lastFlight, ?string $fareType = null): array
    {
        [$min, $max] = match ($paxType) {
            'INFANT' => [$lastFlight->copy()->subYears(2)->addDay(), now()->subDay()],
            'CHILD' => [$departure->copy()->subYears(12)->addDay(), $departure->copy()->subYears(2)],
            default => [$departure->copy()->subYears(101)->addDay(), $departure->copy()->subYears($fareType === 'SENIOR_CITIZEN' ? 60 : 12)],
        };

        return ['min' => $min->toDateString(), 'max' => min($max, now()->subDay())->toDateString()];
    }

    /** Most countries want a passport valid this long after arrival. */
    public const PASSPORT_VALIDITY_MONTHS = 6;

    /**
     * A passport that lasts the trip but has under 6 months left from the
     * first departure. Not a block: entry rules belong to the destination
     * (UAE/Thailand: 6 months from arrival; Schengen: 3 months after leaving;
     * some only the stay) and the airline checks them at check-in — a hard
     * stop would turn away travellers who'd be allowed in.
     */
    public static function passportValidityWarning(?string $expiry, ?\Carbon\Carbon $departure): ?string
    {
        if (! $expiry || ! $departure) {
            return null;
        }
        try {
            $expires = \Carbon\Carbon::parse($expiry)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
        if ($expires->gte($departure->copy()->addMonths(self::PASSPORT_VALIDITY_MONTHS))) {
            return null;
        }

        return 'This passport has less than '.self::PASSPORT_VALIDITY_MONTHS.' months left from your departure date. Many countries require at least 6 months of validity on arrival and the airline can refuse boarding — please check the entry rules for your destination before you pay.';
    }

    /**
     * Fare Validate (pre-book) — confirms the reviewed fare and booking class
     * are still available, using the same traveller/SSR payload as Book.
     * Confirmed live: a valid fare returns status.success with the
     * re-checked totalPriceInfo (plus `alerts` when it changed, and `iobfe`
     * when the old booking fare expired); an unavailable fare returns a 400.
     *
     * Returns a redirect when the guest must not proceed to payment, or null
     * to continue. A TripJack outage is deliberately not treated as a
     * failure: Book re-validates after payment anyway, and a failed Book is
     * refunded automatically (FlightBookingService).
     */
    private function validateFareBeforePayment(TripJackFlightClient $client, array $draft, array $travellerInfo, string $email, string $phone, ?array $gstInfo, float $addonsTotal, ?array $contactInfo = null)
    {
        // Built only when actually returned: RedirectResponse::with() writes
        // the flash to the session immediately, so creating it up front
        // flashed "fare no longer available" on EVERY validation — it then
        // showed on the payment page even though the fare was fine.
        // Back to the guest's own results (fresh fares) with the same
        // ?notice=unavailable toast review() uses.
        $soldOut = function () use ($draft) {
            $resultsUrl = $draft['resultsUrl'] ?? '';
            $target = str_starts_with($resultsUrl, route('flights.search').'?') ? $resultsUrl : route('flights.search');
            $target = preg_replace('/([?&])notice=[^&]*&?/', '$1', $target);

            return redirect()->to(rtrim($target, '?&').(str_contains($target, '?') ? '&' : '?').'notice=unavailable');
        };

        try {
            $result = $client->fareValidatePreBook(array_filter([
                'bookingId' => $draft['bookingId'],
                'deliveryInfo' => [
                    'emails' => [$email],
                    'contacts' => [$phone],
                ],
                'contactInfo' => $contactInfo,
                'travellerInfo' => $travellerInfo,
                'gstInfo' => $gstInfo,
            ], fn ($v) => $v !== null));
        } catch (TripJackApiException $e) {
            // A 5xx is an outage, not an answer — same as a timeout below.
            if (FlightBookingService::isUncertainOutcome($e)) {
                Log::channel('tripjack')->warning('flight_fare_validate_skipped', ['bookingId' => $draft['bookingId'], 'message' => $e->getMessage()]);

                return null;
            }

            // Fare Validate checks the traveller details too: a rejected
            // passport, mobile, age, GSTIN… is the guest's to correct, not a
            // sold-out fare — send them back to the form with their input.
            if (TripJackFlightErrorCatalog::isTravellerInputError($e->errorCode)) {
                Log::channel('tripjack')->info('flight_fare_validate_input_rejected', ['bookingId' => $draft['bookingId'], 'errorCode' => $e->errorCode, 'message' => $e->getMessage()]);

                return redirect()->route('flights.passengers.show', ['booking' => $draft['bookingId']])
                    ->withInput()
                    ->withErrors(['travellers' => TripJackFlightErrorCatalog::describe($e->errorCode)['message']]);
            }

            Log::channel('tripjack')->warning('flight_fare_validate_unavailable', ['bookingId' => $draft['bookingId'], 'errorCode' => $e->errorCode, 'message' => $e->getMessage()]);

            return $soldOut();
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('flight_fare_validate_skipped', ['bookingId' => $draft['bookingId'], 'message' => $e->getMessage()]);

            return null;
        }

        if (! ($result['status']['success'] ?? false) || ! empty($result['iobfe'])) {
            return $soldOut();
        }

        $oldFare = (float) ($draft['response']['totalPriceInfo']['totalFareDetail']['fC']['TF'] ?? 0);
        $newFare = (float) ($result['totalPriceInfo']['totalFareDetail']['fC']['TF'] ?? $oldFare);
        $hasFareAlert = collect($result['alerts'] ?? [])->contains(fn ($a) => ($a['type'] ?? '') === 'FAREALERT');

        if (abs($newFare - $oldFare) < 0.01 && ! $hasFareAlert) {
            return null;
        }

        // The fare moved: store the new one so the page (and the next
        // submit) use it, and ask the guest to confirm before paying.
        $draft['response']['totalPriceInfo'] = $result['totalPriceInfo'] ?? $draft['response']['totalPriceInfo'];
        session(['flight_booking_draft' => $draft]);
        $oldPrice = FlightPricingService::price($oldFare + $addonsTotal, $draft['marginRate'] ?? null)['customer_price'];
        $newPrice = FlightPricingService::price($newFare + $addonsTotal, $draft['marginRate'] ?? null)['customer_price'];
        Log::channel('tripjack')->info('flight_fare_validate_changed', ['bookingId' => $draft['bookingId'], 'old' => $oldFare, 'new' => $newFare]);

        return back()->withInput()->withErrors([
            'fare_changed' => abs($newPrice - $oldPrice) >= 0.01
                ? 'The airline has just changed this fare from ₹'.number_format($oldPrice, 2).' to ₹'.number_format($newPrice, 2).'. The updated amount is shown below — press Proceed to Pay again to continue at the new price.'
                : 'The airline has updated this fare’s details. Please check the fare summary below and press Proceed to Pay again to continue.',
        ]);
    }

    /**
     * POST /flights/book — guest submits traveller details; creates the
     * local Booking + Razorpay order and sends them to pay. TripJack's Book
     * API is NOT called here — only once payment is captured, via
     * FlightBookingService::confirmAfterPayment() — mirrors the hotel flow.
     */
    public function submitBooking(Request $request, RazorpayService $razorpay, TripJackFlightClient $client)
    {
        $draft = session('flight_booking_draft');
        if (! $draft) {
            return redirect()->route('flights.search')->with('booking_error', 'Your booking session has expired. Please search again.');
        }

        // Review opens in a new tab and the draft is one-per-session, so a
        // guest with two review tabs could otherwise submit tab A's form
        // against tab B's (different, possibly pricier) fare.
        if ($request->input('review_booking_id') !== $draft['bookingId']) {
            return redirect()->route('flights.review.show')->withErrors([
                'review_booking_id' => 'You opened another flight in a different tab, so this page was out of date. Please check the flight and fare shown here before continuing.',
            ]);
        }

        // The reviewed fare's TripJack session (conditions.st) has run out —
        // its bookingId can no longer be booked, so don't take payment.
        if (! empty($draft['expiresAt']) && now()->timestamp >= $draft['expiresAt']) {
            return redirect($draft['resultsUrl'] ?? route('flights.search'))->with('booking_error', 'Your fare session expired before booking was completed, so prices may have changed. Please choose your flight again.');
        }

        // Step 2 posts intent=review (validate + show Step 3, book nothing);
        // Step 3's Proceed to Pay / Block post intent=pay|hold with
        // from_review, and the details the guest reviewed are replayed from
        // the session — every check below runs again on them before booking.
        $intent = in_array($request->input('intent'), ['review', 'pay', 'hold'], true) ? $request->input('intent') : 'pay';
        if ($request->boolean('from_review')) {
            $reviewed = $draft['passengerReview']['input'] ?? null;
            if (! $reviewed) {
                return redirect()->route('flights.passengers.show', ['booking' => $draft['bookingId']])
                    ->withErrors(['travellers' => 'Please enter your passenger details again.']);
            }
            // Step 3's agreement checkbox (Terms, Privacy Policy, fare rules).
            if (! $request->boolean('accept_terms')) {
                return redirect()->route('flights.confirm.show')
                    ->withErrors(['accept_terms' => 'Please accept the Terms of Use, Privacy Policy and fare rules to continue.']);
            }
            $request->merge($reviewed);
            $request->merge(['intent' => $intent]);
        }

        $response = $draft['response'];
        $context = $draft['context'];
        $conditions = $response['conditions'] ?? [];
        $panRequired = (bool) ($conditions['gst']['ipa'] ?? $conditions['ipa'] ?? false);
        $passportRequired = (bool) ($conditions['pcs']['pm'] ?? $conditions['pm'] ?? false);
        // Per passenger type (adult / child / infant) — e.g. IndiGo requires
        // DOB for infants only. A senior citizen fare always needs it — the
        // age is checked at Book (doc error 2569).
        $dobFlags = [
            'ADULT' => (bool) ($conditions['dob']['adobr'] ?? false) || ($context['fareType'] ?? null) === 'SENIOR_CITIZEN',
            'CHILD' => (bool) ($conditions['dob']['cdobr'] ?? false),
            'INFANT' => true,
        ];
        $gstMandatory = (bool) ($conditions['gst']['igm'] ?? false);
        // Confirmed live: a STUDENT fare's Review returned dc.ida and dc.idm
        // both true — Book then needs each traveller's ID as travellerInfo.di.
        $docApplicable = (bool) ($conditions['dc']['ida'] ?? false);
        $docMandatory = (bool) ($conditions['dc']['idm'] ?? false);
        $emergencyRequired = (bool) ($conditions['iecr'] ?? false);

        $totalPax = $context['adults'] + $context['children'] + $context['infants'];
        $firstNameMax = (int) ($conditions['anlm']['fN'] ?? 100);
        $lastNameMax = (int) ($conditions['anlm']['lN'] ?? 100);
        $nameRegex = "regex:/^[A-Za-z\\s.'-]+$/";
        // Each number is checked against its own country-code field
        // (contact_dial_code / emergency_dial_code, default +91): India needs
        // 10 digits, other countries 6–14 (E.164 caps the whole number at 15).
        $phoneRule = function ($attribute, $value, $fail) use ($request) {
            $dialField = $attribute === 'emergency_phone' ? 'emergency_dial_code' : 'contact_dial_code';
            $code = preg_replace('/\D/', '', (string) $request->input($dialField, '+91')) ?: '91';
            $digits = preg_replace('/\D/', '', (string) $value);

            if ($code === '91') {
                $digits = preg_replace('/^(91|0)(?=\d{10}$)/', '', $digits);
                if (! preg_match('/^\d{10}$/', $digits)) {
                    $fail('Please enter a valid 10-digit mobile number.');
                }
            } elseif (! preg_match('/^\d{6,14}$/', ltrim($digits, '0')) || strlen($code.ltrim($digits, '0')) > 15) {
                $fail('Please enter a valid mobile number for the selected country code.');
            }
        };
        $dialCodeRule = ['nullable', 'string', 'regex:/^\+?[1-9]\d{0,3}$/'];

        $rules = [
            'contact_email' => 'required|email|max:255',
            'contact_dial_code' => $dialCodeRule,
            'contact_phone' => ['required', 'string', 'max:20', $phoneRule],
            'special_requests' => 'nullable|string|max:500',
            'travellers' => 'required|array|size:'.$totalPax,
        ];
        $travelDates = self::travelDates($response['tripInfos'] ?? []);
        $ageMessages = [];
        for ($i = 0; $i < $totalPax; $i++) {
            $paxType = $i < $context['adults'] ? 'ADULT' : ($i < $context['adults'] + $context['children'] ? 'CHILD' : 'INFANT');
            // Doc titles: Mr/Mrs/Ms (adult), Master/Ms (child/infant). "Miss"
            // is still accepted from details entered before this changed and
            // is sent to TripJack as Ms.
            $rules["travellers.{$i}.title"] = 'required|string|in:'.($paxType === 'ADULT' ? 'Mr,Mrs,Ms' : 'Master,Ms,Miss');
            $rules["travellers.{$i}.first_name"] = ['required', 'string', 'max:'.$firstNameMax, $nameRegex];
            $rules["travellers.{$i}.last_name"] = ['required', 'string', 'max:'.$lastNameMax, $nameRegex];
            $rules["travellers.{$i}.save"] = 'nullable|boolean';
            foreach (['seats', 'baggage', 'meals', 'fastforward'] as $addon) {
                $rules["travellers.{$i}.{$addon}"] = 'nullable|array';
                $rules["travellers.{$i}.{$addon}.*"] = 'nullable|string|max:20';
            }
            if ($dobFlags[$paxType]) {
                $rules["travellers.{$i}.dob"] = ['required', 'date', 'before:today'];
                if ($travelDates) {
                    $range = self::dobRange($paxType, $travelDates[0], $travelDates[1], $context['fareType'] ?? null);
                    $rules["travellers.{$i}.dob"][] = 'after_or_equal:'.$range['min'];
                    $rules["travellers.{$i}.dob"][] = 'before_or_equal:'.$range['max'];
                    $ageMessages["travellers.{$i}.dob.after_or_equal"] = match ($paxType) {
                        'INFANT' => 'Infants must be under 2 years old for the whole trip. Please book this traveller as a child.',
                        'CHILD' => 'Children must be under 12 years old on the travel date. Please book this traveller as an adult.',
                        default => 'Please check the date of birth — adults must be 100 or younger on the travel date.',
                    };
                    $ageMessages["travellers.{$i}.dob.before_or_equal"] = match (true) {
                        $paxType === 'CHILD' => 'Children must be at least 2 years old on the travel date. Please book this traveller as an infant.',
                        $paxType === 'ADULT' && ($context['fareType'] ?? null) === 'SENIOR_CITIZEN' => 'Senior citizen fares are only for travellers aged 60 or over on the travel date.',
                        $paxType === 'ADULT' => 'Adults must be at least 12 years old on the travel date. Please book this traveller as a child.',
                        default => 'The date of birth must be in the past.',
                    };
                }
            }
            if ($passportRequired) {
                $rules["travellers.{$i}.passport_number"] = ['required', 'string', 'regex:/^[A-Za-z0-9]{6,20}$/'];
                // Must not expire before the trip ends — the guest couldn't
                // finish the journey. (Under 6 months from departure is only
                // a warning: the real rule depends on the destination, see
                // passportValidityWarning().)
                $rules["travellers.{$i}.passport_expiry"] = array_filter(['required', 'date', 'after:today',
                    $travelDates ? 'after_or_equal:'.$travelDates[1]->toDateString() : null]);
                // Sent as pNat (2-letter code) and pid — both confirmed
                // accepted and stored by a live sandbox hold.
                $rules["travellers.{$i}.passport_nationality"] = ['required', 'string', 'size:2', \Illuminate\Validation\Rule::in(array_keys(require resource_path('data/countries.php')))];
                $rules["travellers.{$i}.passport_issue_date"] = 'required|date|before_or_equal:today';
            }
            if ($docApplicable && $paxType !== 'INFANT') {
                $rules["travellers.{$i}.document_id"] = [$docMandatory ? 'required' : 'nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9\/-]+$/'];
            }
            if ($panRequired && $paxType === 'ADULT') {
                // Lead traveller's PAN is required; other adults may add theirs.
                // 4th letter = holder type (same check as hotels — TripJack
                // rejects shape-only-valid PANs like ABCDE1234F with 1092).
                $rules["travellers.{$i}.pan"] = [$i === 0 ? 'required' : 'nullable', 'string', \App\Http\Controllers\FrontendController::PAN_REGEX];
            }
            $rules["travellers.{$i}.frequent_flyer"] = ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'];
        }
        if ($emergencyRequired) {
            $rules['emergency_name'] = ['required', 'string', 'max:60', $nameRegex];
            $rules['emergency_dial_code'] = $dialCodeRule;
            $rules['emergency_phone'] = ['required', 'string', 'max:20', $phoneRule];
            $rules['emergency_email'] = 'nullable|email|max:255';
        }
        // GST is optional unless the fare mandates it; once started, both
        // fields are needed.
        $rules['gst_number'] = [$gstMandatory ? 'required' : 'nullable', 'required_with:gst_registered_name', 'string', 'regex:/^[0-9]{2}[A-Za-z0-9]{13}$/'];
        $rules['gst_registered_name'] = [$gstMandatory ? 'required' : 'nullable', 'required_with:gst_number', 'string', 'max:35'];
        $validated = $request->validate($rules, [
            'gst_number.regex' => 'Please enter a valid 15-character GSTIN.',
            'travellers.*.pan.regex' => 'Please enter a valid PAN, e.g. ABCPE1234F — the 4th letter is the holder type (P for an individual).',
            'travellers.*.document_id.required' => 'Please enter the ID number for this '.strtolower(str_replace('_', ' ', $context['fareType'] ?? '')).' fare.',
            'travellers.*.passport_issue_date.before_or_equal' => 'The passport issue date can’t be in the future.',
            'travellers.*.passport_expiry.after_or_equal' => 'This passport expires before your trip ends. Please use a passport that is valid for the whole journey.',
        ] + $ageMessages);

        $contactPhone = FlightBookingService::phoneFromInput($validated['contact_dial_code'] ?? null, $validated['contact_phone']);

        $leadNames = collect($validated['travellers'])->map(fn ($t) => strtolower(trim(($t['first_name'] ?? '').' '.($t['last_name'] ?? ''))));
        if ($leadNames->duplicates()->isNotEmpty()) {
            return back()->withInput()->withErrors(['travellers' => 'Each traveller must have a different name.']);
        }

        $primaryAirlineCode = $response['tripInfos'][0]['sI'][0]['fD']['aI']['code'] ?? null;
        $addonTrips = $this->addonOptions($response);

        // Never trust a client-submitted add-on price. Seat prices come from
        // a fresh seat map; baggage/meal/fast-forward prices from Review's
        // ssrInfo in the server-side draft. TripJack's Book strictly
        // requires paymentInfos.amount to equal TF + every selected add-on
        // (confirmed live — a mismatch 400s), and that amount is read from
        // the pricing breakdown below.
        $seatAmounts = []; // [segmentId][code] => amount, bookable seats only
        $wantsSeats = collect($validated['travellers'])->contains(fn ($t) => array_filter($t['seats'] ?? []) !== []);
        $needsSeats = collect($addonTrips)->contains('seatMandatory', true);
        // Doc (Seat Map): "Only call this when conditions.isa = true".
        if (($conditions['isa'] ?? false) && ($wantsSeats || $needsSeats)) {
            try {
                foreach ($client->seatMap($draft['bookingId'])['tripSeatMap']['tripSeat'] ?? [] as $segId => $map) {
                    foreach ($map['sInfo'] ?? [] as $s) {
                        if (! ($s['isBooked'] ?? false) && ! empty($s['code'])) {
                            $seatAmounts[(string) $segId][$s['code']] = round((float) ($s['amount'] ?? 0), 2);
                        }
                    }
                }
            } catch (TripJackException $e) {
                Log::channel('tripjack')->warning('flight_seat_revalidate_failed', ['bookingId' => $draft['bookingId'], 'message' => $e->getMessage()]);
            }
        }

        $addonErrors = [];
        $usedSeats = [];
        $addonsTotal = 0.0;
        $travellerInfo = [];
        $addonLines = []; // [travellerIdx]['seats'|'extras'][] = "DEL → BOM: 12A" — for the Step 3 table
        $addonTotals = []; // [seats|baggage|meals|fastforward] => {amount, count} — Step 3's fare summary breakdown
        foreach ($validated['travellers'] as $i => $t) {
            $pt = $i < $context['adults'] ? 'ADULT' : ($i < $context['adults'] + $context['children'] ? 'CHILD' : 'INFANT');
            $paxLabel = ucfirst(strtolower($pt)).' '.($i + 1);
            $ssr = [];

            // Infants sit on an adult's lap — no seat or add-ons of their own.
            if ($pt !== 'INFANT') {
                foreach ($addonTrips as $trip) {
                    foreach ($trip['segments'] as $seg) {
                        $segId = $seg['id'];

                        $seatCode = $t['seats'][$segId] ?? null;
                        if ($seatCode) {
                            if (! isset($seatAmounts[$segId][$seatCode]) || in_array($seatCode, $usedSeats[$segId] ?? [], true)) {
                                $addonErrors[] = "Seat {$seatCode} on {$seg['label']} is no longer available for {$paxLabel}. Please choose another seat.";
                            } else {
                                $usedSeats[$segId][] = $seatCode;
                                $addonsTotal += $seatAmounts[$segId][$seatCode];
                                $ssr['ssrSeatInfos'][] = ['key' => $segId, 'code' => $seatCode];
                                $addonLines[$i]['seats'][] = "{$seg['label']}: {$seatCode}";
                                $addonTotals['seats']['amount'] = ($addonTotals['seats']['amount'] ?? 0) + $seatAmounts[$segId][$seatCode];
                                $addonTotals['seats']['count'] = ($addonTotals['seats']['count'] ?? 0) + 1;
                            }
                        } elseif ($trip['seatMandatory']) {
                            $addonErrors[] = "Please select a seat on {$seg['label']} for {$paxLabel} — the airline requires seat selection for this fare.";
                        }

                        foreach (['baggage' => 'BAGGAGE', 'meals' => 'MEAL', 'fastforward' => 'FASTFORWARD'] as $input => $type) {
                            $code = $t[$input][$segId] ?? null;
                            if (! $code) {
                                continue;
                            }
                            $option = $seg['options'][$type][$code] ?? null;
                            if (! $option) {
                                $addonErrors[] = "The selected add-on on {$seg['label']} for {$paxLabel} is no longer available. Please choose again.";

                                continue;
                            }
                            $addonsTotal += $option['amount'];
                            $ssr[self::ADDON_BOOK_FIELDS[$type]][] = ['key' => $segId, 'code' => $code];
                            $addonLines[$i]['extras'][] = "{$seg['label']}: {$option['desc']}";
                            $addonTotals[$input]['amount'] = ($addonTotals[$input]['amount'] ?? 0) + $option['amount'];
                            $addonTotals[$input]['count'] = ($addonTotals[$input]['count'] ?? 0) + 1;
                        }
                    }
                }
            }

            $travellerInfo[] = array_filter([
                'ti' => $t['title'] === 'Miss' ? 'Ms' : $t['title'],
                'pt' => $pt,
                'fN' => $t['first_name'],
                'lN' => $t['last_name'],
                'dob' => $t['dob'] ?? null,
                'pNum' => $t['passport_number'] ?? null,
                'eD' => $t['passport_expiry'] ?? null,
                'pNat' => ! empty($t['passport_nationality']) ? strtoupper($t['passport_nationality']) : null,
                'pid' => $t['passport_issue_date'] ?? null,
                'di' => ! empty($t['document_id']) ? strtoupper($t['document_id']) : null,
                'pan' => $panRequired && ! empty($t['pan']) ? strtoupper($t['pan']) : null,
                'ff' => ($primaryAirlineCode && ! empty($t['frequent_flyer'])) ? [$primaryAirlineCode => $t['frequent_flyer']] : null,
            ], fn ($v) => $v !== null) + $ssr;
        }

        if ($addonErrors) {
            return back()->withInput()->withErrors(['addons' => array_values(array_unique($addonErrors))]);
        }

        // Step 2 → Step 3: everything is valid, so keep the details for the
        // Review page (and for its Pay / Block buttons) — nothing is booked
        // or charged yet.
        if ($intent === 'review') {
            $draft['passengerReview'] = [
                'input' => $request->except(['_token', 'intent', 'from_review']),
                'travellerInfo' => $travellerInfo,
                'addonsTotal' => round($addonsTotal, 2),
                'addonLines' => $addonLines,
                'addonTotals' => $addonTotals,
            ];
            session(['flight_booking_draft' => $draft]);

            return redirect()->route('flights.confirm.show');
        }

        // Doc (Booking API, Hold): "Check conditions.isBA = true in Review
        // before attempting" — the Review page hides Block otherwise, but a
        // crafted/stale post must not reach TripJack either.
        if ($intent === 'hold' && (! ($conditions['isBA'] ?? false) || ! FlightSettings::allowHold())) {
            return back()->withInput()->withErrors(['intent' => 'This fare can’t be held — the airline requires payment to book it. Please choose Proceed to Pay.']);
        }

        // A hold blocks the PNR without any payment, so there's nothing to
        // charge paid add-ons against — free seats/meals are fine.
        if ($intent === 'hold' && $addonsTotal > 0) {
            return back()->withInput()->withErrors(['addons' => 'Paid seats, baggage or services can’t be added to a held fare. Remove them to hold, or choose Proceed to Pay.']);
        }

        $gstInfo = ! empty($validated['gst_number']) ? [
            'gstNumber' => strtoupper($validated['gst_number']),
            'registeredName' => $validated['gst_registered_name'],
        ] : null;

        // Book's contactInfo (emergency contact) — confirmed live that
        // TripJack stores {ecn, emails, contacts} exactly as sent.
        $contactInfo = $emergencyRequired ? array_filter([
            'ecn' => trim($validated['emergency_name']),
            'contacts' => [FlightBookingService::phoneFromInput($validated['emergency_dial_code'] ?? null, $validated['emergency_phone'])],
            'emails' => ! empty($validated['emergency_email']) ? [$validated['emergency_email']] : null,
        ], fn ($v) => $v !== null) : null;

        // Confirmed against a live sandbox response: the reviewed total fare
        // lives at totalPriceInfo.totalFareDetail.fC (same shape read in
        // showReview() above), not response.fD.
        $fc = $response['totalPriceInfo']['totalFareDetail']['fC'] ?? null;
        $afcTaf = $response['totalPriceInfo']['totalFareDetail']['afC']['TAF'] ?? [];
        $totalFare = (float) ($fc['TF'] ?? 0);

        // Payment sits between Review and Book, so re-check the fare with
        // TripJack before sending the guest to pay — otherwise a fare that
        // sold out or changed meanwhile is only discovered after payment.
        // (Holds are re-checked separately at confirm time.)
        if ($intent !== 'hold') {
            $fareCheck = $this->validateFareBeforePayment($client, $draft, $travellerInfo, $validated['contact_email'], $contactPhone, $gstInfo, $addonsTotal, $contactInfo);
            if ($fareCheck !== null) {
                return $fareCheck;
            }

            // Don't take payment for a booking TripJack can't pay for.
            if (! app(FlightBookingService::class)->hasTripJackFunds($totalFare + $addonsTotal)) {
                return back()->withInput()->withErrors(['booking' => FlightBookingService::INSUFFICIENT_FUNDS_MESSAGE]);
            }
        }
        // Add-ons fold into the same markup formula as the airfare (the
        // Passenger Details page mirrors this exactly for its live total)
        // and, critically, into tripjack_total_price — Book's amount must
        // equal TF + add-ons exactly (confirmed live), and that amount is
        // read straight from this breakdown by
        // FlightBookingService::confirmAfterPayment().
        $breakdown = FlightPricingService::price($totalFare + $addonsTotal, $draft['marginRate'] ?? null);
        $customerPrice = $breakdown['customer_price'];

        $leadGuestName = trim(($validated['travellers'][0]['first_name'] ?? '').' '.($validated['travellers'][0]['last_name'] ?? ''));

        // Persisted so the Cancellation Amendment API can later scope a
        // partial cancellation to a specific trip via src/dest/departureDate
        // (confirmed live: departureDate as plain YYYY-MM-DD) — flight_route
        // is just a display string and can't be reliably parsed back.
        if ($context['tripType'] === 'multi') {
            $legs = $context['legs'] ?? [];
            $flightLegs = collect($legs)->map(fn ($leg) => ['src' => $leg['from'], 'dest' => $leg['to'], 'departureDate' => $leg['date']])->all();
            $route = collect($legs)->pluck('from')->push(end($legs)['to'] ?? '')->implode('-');
            $firstDepartDate = $legs[0]['date'] ?? $context['departDate'];
            $lastDepartDate = end($legs)['date'] ?? $context['departDate'];
        } else {
            $flightLegs = [['src' => $context['from'], 'dest' => $context['to'], 'departureDate' => $context['departDate']]];
            if ($context['tripType'] === 'return') {
                $flightLegs[] = ['src' => $context['to'], 'dest' => $context['from'], 'departureDate' => $context['returnDate']];
            }
            $route = $context['from'].'-'.$context['to'].($context['tripType'] === 'return' ? '-'.$context['from'] : '');
            $firstDepartDate = $context['departDate'];
            $lastDepartDate = $context['tripType'] === 'return' ? $context['returnDate'] : $context['departDate'];
        }

        try {
            $booking = Booking::create([
                'reference' => 'TYT'.strtoupper(\Illuminate\Support\Str::random(8)),
                'user_id' => $request->user()->id,
                'guest_email' => $validated['contact_email'],
                // Full international number ("+919876543210") — Book and
                // the other TripJack calls send it as-is (see
                // FlightBookingService::e164(), which also handles older
                // bookings stored as 10 bare digits).
                'guest_phone' => $contactPhone,
                'vertical' => 'flight',
                'flight_route' => $route,
                'flight_journey_type' => strtoupper($context['tripType'] === 'multi' ? 'MULTI_CITY' : ($context['tripType'] === 'return' ? 'RETURN' : 'ONEWAY')),
                'flight_cabin_class' => $context['cabinClass'],
                'flight_departure_date' => $firstDepartDate,
                'flight_return_date' => $context['tripType'] === 'return' ? $context['returnDate'] : null,
                'tripjack_hold_id' => $draft['bookingId'], // Review's bookingId — same column meaning as hotels
                'tripjack_gst_type' => $gstMandatory ? 'PASSTHROUGH' : null,
                'tripjack_gst_info' => $gstInfo,
                // fareExpiresAt / resultsUrl let the payment page keep the
                // fare-hold countdown and stop payment once the reviewed fare
                // has expired (Book would then fail and need a refund).
                'flight_segments_payload' => [
                    'travellerInfo' => $travellerInfo,
                    'contactInfo' => $contactInfo,
                    'fareExpiresAt' => $draft['expiresAt'] ?? null,
                    'resultsUrl' => $draft['resultsUrl'] ?? null,
                ],
                'flight_legs' => $flightLegs,
                'check_in' => $firstDepartDate, // reused generic date columns so admin/list views sort sensibly across verticals
                'check_out' => $lastDepartDate,
                'pax_adults' => $context['adults'],
                'pax_children' => $context['children'],
                'pax_infants' => $context['infants'],
                'lead_guest_name' => $leadGuestName,
                'special_requests' => $validated['special_requests'] ?? null,
                'base_amount' => $breakdown['tripjack_total_price'],
                'tax_amount' => $customerPrice - $breakdown['tripjack_total_price'],
                'total_amount' => $customerPrice,
                'tripjack_total_price' => $breakdown['tripjack_total_price'],
                'gst_slab' => $breakdown['gst_slab'],
                'margin_amount' => $breakdown['margin_amount'],
                'gst_on_margin' => $breakdown['gst_on_margin'],
                'razorpay_recovery' => $breakdown['razorpay_recovery'],
                // MF/MFT are TAF components (afC.TAF), the same place the
                // Review page reads them — fC itself never carries them.
                'tripjack_mf' => round((float) ($afcTaf['MF'] ?? $fc['MF'] ?? 0), 2),
                'tripjack_mft' => round((float) ($afcTaf['MFT'] ?? $fc['MFT'] ?? 0), 2),
                'currency' => $fc['currency'] ?? 'INR',
                'status' => 'pending_payment',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                $existing = Booking::where('tripjack_hold_id', $draft['bookingId'])->first();
                if ($existing) {
                    session()->forget('flight_booking_draft');

                    return redirect()->route('hotel.payment.show', $existing->reference);
                }
            }

            throw $e;
        }

        $this->saveTicketedTravellers($request->user(), $validated['travellers'], $primaryAirlineCode);

        // Hold (Without Payment) — only offered when Review's conditions.isBA
        // was true (flight-passengers.blade.php gates the option on that). No
        // Razorpay order at all yet: TripJack blocks the PNR for free, and
        // payment is only collected later via confirmHold() below.
        if ($intent === 'hold') {
            session()->forget(['flight_booking_draft', 'flight_search_context']);

            return $this->submitHold($booking, $client);
        }

        $order = $razorpay->createOrder((float) $booking->total_amount, $booking->reference);
        Payment::create([
            'booking_id' => $booking->id,
            'razorpay_order_id' => $order['id'],
            'amount' => $booking->total_amount,
            'currency' => $booking->currency ?? 'INR',
            'status' => 'created',
        ]);

        session()->forget(['flight_booking_draft', 'flight_search_context']);

        return redirect()->route('hotel.payment.show', $booking->reference);
    }

    /**
     * "Add this to My Travellers List" — saves ticked travellers to the
     * guest's profile, skipping anyone already saved under the same name.
     */
    private function saveTicketedTravellers(\App\Models\User $user, array $travellers, ?string $airlineCode): void
    {
        $existing = $user->savedTravellers()->get()
            ->map(fn ($t) => strtolower(trim($t->first_name.' '.$t->last_name)))
            ->all();

        foreach ($travellers as $t) {
            $name = strtolower(trim($t['first_name'].' '.$t['last_name']));
            if (empty($t['save']) || in_array($name, $existing, true)) {
                continue;
            }
            $user->savedTravellers()->create([
                'first_name' => $t['first_name'],
                'last_name' => $t['last_name'],
                'gender' => in_array($t['title'], ['Mr', 'Master'], true) ? 'Male' : 'Female',
                'dob' => $t['dob'] ?? null,
                'passport_number' => $t['passport_number'] ?? null,
                'passport_expiry' => $t['passport_expiry'] ?? null,
                'frequent_flyer_airline' => ! empty($t['frequent_flyer']) ? $airlineCode : null,
                'frequent_flyer_number' => $t['frequent_flyer'] ?? null,
            ]);
            $existing[] = $name;
        }
    }

    protected function submitHold(Booking $booking, TripJackFlightClient $client)
    {
        $segments = $booking->flight_segments_payload ?? [];

        try {
            $response = $client->book(
                $booking->tripjack_hold_id,
                null, // omit paymentInfos — this is the Hold, not Instant Book
                $segments['travellerInfo'] ?? [],
                [$booking->guest_email],
                [FlightBookingService::e164($booking->guest_phone)],
                gstInfo: $booking->tripjack_gst_info,
                contactInfo: $segments['contactInfo'] ?? null,
            );
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode);
            Log::channel('tripjack')->{$described['logLevel'] === 'critical' ? 'critical' : 'warning'}('flight_hold_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
            $booking->delete();

            return redirect()->route('flights.search')->with('booking_error', $described['message']);
        }

        if (! ($response['status']['success'] ?? false) || empty($response['bookingId'])) {
            $errorCode = TripJackFlightErrorCatalog::codeFromResponse($response);
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'This fare could not be held. Please search again.');
            Log::channel('tripjack')->warning('flight_hold_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
            $booking->delete();

            return redirect()->route('flights.search')->with('booking_error', $described['message']);
        }

        $tripjackBookingId = $response['bookingId'];
        $expiresAt = null;
        $pnr = null;

        try {
            $details = $client->bookingDetails($tripjackBookingId);
            $expiresAt = $details['itemInfos']['AIR']['timeLimit'] ?? null; // confirmed live — not in the doc's field table
            $pnr = FlightBookingService::airTravellers($details)[0]['pnrDetails'] ?? null;
            $orderStatus = $details['order']['status'] ?? null;

            // Doc booking flow: Block → Booking Details → ON_HOLD. A hold that
            // TripJack reports as failed must not be shown as held (no
            // payment was taken, so there's nothing to refund).
            if (in_array($orderStatus, ['FAILED', 'ABORTED', 'UNCONFIRMED', 'CANCELLED'], true)) {
                Log::channel('tripjack')->warning('flight_hold_not_on_hold', ['booking_id' => $booking->id, 'bookingId' => $tripjackBookingId, 'orderStatus' => $orderStatus]);
                $booking->delete();

                return redirect()->route('flights.search')->with('booking_error', 'The airline couldn’t hold this fare. Please search again or book it with payment.');
            }
            if ($orderStatus !== 'ON_HOLD') {
                // Usually still PENDING this soon after Block — the hold is
                // re-checked (Confirm Fare Before Ticket) before any payment.
                Log::channel('tripjack')->info('flight_hold_status_not_final', ['booking_id' => $booking->id, 'bookingId' => $tripjackBookingId, 'orderStatus' => $orderStatus]);
            }
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('flight_hold_details_failed', ['bookingId' => $tripjackBookingId, 'message' => $e->getMessage()]);
        }

        $booking->update([
            'tripjack_booking_id' => $tripjackBookingId,
            'tripjack_flight_pnr' => $pnr,
            'tripjack_hold_expires_at' => $expiresAt,
            'status' => 'on_hold',
        ]);

        return redirect()->route('hotel.booking.confirmation', $booking->reference);
    }

    /**
     * POST /booking/{reference}/flight/confirm-hold — guest chooses to pay
     * for a held fare. Fare-validates first (catches an expired hold before
     * the guest reaches the payment screen), then creates the Razorpay
     * order — Confirm-Book itself only runs after payment is captured, via
     * FlightBookingService::confirmHoldAfterPayment().
     */
    public function confirmHold(string $reference, RazorpayService $razorpay, FlightBookingService $flights)
    {
        $booking = $this->guardHoldBooking($reference);

        $validation = $flights->validateHoldFare($booking);
        if (! $validation['ok']) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference)->with('booking_error', $validation['message']);
        }

        if (! $flights->hasTripJackFunds((float) $booking->tripjack_total_price)) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference)->with('booking_error', FlightBookingService::INSUFFICIENT_FUNDS_MESSAGE);
        }

        $payment = $booking->payments()->where('purpose', 'flight_confirm_book')->where('status', 'created')->latest()->first();
        if (! $payment) {
            $order = $razorpay->createOrder((float) $booking->total_amount, $booking->reference.'-HOLD-'.now()->timestamp);
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'razorpay_order_id' => $order['id'],
                'amount' => $booking->total_amount,
                'currency' => $booking->currency ?? 'INR',
                'status' => 'created',
                'purpose' => 'flight_confirm_book',
            ]);
        }

        return view('pages.flight-payment', [
            'booking' => $booking,
            'payment' => $payment,
            'razorpayKeyId' => config('services.razorpay.key_id'),
        ]);
    }

    /**
     * POST /booking/{reference}/flight/release-hold — guest decides not to
     * proceed. No refund needed (Hold never captured any payment).
     */
    public function releaseHold(string $reference, FlightBookingService $flights)
    {
        $booking = $this->guardHoldBooking($reference);
        $flights->releaseHold($booking);

        return redirect()->route('hotel.booking.confirmation', $booking->reference);
    }

    protected function guardHoldBooking(string $reference): Booking
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->vertical !== 'flight' || $booking->status !== 'on_hold') {
            abort(404);
        }

        return $booking;
    }

    /**
     * POST /booking/{reference}/flight/cancel-quote — the cancel page's live
     * "what will I get back" estimate for the scope the guest has picked
     * (TripJack's optional Get Amendment Charges step). Nothing is
     * cancelled here.
     */
    public function cancellationQuote(Request $request, string $reference, FlightBookingService $flights)
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
        if ($booking->vertical !== 'flight' || $booking->status !== 'confirmed' || ! $booking->tripjack_booking_id) {
            return response()->json(['quote' => null], 422);
        }

        $scope = $flights->cancellationScope(
            $booking,
            (string) $request->input('cancel_scope', 'full'),
            (array) $request->input('leg_indexes', []),
            (array) $request->input('traveller_indexes', []),
        );
        if (! $scope) {
            return response()->json(['quote' => null, 'empty' => true]);
        }

        return response()->json(['quote' => $flights->cancellationQuote($booking, $scope['trips'], $scope['paxCounts'])]);
    }

    /**
     * GET /booking/{reference}/flight/fare-rules — lets a guest check the
     * cancellation/date-change policy on an already-confirmed booking, not
     * just once at Review (before they'd paid). Uses flowType=BOOKING_DETAIL
     * — confirmed live against a real sandbox booking.
     */
    public function showFareRules(string $reference, TripJackFlightClient $client)
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->vertical !== 'flight' || ! $booking->tripjack_booking_id) {
            abort(404);
        }

        $fareRules = null;
        $fareRuleError = null;

        try {
            $response = $client->fareRule('BOOKING_DETAIL', $booking->tripjack_booking_id);
            $fareRules = $response['fareRule'] ?? null;
        } catch (TripJackException $e) {
            $fareRuleError = 'Fare rules aren\'t available for this booking right now. Please try again shortly.';
            Log::channel('tripjack')->info('flight_booking_farerule_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);
        }

        return view('pages.flight-fare-rules', compact('booking', 'fareRules', 'fareRuleError'));
    }

    /**
     * GET /flights/fare-rule (AJAX, JSON) — lazy per-flight-row fare rule
     * lookup on the results page. Only called when a guest actually opens
     * that tab for that specific flight, not pre-fetched for every result —
     * a results page can show 50-100+ flights, so eagerly calling Fare Rule
     * for all of them would be a large multiple of TripJack calls per page
     * load for policy text most guests never look at.
     */
    public function fareRuleAjax(Request $request, TripJackFlightClient $client)
    {
        $validated = $request->validate(['price_id' => 'required|string']);

        try {
            $response = $client->fareRule('SEARCH', $validated['price_id']);
        } catch (TripJackException $e) {
            return response()->json(['success' => false, 'message' => 'Fare rules aren\'t available for this fare right now.'], 200);
        }

        return response()->json(['success' => true, 'fareRule' => $response['fareRule'] ?? null]);
    }
}
