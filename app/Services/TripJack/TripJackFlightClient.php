<?php

namespace App\Services\TripJack;

use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackAuthException;
use App\Services\TripJack\Exceptions\TripJackTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TripJack Flights API v2.0 client. Deliberately NOT a subclass of (or
 * shared base with) TripJackClient (hotels) — that class's request()
 * pattern is already proven and carries real revenue; forking a new,
 * self-contained client with its own copy of the same retry/backoff/
 * error-envelope logic avoids any risk of regressing the hotel booking
 * path while building this out. See TripJackClient::request() for the
 * original — this mirrors it exactly, just against the flight hosts.
 *
 * Phase 1 scope only: Search, Fare Rule, Review, Instant Book,
 * Booking Details, and the Amendment (cancellation) endpoints. Seat Map,
 * SSR/ancillaries, Hold+Confirm-Book, and Reissue are Phase 2 — see the
 * flight integration plan.
 */
class TripJackFlightClient
{
    protected string $apiKey;

    protected string $fmsBaseUrl;

    protected string $omsBaseUrl;

    protected int $timeout;

    protected int $connectTimeout;

    protected int $retryTimes;

    protected int $retrySleepMs;

    public function __construct()
    {
        $this->apiKey = (string) config('services.tripjack.api_key');
        $this->fmsBaseUrl = rtrim((string) config('services.tripjack.flight.fms_base_url'), '/');
        $this->omsBaseUrl = rtrim((string) config('services.tripjack.flight.oms_base_url'), '/');
        $this->timeout = (int) config('services.tripjack.timeout');
        $this->connectTimeout = (int) config('services.tripjack.connect_timeout');
        $this->retryTimes = (int) config('services.tripjack.retry_times');
        $this->retrySleepMs = (int) config('services.tripjack.retry_sleep_ms');
    }

    /**
     * Splits an itinerary's `sI` segment list into legs (journeys). Most
     * itineraries are a single leg, but International Return and
     * International Multi-City come back under one COMBO key, with every leg
     * in the same `sI` list. Confirmed live: `sN` (segment number within its
     * leg) restarts at 0 for each leg — e.g. BLR>DOH sN=0, DOH>DXB sN=1,
     * DXB>DOH sN=0 (return starts), DOH>CMB sN=1, CMB>BLR sN=2.
     *
     * @return array<int, array<int, array>> legs, each a list of segments
     */
    public static function itineraryLegs(array $segments): array
    {
        $legs = [];
        foreach (array_values($segments) as $i => $segment) {
            if ($i === 0 || (int) ($segment['sN'] ?? 1) === 0) {
                $legs[] = [];
            }
            $legs[count($legs) - 1][] = $segment;
        }

        return $legs;
    }

    /**
     * Search API — POST /fms/v1/air-search-all. Returns cheapest fares for
     * Oneway/Return journeys. priceIds in the response are valid 15 minutes.
     * Phase 1 supports only single-route (Oneway) and 2-route (Return)
     * searches — Multi-City (2-6 legs) is Phase 2.
     *
     * @param  array{ADULT:int, CHILD?:int, INFANT?:int}  $paxInfo
     * @param  array<int, array{fromCityOrAirport: array{code:string}, toCityOrAirport: array{code:string}, travelDate:string}>  $routeInfos
     */
    public function search(
        array $paxInfo,
        array $routeInfos,
        string $cabinClass = 'ECONOMY',
        array $searchModifiers = [],
        array $preferredAirlines = [],
    ): array {
        $payload = [
            'searchQuery' => array_filter([
                'cabinClass' => $cabinClass,
                'paxInfo' => $paxInfo,
                'routeInfos' => $routeInfos,
                'searchModifiers' => $searchModifiers ?: null,
                // Confirmed live: each airline must be an object —
                // [{"code": "AI"}] returns only Air India, while a plain
                // ["AI"] is rejected with 400 "Expected BEGIN_OBJECT but was
                // STRING", which failed every Preferred Airline search.
                'preferredAirline' => $preferredAirlines
                    ? array_map(fn ($code) => ['code' => $code], array_values($preferredAirlines))
                    : null,
            ], fn ($v) => $v !== null),
        ];

        return $this->request('fms', 'POST', '/air-search-all', $payload);
    }

