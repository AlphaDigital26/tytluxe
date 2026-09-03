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
    protected string $nationalityBaseUrl;
    protected int $timeout;
    protected int $connectTimeout;
    protected int $retryTimes;
    protected int $retrySleepMs;

    public function __construct()
    {
        $this->apiKey = (string) config('services.tripjack.api_key');
        $this->hmsBaseUrl = rtrim((string) config('services.tripjack.hms_base_url'), '/');
        $this->bookerBaseUrl = rtrim((string) config('services.tripjack.booker_base_url'), '/');
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
            'timeoutMs' => $this->timeout * 1000,
            'hids' => $hids,
        ]);
    }

    /**
     * Nationalities — GET /nationality-info (separate "nationality" host per docs).
     *
     * Uses a short dedicated timeout (4 s, no retries) because this is a
     * best-effort call that populates a dropdown — it must never block the page.
     */
    public function nationalityInfo(): array
    {
        $url = $this->nationalityBaseUrl.'/nationality-info';
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
        $this->log('GET', $url, $response->status(), $startedAt, [], $responseBody);

        if ($response->failed()) {
            throw new TripJackApiException(
                "TripJack nationality API error: ".$response->body(),
                status: $response->status(),
                errorCode: null,
                body: $responseBody,
            );
        }

        return $responseBody;
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
            'timeoutMs' => $this->timeout * 1000,
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
     * Book API — POST /hotel/book (booker host). Omit paymentInfos for a HOLD
     * booking (reserves without payment, until the review response's ddt) —
     * this is what we use until Phase 8 wires Razorpay. Response only confirms
     * the request was received; poll bookingDetails() for terminal status.
     *
     * @param  array<int, array{travellerInfo: array<int, array{ti:string, pt:string, fN:string, lN:string, pan?:string, pNum?:string}>}>  $roomTravellerInfo
     */
    public function book(
        string $bookingId,
        array $roomTravellerInfo,
        array $emails,
        array $contacts,
        array $dialCodes,
        ?float $amount = null,
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

        return $this->request('booker', 'POST', '/hotel/book', $payload);
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
     * Static Detail — POST /hotel/static-detail. Catalogue metadata only
     * (name, images, star rating, address) — never pricing/availability.
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
            'nationality' => $this->nationalityBaseUrl,
            default => $this->hmsBaseUrl,
        };
        $url = $baseUrl.$path;
        $startedAt = microtime(true);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'apikey' => $this->apiKey,
            ])
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->retry($this->retryTimes, $this->retrySleepMs, function ($exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof \Illuminate\Http\Client\RequestException
                            && $exception->response->status() >= 500);
                }, throw: false)
                ->send($method, $url, [$mode === 'query' ? 'query' : 'json' => $payload]);
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

        if ($response->failed()) {
            $errorCode = $responseBody['error']['code'] ?? $responseBody['errors'][0]['errCode'] ?? null;
            $message = $responseBody['error']['message'] ?? $responseBody['errors'][0]['message'] ?? $response->body();

            throw new TripJackApiException(
                "TripJack API error on {$path}: {$message}",
                status: $response->status(),
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
