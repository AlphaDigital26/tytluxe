<?php

namespace App\Services\TripJack;

use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackAuthException;
use App\Services\TripJack\Exceptions\TripJackTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TripJackClient
{
    protected string $apiKey;
    protected string $hmsBaseUrl;
    protected string $bookerBaseUrl;
    protected string $bookerV1BaseUrl;
    protected string $nationalityBaseUrl;
    protected int $timeout;
    protected int $connectTimeout;
    protected int $retryTimes;
    protected int $retrySleepMs;

    /** TripJack's reason for the first failed batch of the last listingBatches() call. */
    public ?array $lastBatchError = null;

    public function __construct()
    {
        $this->apiKey = (string) config('services.tripjack.api_key');
        $this->hmsBaseUrl = rtrim((string) config('services.tripjack.hms_base_url'), '/');
        $this->bookerBaseUrl = rtrim((string) config('services.tripjack.booker_base_url'), '/');
        $this->bookerV1BaseUrl = rtrim((string) config('services.tripjack.booker_v1_base_url'), '/');
        $this->nationalityBaseUrl = rtrim((string) config('services.tripjack.nationality_base_url'), '/');
        $this->timeout = (int) config('services.tripjack.timeout');
        $this->connectTimeout = (int) config('services.tripjack.connect_timeout');
        $this->retryTimes = (int) config('services.tripjack.retry_times');
        $this->retrySleepMs = (int) config('services.tripjack.retry_sleep_ms');
    }

    public static function newCorrelationId(): string
    {
        return (string) Str::ulid();
    }

    /**
     * timeoutMs for Listing/Detail: a few seconds under our own HTTP timeout,
     * so TripJack answers (with whatever it found) before our connection
     * gives up — equal values raced, and a slow batch was lost entirely.
     */
    protected function searchTimeoutMs(): int
    {
        return max(5, $this->timeout - 5) * 1000;
    }

    /**
     * Listing API — POST /hotel/listing (hms host).
     *
     * @param  array<int, array{adults:int, children?:int, childAge?:int[]}>  $rooms
     * @param  int[]  $hids
     */
    public function listing(
        string $checkIn,
        string $checkOut,
        array $rooms,
        array $hids,
        string $correlationId,
        string $currency = 'INR',
        string $nationality = '106',
    ): array {
        return $this->request('hms', 'POST', '/hotel/listing', [
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'rooms' => $rooms,
            'currency' => $currency,
            'correlationId' => $correlationId,
            'nationality' => $nationality,
            'timeoutMs' => $this->searchTimeoutMs(),
            'hids' => $hids,
        ]);
    }

    /**
     * Listing API for many hid batches at once, sent concurrently. A failed batch
     * comes back as null instead of throwing, so one bad batch can't sink a
     * whole-city search.
     *
     * @param  array<int, int[]>  $hidBatches  each at most 100 hids
     * @param  array<int, array{adults:int, children?:int, childAge?:int[]}>  $rooms
     * @return array<int, array|null>
     */
    public function listingBatches(
        array $hidBatches,
        string $checkIn,
        string $checkOut,
        array $rooms,
        string $correlationId,
        string $currency = 'INR',
        string $nationality = '106',
        int $concurrency = 10,
    ): array {
        $url = $this->hmsBaseUrl.'/hotel/listing';
        $this->lastBatchError = null;
        $payloads = array_map(fn (array $hids) => [
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'rooms' => $rooms,
            'currency' => $currency,
            'correlationId' => $correlationId,
            'nationality' => $nationality,
            'timeoutMs' => $this->searchTimeoutMs(),
            'hids' => array_values($hids),
        ], array_values($hidBatches));

        $startedAt = microtime(true);
        $responses = Http::pool(fn (\Illuminate\Http\Client\Pool $pool) => array_map(
            fn (array $payload) => $pool->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'apikey' => $this->apiKey,
            ])->timeout($this->timeout)->connectTimeout($this->connectTimeout)->post($url, $payload),
            $payloads,
        ), concurrency: $concurrency);

        $results = [];
        foreach ($payloads as $i => $payload) {
            $response = $responses[$i] ?? null;

            if (! $response instanceof \Illuminate\Http\Client\Response) {
                $this->log('POST', $url, null, $startedAt, $payload, ['exception' => $response instanceof \Throwable ? $response->getMessage() : 'no response'], true);
                $results[$i] = null;

                continue;
            }

            $body = $response->json() ?? [];
            $failed = $response->failed() || (array_key_exists('status', $body) && ! ($body['status']['success'] ?? true));
            $this->log('POST', $url, $response->status(), $startedAt, $payload, $body, $failed);
            $results[$i] = $failed ? null : $body;
            // Keep TripJack's reason for the first failed batch, so a search
            // it rejected outright (all batches 400) can say why instead of
            // looking like an outage.
            if ($failed && $this->lastBatchError === null) {
                $this->lastBatchError = [
                    'status' => (int) ($body['status']['httpStatus'] ?? $response->status()),
                    'errorCode' => $body['errors'][0]['errCode'] ?? $body['error']['code'] ?? null,
                    'message' => $body['errors'][0]['message'] ?? $body['error']['message'] ?? 'Listing request failed',
                ];
            }
        }

        return $results;
    }

    /**
     * Nationalities — GET /nationality-info (separate "nationality" host per docs).
     *
     * Uses a short dedicated timeout (4 s, no retries) because this is a
     * best-effort call that populates a dropdown — it must never block the page.
     */
    public function nationalityInfo(): array
    {
        // Lives on the main hms host, not a separate "nationality" host —
        // apitest.tripjack.com/hms/v3/nationality-info (the old configured
        // default) returns a 403 Access Denied; the real endpoint is
        // apitest-hms.tripjack.com/hms/v3/nationality-info, confirmed live.
        $url = $this->hmsBaseUrl.'/nationality-info';
        $startedAt = microtime(true);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'apikey'       => $this->apiKey,
            ])
                ->timeout(4)           // hard cap: 4 s read (never blocks the page)
                ->connectTimeout(3)    // hard cap: 3 s connect
                // no retry() — a slow nationality API must not multiply latency
                ->get($url);
        } catch (ConnectionException $e) {
            $this->log('GET', $url, null, $startedAt, [], ['exception' => $e->getMessage()]);
            throw new TripJackTimeoutException("TripJack nationality request timed out", previous: $e);
        }

        $responseBody = $response->json() ?? [];
        $businessFailed = array_key_exists('status', $responseBody) && ! ($responseBody['status']['success'] ?? true);
        $this->log('GET', $url, $response->status(), $startedAt, [], $responseBody, $response->failed() || $businessFailed);

        // TripJack sometimes signals failure via a 200 HTTP status with
        // status.success:false in the body (e.g. the wrong-host 403 this
        // replaced came back this way) — checking only the HTTP status let
        // that fail silently as an "empty list" instead of a real error.
        if ($response->failed() || $businessFailed) {
            $errorCode = $responseBody['errors'][0]['errCode'] ?? null;
            $message = $responseBody['errors'][0]['message'] ?? $response->body();

            throw new TripJackApiException(
                "TripJack nationality API error: {$message}",
                status: $responseBody['status']['httpStatus'] ?? $response->status(),
                errorCode: $errorCode,
                body: $responseBody,
            );
        }

        return $responseBody;
    }

    /**
     * Fetch Countries — GET /content/fetch-countries. Returns every distinct
     * country name TripJack has hotel data for, for country filter dropdowns
     * and as input to fetchHotelMapping()'s countryName param.
     *
     * @return array{status: array, hotelCountries: string[]}
     */
    public function fetchCountries(): array
    {
        return $this->request('hms', 'GET', '/content/fetch-countries', [], mode: 'query');
    }

    /**
     * City Region IDs — GET /content/fetch-city-regionIds (cursor pagination).
     */
    public function fetchCityRegionIds(int $limit = 2000, ?string $cursor = null): array
    {
        $query = ['limit' => $limit];
        if ($cursor) {
            $query['cursor'] = $cursor;
        }

        return $this->request('hms', 'GET', '/content/fetch-city-regionIds', $query, mode: 'query');
    }

    /**
     * Hotel ID Mapping — POST /content/fetch-hotel-mapping (page pagination).
     *
     * @param  string[]  $regionIds
     */
    public function fetchHotelMapping(?string $countryName = null, array $regionIds = [], int $page = 0, int $size = 2000): array
    {
        $payload = ['page' => $page, 'size' => $size];
        if ($regionIds) {
            $payload['regionIds'] = $regionIds;
        } elseif ($countryName) {
            $payload['countryName'] = $countryName;
        }

        return $this->request('hms', 'POST', '/content/fetch-hotel-mapping', $payload);
    }

    /**
     * Hotel Mapping Sync — POST /content/fetch-hotel-mapping-sync (page query
     * param + cursor body pagination). $type is NEW or UPDATE; DELETE goes
     * through fetchDeletedHotelMapping() instead (separate endpoint).
     */
    public function fetchHotelMappingSync(string $type, string $lastUpdateTime, ?string $cursor = null, int $page = 0): array
    {
        $payload = ['type' => $type, 'lastUpdateTime' => $lastUpdateTime];
        if ($cursor) {
            $payload['cursor'] = $cursor;
        }

        return $this->request('hms', 'POST', '/content/fetch-hotel-mapping-sync?page='.$page, $payload);
    }

    /**
     * Deleted Mapping Sync — POST /content/fetch-deleted-hotel-mapping.
     */
    public function fetchDeletedHotelMapping(string $lastUpdateTime, ?string $cursor = null, int $page = 0): array
    {
        $payload = ['type' => 'DELETE', 'lastUpdateTime' => $lastUpdateTime];
        if ($cursor) {
            $payload['cursor'] = $cursor;
        }

        return $this->request('hms', 'POST', '/content/fetch-deleted-hotel-mapping?page='.$page, $payload);
    }

    /**
     * Detail (Dynamic Pricing) API — POST /hotel/pricing. Returns all bookable
     * options for one hotel plus the reviewHash required by the Review API.
     * This — not static-detail — is the source of truth for prices, room
     * configs, meal plans, and cancellation policy.
     *
     * @param  array<int, array{adults:int, children?:int, childAge?:int[]}>  $rooms
     */
    public function pricing(
        string $hid,
        string $checkIn,
        string $checkOut,
        array $rooms,
        string $correlationId,
        string $currency = 'INR',
        string $nationality = '106',
    ): array {
        return $this->request('hms', 'POST', '/hotel/pricing', [
            'correlationId' => $correlationId,
            'hid' => $hid,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'rooms' => $rooms,
            'currency' => $currency,
            'nationality' => $nationality,
            'timeoutMs' => $this->searchTimeoutMs(),
        ]);
    }

    /**
     * Review API — POST /hotel/review. Re-validates price/availability for one
     * selected option immediately before booking, and returns the bookingId
     * that the Book API (Phase 7) consumes. Must be called right before Book —
     * never book off stale listing/pricing data.
     */
    public function review(string $correlationId, string $optionId, string $reviewHash, string $hid): array
    {
        return $this->request('hms', 'POST', '/hotel/review', [
            'correlationId' => $correlationId,
            'optionId' => $optionId,
            'reviewHash' => $reviewHash,
            'hid' => $hid,
        ]);
    }

    /**
     * Book API — POST /hotel/book (booker host). Include $amount for Instant
     * Booking (what we always use since Phase 8 — payment is captured before
     * this is ever called); omit for a HOLD booking (unused in this codebase).
     *
     * Important: passing $amount does NOT guarantee TripJack confirms
     * instantly — the response can still come back ON_HOLD (see
     * bookingDetails()'s status table), in which case confirmBook() must be
     * called separately before the option's deadline or it auto-cancels.
     * Response only confirms the request was received; poll bookingDetails()
     * for terminal status.
     *
     * @param  array<int, array{travellerInfo: array<int, array{ti:string, pt:string, fN:string, lN:string, pan?:string, pNum?:string}>}>  $roomTravellerInfo
     * @param  array{gstNumber:string, registeredName:string}|null  $gstInfo  Echoed back
     *         verbatim from the Review response's option.gstInfo when
     *         compliance.gstType is PASSTHROUGH/RESELLER — see
     *         FrontendController::submitBooking(), which captures it onto the
     *         Booking row for confirmBookingAfterPayment() to pass here.
     */
    public function book(
        string $bookingId,
        array $roomTravellerInfo,
        array $emails,
        array $contacts,
        array $dialCodes,
        ?float $amount = null,
        ?array $gstInfo = null,
    ): array {
        $payload = [
            'bookingId' => $bookingId,
            'type' => 'HOTEL',
            'roomTravellerInfo' => $roomTravellerInfo,
            'deliveryInfo' => [
                'emails' => $emails,
                'contacts' => $contacts,
                'code' => $dialCodes,
            ],
        ];

        if ($amount !== null) {
            $payload['paymentInfos'] = [['amount' => $amount]];
        }

        if ($gstInfo !== null) {
            $payload['gstInfo'] = $gstInfo;
        }

        return $this->request('booker', 'POST', '/hotel/book', $payload);
    }

    /**  
     * Confirm Hold — POST /hotel/confirm-book (booker host). Required when
     * Book's response (or a later bookingDetails() poll) shows ON_HOLD —
     * TripJack only reserved the option, it isn't actually confirmed yet.
     * $bookingId here is the Book *response's* bookingId (TJ...), not the
     * Review bookingId (TGS...) originally passed to book(). Must be called
     * before the option's deadlineDateTime or the hold auto-cancels.
     */
    public function confirmBook(string $bookingId, float $amount): array
    {
        return $this->request('booker', 'POST', '/hotel/confirm-book', [
            'bookingId' => $bookingId,
            'paymentInfos' => [['amount' => $amount]],
        ]);
    }

    /**
     * Booking Cancellation — POST /hotel/cancel-booking/{bookingId} (booker
     * host). No request body; a 200 with status.success only acknowledges
     * the cancellation request — poll bookingDetails() for the final
     * CANCELLED/CANCELLATION_PENDING status.
     */
    public function cancelBooking(string $bookingId): array
    {
        return $this->request('booker', 'POST', '/hotel/cancel-booking/'.$bookingId, [], 'none');
    }

    /** TripJack's Booking List limits: at most 7 days per call, at most 15 days back. */
    public const BOOKING_LIST_MAX_RANGE_DAYS = 7;

    public const BOOKING_LIST_MAX_LOOKBACK_DAYS = 15;

    /**
     * Booking List — POST /oms/v3/hotel/bookings. Dates are IST
     * (Y-m-d\TH:i:s, no offset); the range may span at most 7 days and start
     * at most 15 days ago — see bookingListRange().
     */
    public function bookingList(string $startDate, string $endDate): array
    {
        return $this->request('booker', 'POST', '/hotel/bookings', [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Converts a requested date range into the IST strings Booking List
     * expects, enforcing its limits. Returns [start, end] or throws
     * \InvalidArgumentException with a message fit to show the user.
     *
     * @return array{0: string, 1: string}
     */
    public static function bookingListRange(\DateTimeInterface|string $start, \DateTimeInterface|string $end): array
    {
        $tz = 'Asia/Kolkata';
        $start = \Carbon\Carbon::parse($start, $tz)->setTimezone($tz);
        $end = \Carbon\Carbon::parse($end, $tz)->setTimezone($tz);
        $nowIst = now($tz);

        if ($end->greaterThan($nowIst)) {
            $end = $nowIst->copy();
        }
        if ($start->greaterThan($end)) {
            throw new \InvalidArgumentException('Start date must be before end date.');
        }
        if ($start->lessThan($nowIst->copy()->subDays(self::BOOKING_LIST_MAX_LOOKBACK_DAYS)->startOfDay())) {
            throw new \InvalidArgumentException('TripJack only lists bookings from the last '.self::BOOKING_LIST_MAX_LOOKBACK_DAYS.' days.');
        }
        if ($start->diffInSeconds($end) > self::BOOKING_LIST_MAX_RANGE_DAYS * 86400) {
            throw new \InvalidArgumentException('TripJack allows at most '.self::BOOKING_LIST_MAX_RANGE_DAYS.' days per request.');
        }

        return [$start->format('Y-m-d\TH:i:s'), $end->format('Y-m-d\TH:i:s')];
    }

    /**
     * Booking Details — POST /hotel/booking-details (booker host). Poll this
     * after Book to confirm terminal status (Book's own response only
     * confirms the request was received, not that it succeeded).
     */
    public function bookingDetails(string $bookingId): array
    {
        return $this->request('booker', 'POST', '/hotel/booking-details', ['bookingId' => $bookingId]);
    }

    /**
     * Hotel Static Content — POST /content/fetch-hotel-content. Catalogue
     * metadata only (name, images, star rating, address, amenities, rooms) —
     * never pricing/availability. Up to 100 hotel IDs per call; every sync
     * path in this codebase batches through this rather than fetching one
     * hotel at a time.
     *
     * @param  string[]  $hotelIds  Max 100 per call — TripJack returns a 400
     *                              ("Max hotel ids size...should be 100") above that.
     * @return array{status: array, hotels: array<int, array>}
     */
    public function fetchHotelContent(array $hotelIds): array
    {
        return $this->request('hms', 'POST', '/content/fetch-hotel-content', ['hotelIds' => array_values($hotelIds)]);
    }

    /**
     * Static Detail API — POST /hotel/static-detail. Single-hotel content
     * lookup, richer than fetchHotelContent() for room data specifically:
     * some properties return zero rooms via the bulk content endpoint but
     * do have full per-room data (images, bed_config, occupancy, amenities)
     * here, keyed by the same room id used in Pricing's roomInfo[].id.
     *
     * Deliberately NOT used for bulk/whole-city syncing — one call per
     * hotel is what triggered TripJack's rate limiting during a full
     * resync in the past (see TripJackHotelSync::resyncAll()'s chunked
     * fetchHotelContent() comment). Use only as a targeted, per-hotel
     * fallback when bulk content sync left a hotel with no room images.
     */
    public function staticDetail(string $hid): array
    {
        return $this->request('hms', 'POST', '/hotel/static-detail', ['hid' => $hid]);
    }

    /**
     * Low-level request wrapper: auth headers, timeouts, retry-on-5xx/connection
     * error, structured logging, and typed exceptions on failure.
     */
    protected function request(string $host, string $method, string $path, array $payload = [], string $mode = 'json'): array
    {
        $baseUrl = match ($host) {
            'booker' => $this->bookerBaseUrl,
            'booker_v1' => $this->bookerV1BaseUrl,
            'nationality' => $this->nationalityBaseUrl,
            default => $this->hmsBaseUrl,
        };
        $url = $baseUrl.$path;
        $startedAt = microtime(true);

        // Only reads are retried: a timed-out Book/confirm-book/cancel may
        // still have gone through, and repeating it could double-book or
        // double-deduct the wallet.
        $isWrite = (bool) preg_match('#^/hotel/(book|confirm-book|cancel-booking)(/|$)#', $path);
        $tries = $isWrite ? 1 : max(1, $this->retryTimes);

        // Cancellation must be sent with no body at all (not even "[]").
        $options = $mode === 'none' ? [] : [$mode === 'query' ? 'query' : 'json' => $payload];

        try {
            // No Content-Type on a bodyless call: TripJack's cancel-booking
            // answers 403 when the header is present (confirmed on sandbox).
            $response = Http::withHeaders(array_filter([
                'Content-Type' => $mode === 'none' ? null : 'application/json',
                'Accept' => 'application/json',
                'apikey' => $this->apiKey,
            ]))
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->retry($tries, function (int $attempt, \Exception $exception) {
                    // Rate-limit responses (429) tell us exactly how long to wait via
                    // Retry-After — honor it instead of guessing.
                    if ($exception instanceof \Illuminate\Http\Client\RequestException
                        && $exception->response->status() === 429) {
                        $retryAfter = $exception->response->header('Retry-After');
                        if (is_numeric($retryAfter)) {
                            return ((int) $retryAfter) * 1000;
                        }
                    }

                    // Otherwise: exponential backoff per TripJack's documented
                    // guidance for 5xx/connection failures — 1s, 2s, 4s.
                    return 1000 * (2 ** ($attempt - 1));
                }, function ($exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof \Illuminate\Http\Client\RequestException
                            && ($exception->response->status() >= 500 || $exception->response->status() === 429));
                }, throw: false)
                ->send($method, $url, $options);
        } catch (ConnectionException $e) {
            $this->log($method, $url, null, $startedAt, $payload, ['exception' => $e->getMessage()]);
            throw new TripJackTimeoutException("TripJack request timed out: {$path}", previous: $e);
        }

        $responseBody = $response->json() ?? [];
        $businessFailed = array_key_exists('status', $responseBody) && ! ($responseBody['status']['success'] ?? true);
        $this->log($method, $url, $response->status(), $startedAt, $payload, $responseBody, $response->failed() || $businessFailed);

        if ($response->status() === 401 || $response->status() === 403) {
            throw new TripJackAuthException("TripJack auth failed ({$response->status()}) on {$path}: ".$response->body());
        }

        // TripJack sometimes signals a genuine failure with a 200 HTTP status
        // and status.success:false in the body (confirmed on /hotel/pricing
        // and /nationality-info) — checking only the transport-level status
        // let that pass through silently as if it were a normal empty/valid
        // response, instead of surfacing via TripJackErrorCatalog like every
        // other failure does.
        if ($response->failed() || $businessFailed) {
            $errorCode = $responseBody['error']['code'] ?? $responseBody['errors'][0]['errCode'] ?? null;
            $message = $responseBody['error']['message'] ?? $responseBody['errors'][0]['message'] ?? $response->body();

            throw new TripJackApiException(
                "TripJack API error on {$path}: {$message}",
                status: $responseBody['status']['httpStatus'] ?? $response->status(),
                errorCode: $errorCode,
                body: $responseBody,
            );
        }

        return $responseBody;
    }

    protected function log(string $method, string $url, ?int $status, float $startedAt, array $requestPayload, array $responseBody, bool $includeFullResponse = false): void
    {
        Log::channel('tripjack')->info('tripjack_request', [
            'method' => $method,
            'url' => $url,
            'status' => $status,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'correlationId' => $requestPayload['correlationId'] ?? null,
            'request' => $requestPayload,
            // Full body only on failure (for diagnostics); success responses can nest
            // deeply enough to hit Monolog's normalization depth limit, so just summarize.
            'response' => $includeFullResponse ? $responseBody : [
                'status' => $responseBody['status'] ?? null,
                'totalResults' => $responseBody['totalResults'] ?? null,
                'keys' => array_keys($responseBody),
            ],
        ]);
    }
}