    /**
     * Fare Rule API — POST /fms/v2/farerule. Note: v2, not v1 like every
     * other FMS endpoint — TripJack's own doc confirms this exception.
     */
    public function fareRule(string $flowType, string $id): array
    {
        // fms base URL is .../fms/v1 — swap the version segment rather than
        // adding a second base URL just for this one endpoint.
        $url = preg_replace('#/fms/v1$#', '/fms/v2', $this->fmsBaseUrl).'/farerule';

        return $this->rawRequest('POST', $url, [
            'flowType' => $flowType,
            'id' => $id,
        ]);
    }

    /**
     * User Detail — GET /ums/v1/user-detail (POST is 405). The API account's
     * balances, nested under user.bs — see FlightBookingService::
     * tripJackBalance(). Every
     * Book/Confirm-Book/Add SSR/Auto Reissue is paid from these, so a low
     * balance makes bookings fail after the guest has paid.
     */
    public function userDetail(): array
    {
        $url = preg_replace('#/fms/v1$#', '/ums/v1', $this->fmsBaseUrl).'/user-detail';

        return $this->rawRequest('GET', $url);
    }

    /**
     * Review API — POST /fms/v1/review. Revalidates selected priceIds and
     * returns the session bookingId required for Book. $priceIds is a
     * single-element array for Oneway, two elements for Return.
     *
     * @param  string[]  $priceIds
     */
    public function review(array $priceIds): array
    {
        return $this->request('fms', 'POST', '/review', ['priceIds' => $priceIds]);
    }

    /**
     * Book API — POST /oms/v1/air/book. Pass $amount for Instant Book
     * (payment already captured via Razorpay before this is ever called,
     * mirroring the hotel flow); pass null for Hold (Without Payment) —
     * confirmed against a live sandbox call that omitting paymentInfos
     * entirely blocks the PNR without charging anything. Only attempt Hold
     * when Review's conditions.isBA is true.
     *
     * @param  array<int, array>  $travellerInfo
     */
    public function book(
        string $bookingId,
        ?float $amount,
        array $travellerInfo,
        array $emails,
        array $contacts,
        ?array $gstInfo = null,
        ?array $contactInfo = null,
    ): array {
        $payload = array_filter([
            'bookingId' => $bookingId,
            'paymentInfos' => $amount !== null ? [['amount' => $amount]] : null,
            'deliveryInfo' => [
                'emails' => $emails,
                'contacts' => $contacts,
            ],
            'contactInfo' => $contactInfo,
            'travellerInfo' => $travellerInfo,
            'gstInfo' => $gstInfo,
        ], fn ($v) => $v !== null);

        return $this->request('oms', 'POST', '/air/book', $payload, retry: false);
    }

    /**
     * Confirm Fare Before Ticket — POST /oms/v1/air/fare-validate. Call
     * before Confirm-Book to check a held fare hasn't expired/changed.
     * Response (confirmed live): {bookingId, iobfe, status} — iobfe ("is
     * old booking fare expired") true means the hold's fare is stale and a
     * new search/booking is needed instead of confirming.
     */
    public function fareValidateHold(string $bookingId): array
    {
        return $this->request('oms', 'POST', '/air/fare-validate', ['bookingId' => $bookingId]);
    }

    /**
     * Confirm-Book — POST /oms/v1/air/confirm-book. Tickets a held booking
     * after payment is captured. Response only echoes {status} (no
     * bookingId) — confirmed live; poll bookingDetails() for the resulting
     * PNR/ticket the same way Instant Book's own response is poll-only.
     */
    public function confirmBook(string $bookingId, float $amount): array
    {
        return $this->request('oms', 'POST', '/air/confirm-book', [
            'bookingId' => $bookingId,
            'paymentInfos' => [['amount' => $amount]],
        ], retry: false);
    }

    /**
     * Release PNR — POST /oms/v1/air/unhold. Releases a held (unpaid)
     * booking's PNR with the supplier. Doc: verify via bookingDetails()
     * afterwards — order.status becomes UNCONFIRMED (confirmed live).
     *
     * @param  string[]  $pnrs
     */
    public function unhold(string $bookingId, array $pnrs): array
    {
        return $this->request('oms', 'POST', '/air/unhold', [
            'bookingId' => $bookingId,
            'pnrs' => $pnrs,
        ], retry: false);
    }

