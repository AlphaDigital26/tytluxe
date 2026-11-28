<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * TripJack UAT certification logs in the exact layout of TripJack's own
 * samples. A test case is started with `php artisan uat:start`; every
 * TripJack call the website makes while it is open is saved into that
 * case's folder as the raw request body and the raw response:
 *
 *   storage/uat/Tripjack API logs/oneway/DEL-BOM-1A-DIRECT/SearchRequest.json
 *   storage/uat/HOTEL API TEST CASES/Test Case 2/TJ test Hotel Book Response.json
 *
 * Never active in production. A write failure never breaks the booking.
 */
class UatRecorder
{
    public const FLIGHT_ROOT = 'Tripjack API logs';

    public const HOTEL_ROOT = 'HOTEL API TEST CASES';

    public const CAB_ROOT = 'CAB API TEST CASES';

    public static function root(): string
    {
        return config('services.tripjack.uat_path') ?: storage_path('uat');
    }

    protected static function stateFile(): string
    {
        return self::root().'/active-case.json';
    }

    /** @return array{vertical: string, folder: string, cancelled?: bool}|null */
    public static function active(): ?array
    {
        if (config('services.tripjack.env') === 'production' || ! is_file(self::stateFile())) {
            return null;
        }

        return json_decode((string) file_get_contents(self::stateFile()), true) ?: null;
    }

    public static function start(string $vertical, string $folder): string
    {
        $dir = self::root().'/'.$folder;
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents(self::stateFile(), json_encode(['vertical' => $vertical, 'folder' => $folder]));
        foreach (glob(self::batchesDir().'/*.json') ?: [] as $old) {
            @unlink($old);
        }

        return $dir;
    }

    /**
     * Every hotel search batch of the open case, kept outside the test-case
     * folders (so never zipped) until the priced hotel shows which one the
     * Search files must be.
     */
    protected static function batchesDir(): string
    {
        return self::root().'/.search-batches';
    }

    public static function stop(): void
    {
        @unlink(self::stateFile());
    }

    /**
     * Saves one TripJack call into the open test case, if one is open for
     * this vertical.
     */
    public static function record(string $vertical, string $path, array $requestBody, ?string $rawResponse): void
    {
        $case = self::active();
        if (! $case || $case['vertical'] !== $vertical) {
            return;
        }
        // Only what the tester does on the website: background jobs (status
        // checks on older bookings) would mix other bookings into this case.
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        try {
            $names = match ($vertical) {
                'flight' => self::flightNames($path),
                'cab' => self::cabNames($path, $requestBody),
                default => self::hotelNames($path, $case, $requestBody),
            };
            if (! $names) {
                return; // a call TripJack's samples don't include
            }
            [$request, $response] = $names;
            $dir = self::root().'/'.$case['folder'];

            // Cabs look up two places (pickup, then drop): keep both, numbered.
            if ($vertical === 'cab' && str_contains($path, '/get-lat-long')) {
                for ($n = 2; is_file("$dir/$response"); $n++) {
                    [$request, $response] = ["LatLong{$n}Request.json", "LatLong{$n}Response.json"];
                }
            }

            // Hotel search runs in parallel batches: keep the first batch that found hotels.
            if ($vertical === 'hotel' && str_contains($path, '/hotel/listing')
                && (is_file("$dir/$response") || empty(json_decode((string) $rawResponse, true)['hotels'] ?? []))) {
                return;
            }

            $json = fn ($data) => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // Hotel search runs in parallel batches of up to 100 hotels. Each
            // batch that found hotels is kept aside; the Search files start as
            // the first one and are replaced by the batch holding the hotel
            // that gets priced (TripJack checks the priced hid is in both).
            if ($vertical === 'hotel' && str_contains($path, '/hotel/listing')) {
                $found = json_decode((string) $rawResponse, true);
                if (empty($found['hotels'] ?? [])) {
                    return;
                }
                if (! is_dir(self::batchesDir())) {
                    mkdir(self::batchesDir(), 0775, true);
                }
                file_put_contents(self::batchesDir().'/'.sprintf('%.6f', microtime(true)).'-'.bin2hex(random_bytes(3)).'.json',
                    json_encode(['request' => $requestBody, 'response' => $found]));
                if (is_file("$dir/$response")) {
                    return;
                }
            }
            file_put_contents("$dir/$request", $json($requestBody === [] ? new \stdClass : $requestBody));
            $decoded = json_decode((string) $rawResponse, true);
            file_put_contents("$dir/$response", $decoded !== null ? $json($decoded) : (string) $rawResponse);

            if ($vertical === 'hotel' && str_contains($path, '/hotel/pricing') && filled($requestBody['hid'] ?? null)) {
                self::useSearchBatchWith((string) $requestBody['hid'], $dir, $json);
            }

            // The review response carries the cancellation policy TripJack asks to see on its own.
            if ($vertical === 'hotel' && str_contains($path, '/hotel/review') && is_array($decoded)) {
                // v3: option.cancellation {isRefundable, penalties}; older shapes used "cnp".
                $policy = $decoded['option']['cancellation'] ?? self::findKey($decoded, 'cancellation') ?? self::findKey($decoded, 'cnp');
                file_put_contents("$dir/TJ test Hotel cancellation policy Request.json", $json($requestBody));
                file_put_contents("$dir/TJ test Hotel cancellation policy Response.json", $json($policy ?? new \stdClass));
            }

            if ($vertical === 'hotel' && str_contains($path, '/hotel/cancel-booking')) {
                file_put_contents(self::stateFile(), json_encode($case + ['cancelled' => true]));
            }
        } catch (\Throwable $e) {
            Log::channel('tripjack')->warning('uat_log_write_failed', ['message' => $e->getMessage()]);
        }
    }

