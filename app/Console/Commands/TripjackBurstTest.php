<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Finds TripJack's real (undocumented) rate limit: sends growing bursts of
 * simultaneous requests to a read-only endpoint and stops at the first 429,
 * reporting its Retry-After. Run it from the whitelisted server at a quiet
 * time — it uses the live API key, and every 429 counts against the site.
 *
 * Results go to the screen and to storage/app/tripjack-burst-tests/*.csv,
 * not to tripjack.log, so the test's own 429s don't set off
 * tripjack:monitor-rate-limits alerts.
 */
class TripjackBurstTest extends Command
{
    protected $signature = 'tripjack:burst-test
        {--sizes=5,10,20,30,40 : Burst sizes to try, in order}
        {--pause=60 : Seconds to wait between bursts so the limit can reset}
        {--endpoint=nationality : Read-only endpoint to hit: nationality or countries}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Send growing bursts of read-only TripJack requests to find the rate limit (stops at the first 429)';

    private const ENDPOINTS = [
        'nationality' => '/nationality-info',
        'countries' => '/content/fetch-countries',
    ];

    public function handle(): int
    {
        $endpoint = (string) $this->option('endpoint');
        if (! isset(self::ENDPOINTS[$endpoint])) {
            $this->error('Unknown --endpoint. Use: '.implode(', ', array_keys(self::ENDPOINTS)));

            return self::INVALID;
        }

        $sizes = array_values(array_filter(array_map('intval', explode(',', (string) $this->option('sizes'))), fn ($n) => $n > 0 && $n <= 200));
        if (! $sizes) {
            $this->error('--sizes must list burst sizes between 1 and 200, e.g. 5,10,20');

            return self::INVALID;
        }

        $url = rtrim((string) config('services.tripjack.hms_base_url'), '/').self::ENDPOINTS[$endpoint];
        $pause = max(0, (int) $this->option('pause'));

        $this->line("Endpoint: GET {$url}");
        $this->line('Bursts: '.implode(', ', $sizes).", {$pause}s apart (".array_sum($sizes).' requests at most)');
        if (! $this->option('force') && ! $this->confirm('This uses the live TripJack API key. Run it now, at a quiet time?')) {
            return self::SUCCESS;
        }

        $rows = [];
        $summary = [];
        $limitHit = false;

        foreach ($sizes as $i => $size) {
            if ($i > 0 && $pause > 0) {
                $this->line("Waiting {$pause}s for the limit to reset…");
                sleep($pause);
            }

            $burst = $this->burst($url, $size);
            $rows = array_merge($rows, array_map(fn ($r) => ['burst' => $size] + $r, $burst));

            $ok = count(array_filter($burst, fn ($r) => $r['status'] >= 200 && $r['status'] < 300));
            $limited = array_values(array_filter($burst, fn ($r) => $r['status'] === 429));
            $retryAfter = $limited[0]['retry_after'] ?? null;
            $summary[] = [
                $size, $ok, count($limited), $size - $ok - count($limited),
                max(array_column($burst, 'ms')).' ms',
                $retryAfter !== null && $retryAfter !== '' ? $retryAfter.'s' : '—',
            ];

            if ($limited) {
                $limitHit = true;
                break;
            }
        }

        $this->table(['Burst', 'OK', '429', 'Other errors', 'Slowest', 'Retry-After'], $summary);

        $path = 'tripjack-burst-tests/'.now()->format('Y-m-d_His').'.csv';
        Storage::put($path, $this->csv($rows));
        $this->line('Every request is saved in storage/app/'.$path);

        if ($limitHit) {
            $last = end($summary);
            $this->warn("Rate limited at a burst of {$last[0]} simultaneous requests (Retry-After {$last[5]}).");
        } else {
            $this->info('No 429 up to a burst of '.end($sizes).'. Try larger --sizes to find the ceiling.');
        }

        return self::SUCCESS;
    }

    /**
     * Sends $size requests at once, with no retries (a retry would hide the
     * 429 this is looking for).
     *
     * @return array<int, array{n: int, status: int, ms: int, retry_after: ?string, limit_headers: string}>
     */
    private function burst(string $url, int $size): array
    {
        $startedAt = microtime(true);
        $headers = ['Accept' => 'application/json', 'apikey' => (string) config('services.tripjack.api_key')];

        $responses = Http::pool(fn (Pool $pool) => array_map(
            fn ($n) => $pool->as((string) $n)->withHeaders($headers)->connectTimeout(5)->timeout(20)->get($url),
            range(1, $size),
        ));

        $ms = (int) round((microtime(true) - $startedAt) * 1000);

        return collect($responses)->map(function ($response, $n) use ($ms) {
            if (! $response instanceof Response) {
                return ['n' => (int) $n, 'status' => 0, 'ms' => $ms, 'retry_after' => null, 'limit_headers' => 'connection failed'];
            }

            $limitHeaders = collect($response->headers())
                ->filter(fn ($v, $name) => str_contains(strtolower($name), 'ratelimit') || str_contains(strtolower($name), 'rate-limit'))
                ->map(fn ($v, $name) => $name.'='.implode(',', (array) $v))
                ->implode('; ');

            return [
                'n' => (int) $n,
                'status' => $response->status(),
                'ms' => (int) round(($response->transferStats?->getTransferTime() ?? $ms / 1000) * 1000),
                'retry_after' => $response->header('Retry-After') ?: null,
                'limit_headers' => $limitHeaders,
            ];
        })->sortBy('n')->values()->all();
    }

    private function csv(array $rows): string
    {
        $lines = ['burst,request,status,ms,retry_after,rate_limit_headers'];
        foreach ($rows as $r) {
            $lines[] = implode(',', [$r['burst'], $r['n'], $r['status'], $r['ms'], $r['retry_after'] ?? '', '"'.str_replace('"', "'", $r['limit_headers']).'"']);
        }

        return implode("\n", $lines)."\n";
    }
}
