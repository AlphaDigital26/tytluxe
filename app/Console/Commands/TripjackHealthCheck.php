<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Setting;
use App\Notifications\TripJackLowBalanceAlert;
use App\Services\FlightBookingService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackClient;
use App\Services\TripJack\TripJackFlightClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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

    /** Don't re-alert every 15 minutes while the balance stays low. */
    private const LOW_BALANCE_COOLDOWN_HOURS = 6;

    public function handle(TripJackClient $client, TripJackFlightClient $flightClient): int
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

        // Flights doc's User Detail API — the account balance every booking
        // is paid from. A failure here counts against health like the rest.
        $startedAt = microtime(true);
        try {
            $balance = $this->checkBalance($flightClient);
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $results[] = ['user-detail', 'OK', "{$durationMs} ms", $balance];
        } catch (TripJackException $e) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $results[] = ['user-detail', 'FAILED', "{$durationMs} ms", $e->getMessage()];
            $allHealthy = false;
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

    /**
     * Reads the TripJack balance and alerts active admins (mail + panel)
     * when it's below services.tripjack.flight.low_balance_alert.
     *
     * @return string table detail
     */
    private function checkBalance(TripJackFlightClient $flightClient): string
    {
        $detail = $flightClient->userDetail();
        $total = FlightBookingService::tripJackBalance($detail);
        if ($total === null) {
            throw new TripJackApiException('user-detail response has no balance fields: '.implode(', ', array_keys($detail)), status: 200, body: $detail);
        }
        $threshold = (float) config('services.tripjack.flight.low_balance_alert');

        if ($total >= $threshold) {
            return 'balance ₹'.number_format($total, 2);
        }

        Log::channel('tripjack')->critical('tripjack_low_balance', ['totalBalance' => $total, 'threshold' => $threshold, 'detail' => $detail]);

        $now = Carbon::now();
        $lastAlertedAt = Setting::get('tripjack_alert.low_balance_last_alerted_at');
        if (! $lastAlertedAt || Carbon::parse($lastAlertedAt)->diffInHours($now) >= self::LOW_BALANCE_COOLDOWN_HOURS) {
            $admins = Admin::where('status', 'Active')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new TripJackLowBalanceAlert($total, $threshold, $now));
            }
            Setting::set('tripjack_alert.low_balance_last_alerted_at', $now->toDateTimeString());
        }

        $this->warn('TripJack balance ₹'.number_format($total, 2).' is below the alert level of ₹'.number_format($threshold, 2).'.');

        return 'LOW balance ₹'.number_format($total, 2);
    }
}