    /**
     * Auto Reissue, Step 1 — POST /fms/v1/reissue/poll/searchquery-list.
     * Correction vs the doc table, confirmed live: every field (paxInfo,
     * routeInfos, pnr, oldBookingId, paxIds) is at the payload ROOT — the
     * doc's dotted paths ("searchQuery.paxInfo...") describe how the
     * RESPONSE echoes it back nested under `searchQuery`, not the request
     * shape. Sending it nested 400s with "paxInfo : may not be null".
     * Returns a requestId for reissueSearch() below.
     *
     * @param  array{ADULT:int, CHILD?:int, INFANT?:int}  $paxInfo
     * @param  array<int, array{fromCityOrAirport: array{code:string}, toCityOrAirport: array{code:string}, travelDate:string}>  $routeInfos  single element — doc: "one trip can be reissued at a time"
     * @param  string[]  $paxIds  travellerInfos[].id from bookingDetails(), as strings
     */
    public function reissueSearchQueryList(array $paxInfo, array $routeInfos, string $pnr, string $oldBookingId, array $paxIds): array
    {
        return $this->request('fms', 'POST', '/reissue/poll/searchquery-list', [
            'paxInfo' => $paxInfo,
            'routeInfos' => $routeInfos,
            'pnr' => $pnr,
            'oldBookingId' => $oldBookingId,
            'paxIds' => $paxIds,
        ]);
    }

    /**
     * Auto Reissue, Step 2 — POST /fms/v1/reissue/poll/search. Confirmed
     * live: payload is just {requestId}, no wrapper. Response matches
     * standard Search's shape (tripInfos keyed ONWARD, array of itineraries
     * each with sI + totalPriceList) — reissue-specific fare components
     * (TF, OTF, AFS, RSSR) live in the same totalPriceList[].fd[PaxType].fC
     * spot as always.
     */
    public function reissueSearch(string $requestId): array
    {
        return $this->request('fms', 'POST', '/reissue/poll/search', ['requestId' => $requestId]);
    }

    /**
     * Auto Reissue, Step 3 — POST /fms/v1/reissue/review. Confirmed live:
     * response includes a ready-to-use `travellers` array (ti/pt/fN/lN/dob/
     * id) matching autoReissueBook()'s travellerInfo shape exactly — no
     * need to reconstruct it from the original booking.
     *
     * @param  string[]  $priceIds  single element — one trip at a time
     */
    public function reissueReview(array $priceIds, string $oldBookingId): array
    {
        return $this->request('fms', 'POST', '/reissue/review', [
            'priceIds' => $priceIds,
            'oldBookingId' => $oldBookingId,
            'priceValidation' => true,
        ]);
    }

    /**
     * Auto Reissue, Step 4 — POST /oms/v1/air/amendment/auto-reissue.
     * Tickets the reissued booking — confirmed live end-to-end against a
     * real sandbox SUCCESS booking. $amount is reissueReview()'s TF.
     * $travellerInfo should come straight from reissueReview()'s
     * `travellers` array per the doc ("must match original booking data
     * from Review response").
     */
    public function autoReissueBook(string $bookingId, string $oldBookingId, float $amount, array $travellerInfo, array $emails, array $contacts, ?array $gstInfo = null): array
    {
        return $this->request('oms', 'POST', '/air/amendment/auto-reissue', array_filter([
            'bookingId' => $bookingId,
            'oldBookingId' => $oldBookingId,
            'paymentInfos' => [['amount' => $amount, 'bookingId' => $bookingId]],
            'deliveryInfo' => ['emails' => $emails, 'contacts' => $contacts],
            'travellerInfo' => $travellerInfo,
            'gstInfo' => $gstInfo,
        ], fn ($v) => $v !== null), retry: false);
    }

    /**
     * Seat Map (pre-booking) — POST /fms/v1/seat. Only meaningful when
     * Review's conditions.isa is true. Informational at this stage — actual
     * seat/meal/baggage selection is a post-booking operation, see
     * fetchAncillarySsr()/fetchAncillarySeatMap()/addSsr() below.
     */
    public function seatMap(string $bookingId): array
    {
        return $this->request('fms', 'POST', '/seat', ['bookingId' => $bookingId]);
    }

