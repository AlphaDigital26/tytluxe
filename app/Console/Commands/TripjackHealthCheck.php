<?php

namespace App\Console\Commands;

use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TripjackHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tripjack:health-check {--fail-silently : Always exit 0, even if a check fails (for schedules that alert via log only)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pings core TripJack API endpoints and reports whether the integration is up, replacing manual daily checks';

    public function handle(TripJackClient $client): int
    {
        $checks = [
            'nationality-info' => fn () => $client->nationalityInfo(),
            'fetch-countries' => fn () => $client->fetchCountries(),
        ];

        $results = [];
        $allHealthy = true;

        foreach ($checks as $name => $call) {
            $startedAt = microtime(true);

            try {
                $response = $call();
                $success = (bool) ($response['status']['success'] ?? true);
                $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

                $results[] = [$name, $success ? 'OK' : 'FAILED', "{$durationMs} ms", $success ? '' : 'status.success=false'];
                $allHealthy = $allHealthy && $success;
            } catch (TripJackException $e) {
                $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
                $results[] = [$name, 'FAILED', "{$durationMs} ms", $e->getMessage()];
                $allHealthy = false;
            }
        }

        $this->table(['Endpoint', 'Status', 'Latency', 'Detail'], $results);

        $logLevel = $allHealthy ? 'info' : 'critical';
        Log::channel('tripjack')->{$logLevel}('tripjack_health_check', [
            'healthy' => $allHealthy,
            'results' => $results,
        ]);

        if ($allHealthy) {
            $this->info('TripJack API is healthy.');

            return self::SUCCESS;
        }

        $this->error('TripJack API health check failed — see the table above and the tripjack log channel.');

        return $this->option('fail-silently') ? self::SUCCESS : self::FAILURE;
    }
}
