<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Setting;
use App\Notifications\TripJackRateLimitAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class MonitorTripJackRateLimits extends Command
{
    protected $signature = 'tripjack:monitor-rate-limits';

    protected $description = 'Scan recent tripjack.log entries for 429 rate-limit responses and alert admins if they spike';

    /** Alert once at least this many 429s land within the trailing window. */
    private const THRESHOLD = 5;

    private const WINDOW_MINUTES = 15;

    /** Avoid re-alerting every 15 minutes while rate limiting is ongoing. */
    private const COOLDOWN_MINUTES = 60;

    public function handle(): int
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->subMinutes(self::WINDOW_MINUTES);

        $count = $this->count429sSince($windowStart);

        if ($count < self::THRESHOLD) {
            $this->info('TripJack 429 count in last '.self::WINDOW_MINUTES."m: {$count} (below threshold).");

            return self::SUCCESS;
        }

        $lastAlertedAt = Setting::get('tripjack_alert.last_alerted_at');

        if ($lastAlertedAt && Carbon::parse($lastAlertedAt)->diffInMinutes($now) < self::COOLDOWN_MINUTES) {
            $this->info("TripJack 429 count is {$count} (over threshold) but the last alert was sent within the cooldown window, skipping.");

            return self::SUCCESS;
        }

        $admins = Admin::where('status', 'Active')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new TripJackRateLimitAlert($count, self::WINDOW_MINUTES, $now));
        }

        Setting::set('tripjack_alert.last_alerted_at', $now->toDateTimeString());

        $this->warn("TripJack rate-limit alert sent: {$count} 429s in the last ".self::WINDOW_MINUTES.' minutes.');

        return self::SUCCESS;
    }

    private function count429sSince(Carbon $windowStart): int
    {
        $count = 0;

        foreach ($this->logFilesToScan($windowStart) as $file) {
            if (! is_file($file)) {
                continue;
            }

            $handle = fopen($file, 'r');

            if (! $handle) {
                continue;
            }

            while (($line = fgets($handle)) !== false) {
                if (! str_contains($line, '"status":429')) {
                    continue;
                }

                if (! preg_match('/^\[(?<ts>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $m)) {
                    continue;
                }

                if (Carbon::parse($m['ts'])->lt($windowStart)) {
                    continue;
                }

                $count++;
            }

            fclose($handle);
        }

        return $count;
    }

    /**
     * @return array<int, string>
     */
    private function logFilesToScan(Carbon $windowStart): array
    {
        // The 'tripjack' channel rotates daily; scan today's file, and
        // yesterday's too if the trailing window straddles midnight.
        $dates = array_unique([
            $windowStart->format('Y-m-d'),
            Carbon::now()->format('Y-m-d'),
        ]);

        return array_map(
            fn (string $date) => storage_path("logs/tripjack-{$date}.log"),
            $dates,
        );
    }
}