    /**
     * Fare Validate (pre-Instant-Book) — POST /oms/v1/air/book/fare-validate.
     * Optional confirmation that the fare/class is still available before an
     * Instant Book — recommended when there's a payment step between Review
     * and Book. Takes the same traveller/SSR structure as book(). Not to be
     * confused with fareValidateHold() above, a differently-pathed endpoint
     * (/air/fare-validate, no "book/") used only for the Hold flow.
     */
    public function fareValidatePreBook(array $payload): array
    {
        return $this->request('oms', 'POST', '/air/book/fare-validate', $payload);
    }

    /**
     * Fetch SSR (post-booking) — POST /fms/v1/ancillaries/fetch/ssr.
     * Booking must be in SUCCESS state. Returns available meal/baggage
     * options per segment (tripInfos[].sI[].bI.ssrInfo) — codes from here
     * are passed to addSsr() below.
     */
    public function fetchAncillarySsr(string $bookingId): array
    {
        return $this->request('fms', 'POST', '/ancillaries/fetch/ssr', ['bookingId' => $bookingId]);
    }

    /**
     * Fetch Seat Map (post-booking) — POST /fms/v1/ancillaries/fetch/seat.
     * Returns tripSeatMap keyed by the segment ID from fetchAncillarySsr().
     */
    public function fetchAncillarySeatMap(string $bookingId): array
    {
        return $this->request('fms', 'POST', '/ancillaries/fetch/seat', ['bookingId' => $bookingId]);
    }

    /**
     * Add SSR — POST /oms/v1/air/amendment/add/ssr. Applies the guest's
     * selected seat/meal/baggage codes and triggers payment for them.
     * Doc: for connecting segments, pass the baggage SBI code on every
     * segment but amount 0 on all but the first (baggage is per-journey,
     * not per-segment).
     *
     * Two corrections vs TripJack's doc table, confirmed against a live
     * sandbox call: (1) `bookingId` is required in the payload even though
     * the doc table omits it — omitting it 400s with a generic "backend
     * service" error; (2) the response key is `amendmentIds` (capital I),
     * not the doc's lowercase `amendmentids`. Poll each ID via
     * amendmentDetails() below, same async pattern as cancellation.
     *
     * @param  array<int, array{id: string, bI: array}>  $segmentInfos  sI[] payload
     */
    public function addSsr(string $bookingId, float $amount, array $segmentInfos): array
    {
        return $this->request('oms', 'POST', '/air/amendment/add/ssr', [
            'bookingId' => $bookingId,
            'paymentInfos' => [['amount' => $amount]],
            'sI' => $segmentInfos,
        ], retry: false);
    }

    /**
     * Booking Details — POST /oms/v1/booking-details. Book's own response
     * only confirms the request was received (doc: "call after 5 seconds
     * elapsed") — poll this for the terminal order.status.
     */
    public function bookingDetails(string $bookingId, bool $requirePaxPricing = false): array
    {
        return $this->request('oms', 'POST', '/booking-details', array_filter([
            'bookingId' => $bookingId,
            'requirePaxPricing' => $requirePaxPricing ?: null,
        ], fn ($v) => $v !== null));
    }

    /**
     * Get Amendment Charges — POST /oms/v1/air/amendment/amendment-charges.
     * Optional step: returns cancellation/void fees without applying
     * anything. Correction vs the doc table, confirmed live: `remarks` IS
     * required here too (the doc only lists it for Submit Amendment) —
     * omitting it 400s with "remarks : may not be null". $trips scopes the
     * charges preview to specific trips/travellers, same shape as
     * submitAmendment() below; empty means the whole booking.
     *
     * @param  array<int, array{src?: string, dest?: string, departureDate?: string, travellers?: array}>  $trips
     */
    public function amendmentCharges(string $bookingId, string $type, string $remarks, array $trips = []): array
    {
        return $this->request('oms', 'POST', '/air/amendment/amendment-charges', array_filter([
            'bookingId' => $bookingId,
            'type' => $type,
            'remarks' => $remarks,
            'trips' => $trips ?: null,
        ], fn ($v) => $v !== null));
    }

