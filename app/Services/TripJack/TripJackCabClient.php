<?php

namespace App\Services\TripJack;

use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackAuthException;
use App\Services\TripJack\Exceptions\TripJackTimeoutException;
use App\Support\UatRecorder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TripJack Cabs API v2.0 client (airport transfer, outstation and local
 * cabs). Same retry/log/exception pattern as TripJackFlightClient, against
 * the cab host. Every response is {success, message, data}; a failure is
 * {success: false, error: {code, message}}.
 *
 * Calls that book, pay or cancel are sent once only (no retry): a timeout
 * there doesn't mean TripJack didn't act on it, so resending could book or
 * charge twice. CabBookingService treats those as "outcome unknown".
 */
class TripJackCabClient
{
    protected string $apiKey;

    protected string $baseUrl;

    protected int $timeout;

    protected int $connectTimeout;

    protected int $retryTimes;

    public function __construct()
    {
        $this->apiKey = (string) config('services.tripjack.api_key');
        $this->baseUrl = rtrim((string) config('services.tripjack.cab.base_url'), '/');
        $this->timeout = (int) config('services.tripjack.timeout');
        $this->connectTimeout = (int) config('services.tripjack.connect_timeout');
        $this->retryTimes = (int) config('services.tripjack.retry_times');
    }

    /** Place suggestions as the guest types ("igi" → IGI Airport T3…). */
    public function places(string $input): array
    {
        // Typed live: a quick answer or none (no retries, short wait).
        return $this->request('POST', '/cabs/v1/google-places', ['input' => $input], retry: false, timeout: 6)['data']['places'] ?? [];
    }

    /** A chosen place's coordinates and address (city, country, PIN). */
    public function latLong(string $placeId): array
    {
        return $this->request('POST', '/cabs/v1/get-lat-long', ['placeId' => $placeId])['data'] ?? [];
    }

    /** Cab options and prices for a trip. */
    public function quotes(array $payload): array
    {
        return $this->request('POST', '/cabs/v2/quotes', $payload)['data'] ?? [];
    }

    /** Creates the booking. TripJack returns it as PAYMENT_PENDING until pay() is called. */
    public function book(array $payload): array
    {
        return $this->request('POST', '/cabs/v2/booking', $payload, retry: false)['data'] ?? [];
    }

    /** Pays TripJack for the booking from our TripJack wallet. */
    public function pay(string $bookingId, float $amount, string $payUserId): array
    {
        return $this->request('POST', '/cabs/v1/payment/create', [
            'amount' => $amount,
            'payUserId' => $payUserId,
            'paymentMedium' => 'WALLET',
            'bookingId' => $bookingId,
            'opType' => 'DEBIT',
            'product' => 'CAB',
            'transactionType' => 'PAID_FOR_ORDER',
        ], retry: false);
    }

    /** The booking's latest state: status, payment, ride status, tracking link, policies. */
    public function bookingDetails(string $bookingId): array
    {
        return $this->request('GET', '/cabs/v1/booking/details', ['bookingIds' => $bookingId])['data'][0] ?? [];
    }

    /** What cancelling right now would refund. */
    public function cancellationCharges(string $bookingId): array
    {
        return $this->request('GET', '/cabs/v1/amendment', ['bookingId' => $bookingId, 'type' => 'CANCELLATION'])['data']['amendment'] ?? [];
    }

    /** Cancels the booking (the only change TripJack allows on cabs). */
    public function cancel(string $bookingId): array
    {
        return $this->request('POST', '/cabs/v1/amendment', [
            'bookingId' => $bookingId,
            'amendmentType' => 'CANCELLATION',
        ], retry: false)['data'] ?? [];
    }

    protected function request(string $method, string $path, array $payload = [], bool $retry = true, ?int $timeout = null): array
    {
        $url = $this->baseUrl.$path;
        $startedAt = microtime(true);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                // The cab doc names the header "x-api-key"; the other TripJack
                // APIs use "apikey". Both carry the same key.
                'x-api-key' => $this->apiKey,
                'apikey' => $this->apiKey,
            ])
                ->timeout($timeout ?? $this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->retry($retry ? $this->retryTimes : 1, fn (int $attempt) => 1000 * (2 ** ($attempt - 1)), function ($exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof \Illuminate\Http\Client\RequestException
                            && ($exception->response->status() >= 500 || $exception->response->status() === 429));
                }, throw: false)
                ->send($method, $url, $method === 'GET' ? ['query' => $payload] : ['json' => $payload]);
        } catch (ConnectionException $e) {
            $this->log($method, $url, null, $startedAt, $payload, ['exception' => $e->getMessage()], true);
            throw new TripJackTimeoutException("TripJack cab request timed out: {$url}", previous: $e);
        }

        $body = $response->json() ?? [];
        $failed = $response->failed() || (array_key_exists('success', $body) && $body['success'] === false);
        $this->log($method, $url, $response->status(), $startedAt, $payload, $body, $failed);
        UatRecorder::record('cab', $path, $payload, $response->body());

        if ($response->status() === 401 || $response->status() === 403) {
            throw new TripJackAuthException("TripJack cab auth failed ({$response->status()}) on {$url}: ".$response->body());
        }

        if ($failed) {
            $message = $body['error']['message'] ?? $body['message'] ?? $response->body();

            throw new TripJackApiException(
                "TripJack cab API error on {$url}: {$message}",
                status: $response->status(),
                errorCode: isset($body['error']['code']) ? (string) $body['error']['code'] : null,
                body: $body,
            );
        }

        return $body;
    }

    protected function log(string $method, string $url, ?int $status, float $startedAt, array $request, array $response, bool $full): void
    {
        Log::channel('tripjack')->info('tripjack_cab_request', [
            'method' => $method,
            'url' => $url,
            'status' => $status,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'request' => TripJackFlightClient::maskPii($request),
            'response' => $full ? TripJackFlightClient::maskPii($response) : ['success' => $response['success'] ?? null, 'message' => $response['message'] ?? null],
        ]);
    }
}