    /** The newest search batch that holds this hotel becomes the case's Search files. */
    protected static function useSearchBatchWith(string $hid, string $dir, \Closure $json): void
    {
        $files = glob(self::batchesDir().'/*.json') ?: [];
        rsort($files); // newest search first
        foreach ($files as $file) {
            $batch = json_decode((string) file_get_contents($file), true);
            $ids = array_map('strval', $batch['request']['hids'] ?? []);
            $returned = array_map(fn ($h) => (string) ($h['hotelId'] ?? $h['id'] ?? $h['hid'] ?? ''), $batch['response']['hotels'] ?? []);
            if (in_array($hid, $ids, true) && in_array($hid, $returned, true)) {
                file_put_contents("$dir/TJ test Hotel Search Request.json", $json($batch['request']));
                file_put_contents("$dir/TJ test Hotel Search Response.json", $json($batch['response']));

                return;
            }
        }
    }

    /**
     * Hotel Test Case 14 ("cancel all test bookings") is one file in
     * TripJack's sample, cleanup_summary.json: every booking cancelled while
     * the case is open, added to the list rather than overwritten. Other
     * calls (status checks) aren't part of it.
     */
    protected static function recordCleanup(string $path, ?string $rawResponse): void
    {
        if (! preg_match('#/hotel/cancel-booking/([^/?]+)#', $path, $m)) {
            return;
        }
        $file = self::root().'/'.self::HOTEL_ROOT.'/Test Case 14/cleanup_summary.json';
        $summary = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
        $details = collect($summary['details'] ?? [])->reject(fn ($d) => ($d['bookingId'] ?? null) === $m[1])->values();
        $decoded = json_decode((string) $rawResponse, true);
        $details->push(['bookingId' => $m[1], 'response' => $decoded ?? $rawResponse]);

        file_put_contents($file, json_encode([
            'cancelled' => $details->pluck('bookingId')->all(),
            'count' => $details->count(),
            'details' => $details->all(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /** @return array{0: string, 1: string}|null */
    protected static function flightNames(string $path): ?array
    {
        $base = match (true) {
            str_contains($path, '/air-search-all') => 'Search',
            str_ends_with($path, '/review') && ! str_contains($path, 'reissue') => 'Review',
            str_ends_with($path, '/air/book') => 'Booking',
            str_contains($path, '/booking-details') => 'BookingDetail',
            str_contains($path, '/farerule') => 'FareRule',
            str_contains($path, '/air/book/fare-validate') => 'FareValidate',
            str_ends_with($path, '/seat') => 'SeatMap',
            str_contains($path, '/ancillaries/fetch/ssr') => 'SsrSearch',
            str_contains($path, '/amendment/add/ssr') => 'AddSsr',
            str_contains($path, '/amendment-charges') => 'CancellationCharges',
            str_contains($path, '/submit-amendment') => 'Cancellation',
            str_contains($path, '/amendment-details') => 'CancellationDetail',
            default => null,
        };

        return $base ? [$base.'Request.json', $base.'Response.json'] : null;
    }

    /** @return array{0: string, 1: string}|null */
    protected static function hotelNames(string $path, array $case, array $requestBody): ?array
    {
        $base = match (true) {
            str_contains($path, '/hotel/listing') => 'TJ test Hotel Search',
            str_contains($path, '/hotel/pricing') => 'TJ test hotelDetail-search',
            str_contains($path, '/hotel/review') => 'TJ test Hotel review',
            str_contains($path, '/hotel/confirm-book') => 'TJ test Hotel Confirm Hold Booking',
            str_ends_with($path, '/hotel/book') => self::isHold($requestBody) ? 'TJ test Hotel Hold' : 'TJ test Hotel Book',
            str_contains($path, '/hotel/cancel-booking') => 'TJ Test Hotel Cancel',
            str_contains($path, '/hotel/booking-details') => ! empty($case['cancelled']) ? 'TJ test hotel detail after cancel' : 'TJ test hotel booking detail',
            default => null,
        };

        return $base ? [$base.' Request.json', $base.' Response.json'] : null;
    }

    /**
     * Cab calls, one request and one response file each. The amendment
     * endpoint is the same path for the charges check (GET, "type") and the
     * cancellation itself (POST, "amendmentType").
     *
     * @return array{0: string, 1: string}|null
     */
    protected static function cabNames(string $path, array $requestBody): ?array
    {
        $base = match (true) {
            str_contains($path, '/google-places') => 'LocationSearch',
            str_contains($path, '/get-lat-long') => 'LatLong',
            str_contains($path, '/embedded/booking') => 'EmbeddedBooking',
            str_contains($path, '/quotes') => 'Quotes',
            str_contains($path, '/payment/create') => 'Payment',
            str_contains($path, '/booking/details') => 'BookingDetails',
            str_ends_with($path, '/booking') => 'Booking',
            str_contains($path, '/amendment') => isset($requestBody['amendmentType']) ? 'Cancellation' : 'CancellationCharges',
            default => null,
        };

        return $base ? [$base.'Request.json', $base.'Response.json'] : null;
    }

    /** A book call sent without payment details is TripJack's "hold". */
    protected static function isHold(array $requestBody): bool
    {
        return empty($requestBody['paymentInfos']);
    }

    protected static function findKey(array $data, string $key): mixed
    {
        foreach ($data as $k => $v) {
            if ($k === $key) {
                return $v;
            }
            if (is_array($v) && ($found = self::findKey($v, $key)) !== null) {
                return $found;
            }
        }

        return null;
    }
}