    /**
     * Submit Amendment — POST /oms/v1/air/amendment/submit-amendment.
     * Applies the cancellation/void/full-refund and returns an amendmentId
     * for status polling via amendmentDetails(). Prerequisite: booking must
     * be in SUCCESS state. $trips scopes to specific trips (src/dest/
     * departureDate) and/or specific travellers (nested trips[].travellers[]
     * with fn/ln) — confirmed live against a real sandbox booking; empty
     * means the whole booking, every pax, every trip.
     *
     * @param  array<int, array{src?: string, dest?: string, departureDate?: string, travellers?: array}>  $trips
     */
    public function submitAmendment(string $bookingId, string $remarks, string $type = 'CANCELLATION', array $trips = []): array
    {
        return $this->request('oms', 'POST', '/air/amendment/submit-amendment', array_filter([
            'bookingId' => $bookingId,
            'type' => $type,
            'remarks' => $remarks,
            'trips' => $trips ?: null,
        ], fn ($v) => $v !== null), retry: false);
    }

    /**
     * Get Amendment Details — POST /oms/v1/air/amendment/amendment-details.
     * Poll this after submitAmendment() — doc: "if REQUESTED, poll 4-5x
     * with 10-second intervals" — see PollFlightAmendmentJob, which owns
     * that polling loop rather than blocking a web request on it.
     */
    public function amendmentDetails(string $amendmentId): array
    {
        return $this->request('oms', 'POST', '/air/amendment/amendment-details', [
            'amendmentId' => $amendmentId,
        ]);
    }

    /**
     * Low-level request wrapper — auth headers, timeouts, retry-on-5xx/
     * connection error, structured logging, typed exceptions. Mirrors
     * TripJackClient::request() exactly (see that class for the full
     * rationale on each behavior) — kept as a separate copy rather than a
     * shared base class, see this class's docblock.
     *
     * $retry is false for calls that book, charge or amend (Book,
     * Confirm-Book, Unhold, Add SSR, Auto Reissue, Submit Amendment): a
     * timeout there doesn't mean TripJack didn't act on it, so resending
     * could book or charge twice (doc error 816, "Duplicate request").
     * Callers treat a timeout/5xx on those as "outcome unknown" instead —
     * see FlightBookingService::isUncertainOutcome().
     */
    protected function request(string $host, string $method, string $path, array $payload = [], bool $retry = true): array
    {
        $baseUrl = match ($host) {
            'oms' => $this->omsBaseUrl,
            default => $this->fmsBaseUrl,
        };

        return $this->rawRequest($method, $baseUrl.$path, $payload, $retry);
    }

    protected function rawRequest(string $method, string $url, array $payload = [], bool $retry = true): array
    {
        $startedAt = microtime(true);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'apikey' => $this->apiKey,
            ])
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                // Laravel's retry() takes the TOTAL attempt count — 1 = send once.
                ->retry($retry ? $this->retryTimes : 1, function (int $attempt, \Exception $exception) {
                    if ($exception instanceof \Illuminate\Http\Client\RequestException
                        && $exception->response->status() === 429) {
                        $retryAfter = $exception->response->header('Retry-After');
                        if (is_numeric($retryAfter)) {
                            return ((int) $retryAfter) * 1000;
                        }
                    }

                    return 1000 * (2 ** ($attempt - 1));
                }, function ($exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof \Illuminate\Http\Client\RequestException
                            && ($exception->response->status() >= 500 || $exception->response->status() === 429));
                }, throw: false)
                ->send($method, $url, $method === 'GET' ? ['query' => $payload] : ['json' => $payload]);
        } catch (ConnectionException $e) {
            $this->log($method, $url, null, $startedAt, $payload, ['exception' => $e->getMessage()]);
            $this->writeCertificationLog($method, $url, $payload, null, null);
            throw new TripJackTimeoutException("TripJack flight request timed out: {$url}", previous: $e);
        }

        $responseBody = $response->json() ?? [];
        $businessFailed = array_key_exists('status', $responseBody) && ! ($responseBody['status']['success'] ?? true);
        $this->log($method, $url, $response->status(), $startedAt, $payload, $responseBody, $response->failed() || $businessFailed);
        $this->writeCertificationLog($method, $url, $payload, $response->status(), $response->body());

        if ($response->status() === 401 || $response->status() === 403) {
            throw new TripJackAuthException("TripJack flight auth failed ({$response->status()}) on {$url}: ".$response->body());
        }

        if ($response->failed() || $businessFailed) {
            $errorCode = $responseBody['error']['code'] ?? $responseBody['errors'][0]['errCode'] ?? null;
            $message = $responseBody['error']['message'] ?? $responseBody['errors'][0]['message'] ?? $response->body();

            throw new TripJackApiException(
                "TripJack flight API error on {$url}: {$message}",
                status: $responseBody['status']['httpStatus'] ?? $response->status(),
                errorCode: $errorCode,
                body: $responseBody,
            );
        }

        return $responseBody;
    }

    protected function log(string $method, string $url, ?int $status, float $startedAt, array $requestPayload, array $responseBody, bool $includeFullResponse = false): void
    {
        Log::channel('tripjack')->info('tripjack_flight_request', [
            'method' => $method,
            'url' => $url,
            'status' => $status,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'request' => self::maskPii($requestPayload),
            'response' => $includeFullResponse ? self::maskPii($responseBody) : [
                'status' => $responseBody['status'] ?? null,
                'keys' => array_keys($responseBody),
            ],
        ]);
    }

    /**
     * Traveller/contact fields masked in tripjack.log (kept 14 days):
     * passport no./expiry/issue date, PAN, document ID, date of birth,
     * phone numbers, emails, GSTIN and GST contact details. Names stay —
     * they're needed to match a log line to a booking.
     */
    protected const PII_KEYS = ['pNum', 'eD', 'pid', 'pan', 'di', 'dob', 'contacts', 'emails', 'gstNumber', 'mobile', 'email', 'address'];

    public static function maskPii(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && in_array($key, self::PII_KEYS, true)) {
                $data[$key] = self::maskValue($value);
            } elseif (is_array($value)) {
                $data[$key] = self::maskPii($value);
            }
        }

        return $data;
    }

    protected static function maskValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => self::maskValue($v), $value);
        }
        if (! is_string($value) || $value === '') {
            return $value;
        }
        if (str_contains($value, '@')) {
            return '***@'.substr(strrchr($value, '@'), 1);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return '****-**-**';
        }

        // Last 4 characters only — enough to tell two travellers apart.
        return str_repeat('*', max(0, strlen($value) - 4)).substr($value, -4);
    }

    /**
     * UAT certification evidence (doc "Log Format Rules": JSON, a separate
     * file for each request and each response, response unmodified). Only
     * when TRIPJACK_CERT_LOGS is on and TRIPJACK_ENV isn't production —
     * these files are deliberately unmasked. Never lets a write failure
     * break the actual API call.
     */
    protected function writeCertificationLog(string $method, string $url, array $payload, ?int $status, ?string $rawResponse): void
    {
        if (! config('services.tripjack.flight.cert_logs') || config('services.tripjack.env') === 'production') {
            return;
        }

        try {
            $dir = rtrim((string) config('services.tripjack.flight.cert_log_path'), '/\\').'/'.now()->format('Y-m-d');
            if (! is_dir($dir)) {
                mkdir($dir, 0750, true);
            }

            // e.g. 153012_481022_oms-v1-air-book_TJS100000000001
            $endpoint = trim(preg_replace('#[^a-z0-9]+#i', '-', parse_url($url, PHP_URL_PATH) ?? ''), '-');
            $ref = preg_replace('#[^A-Za-z0-9]#', '', (string) ($payload['bookingId'] ?? $payload['amendmentId'] ?? ''));
            $base = $dir.'/'.now()->format('His_u').'_'.$endpoint.($ref !== '' ? '_'.$ref : '');

            // Doc: "Include API key with the logs" — the UAT key; these files
            // are never written in production (see the guard above).
            file_put_contents($base.'_request.json', json_encode(
                ['method' => $method, 'url' => $url, 'headers' => ['apikey' => $this->apiKey, 'Content-Type' => 'application/json'], 'body' => $payload],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));
            file_put_contents($base.'_response.json', $rawResponse ?? json_encode(['error' => 'No response — request timed out', 'httpStatus' => $status]));
        } catch (\Throwable $e) {
            Log::channel('tripjack')->warning('tripjack_cert_log_write_failed', ['message' => $e->getMessage()]);
        }
    }
}
