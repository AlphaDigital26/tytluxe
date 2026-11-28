<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sign-ups that never verified, after 48 hours. Never an account with
// bookings — those are real customers whatever their verified flag says.
Schedule::call(function () {
    User::whereNull('email_verified_at')
        ->where('created_at', '<=', now()->subHours(48))
        ->whereDoesntHave('bookings')
        ->forceDelete();
})->hourly();

Schedule::command('app:sync-tripjack-hotel-mappings UPDATE')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('app:sync-tripjack-hotel-mappings NEW')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('app:sync-tripjack-hotel-mappings DELETE')->dailyAt('03:00')->withoutOverlapping();

// Replaces manual daily TripJack up/down checks — runs every 15 min, logs to
// the tripjack channel at critical level on failure, exits non-zero on
// failure so scheduler failure hooks/monitoring can pick it up.
Schedule::command('tripjack:health-check')->everyFifteenMinutes()->withoutOverlapping();

// Rolling room-type freshness — keeps every TripJack hotel's room data
// (occupancy, bed type, amenities, photos) from going stale without any
// admin manually clicking "Fetch Rooms". Small batch, frequent run, so a
// ~1000-hotel catalogue cycles fully every few days without bursting
// TripJack's rate limits. See ResyncStaleRoomTypes for the ordering logic.
Schedule::command('app:resync-stale-room-types')->everyFifteenMinutes()->withoutOverlapping();

// Watches tripjack.log for 429 spikes and alerts admins (email + in-panel
// notification) instead of a rate-limit spike going unnoticed until a
// guest complains. Self-throttles its own alerts (see command class).
Schedule::command('tripjack:monitor-rate-limits')->everyFifteenMinutes()->withoutOverlapping();

// Queue worker driven by the scheduler, so the one `schedule:run` cron
// entry also processes queued jobs (room-type fills, destination syncs,
// notifications) — no separate always-on worker process needed. Exits when
// the queue is empty or after ~55s; withoutOverlapping keeps it to one.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=55')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->runInBackground();

// Full TripJack city/region list, weekly — hotel syncs look cities up here
// (falling back to slow country scans for any city missing from it).
Schedule::command('app:cache-tripjack-cities')->weeklyOn(0, '01:00')->withoutOverlapping();

// Hotel bookings still PENDING at TripJack get re-checked in the background
// (not only when the guest reloads the page); pending cancellations and
// upcoming stays once a day, per TripJack's docs.
Schedule::command('tripjack:refresh-hotel-bookings')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('tripjack:refresh-hotel-bookings --cancellations')->dailyAt('06:00')->withoutOverlapping();


// Group packages: balance reminders, and cancelling bookings whose balance
// is still unpaid on the departure day (booking amount forfeited).
Schedule::command('packages:balance-check')->hourly()->withoutOverlapping();

// ── TripJack UAT certification logs (see App\Support\UatRecorder) ──────────
// php artisan uat:start flight oneway DEL-BOM-1A-DIRECT   → Tripjack API logs/oneway/DEL-BOM-1A-DIRECT/
// php artisan uat:start hotel 2                           → HOTEL API TEST CASES/Test Case 2/
// php artisan uat:start cab airport-oneway                → CAB API TEST CASES/Airport Transfer - One Way/
Artisan::command('uat:start {vertical : flight, hotel or cab} {group : oneway, roundtrip, multicity; the hotel test case number; or for cabs airport|outstation|local|embedded-oneway|roundtrip} {name? : flight case name, e.g. DEL-BOM-1A-DIRECT}', function (string $vertical, string $group, ?string $name = null) {
    if (config('services.tripjack.env') === 'production') {
        return $this->error('UAT logs are only for the TripJack test (UAT) system, not production.');
    }
    if ($vertical === 'flight') {
        if (! in_array($group, ['oneway', 'roundtrip', 'multicity'], true) || ! $name) {
            return $this->error('Use: php artisan uat:start flight oneway|roundtrip|multicity DEL-BOM-1A-DIRECT');
        }
        $folder = \App\Support\UatRecorder::FLIGHT_ROOT.'/'.$group.'/'.strtoupper($name);
    } elseif ($vertical === 'hotel' && ctype_digit($group)) {
        $folder = \App\Support\UatRecorder::HOTEL_ROOT.'/Test Case '.$group;
    } elseif ($vertical === 'cab' && preg_match('/^(airport|outstation|local|embedded)-(oneway|roundtrip)$/', $group)) {
        // TripJack cab doc 4.1–4.4, e.g. "airport-oneway" → CAB API TEST CASES/Airport Transfer - One Way
        [$type, $way] = explode('-', $group);
        $folder = \App\Support\UatRecorder::CAB_ROOT.'/'.['airport' => 'Airport Transfer', 'outstation' => 'Outstation', 'local' => 'Local', 'embedded' => 'Embedded'][$type]
            .' - '.($way === 'oneway' ? 'One Way' : 'Round Trip');
    } else {
        return $this->error('Use: php artisan uat:start hotel 2   or   php artisan uat:start cab airport-oneway   (airport|outstation|local|embedded - oneway|roundtrip)');
    }
    $dir = \App\Support\UatRecorder::start($vertical, $folder);
    $this->info("Recording {$vertical} test case: {$folder}");
    $this->line("Now do this case on the website. Files are saved in {$dir}");
})->purpose('Start recording one TripJack UAT test case');

Artisan::command('uat:status', function () {
    $case = \App\Support\UatRecorder::active();
    if (! $case) {
        return $this->line('No test case is being recorded.');
    }
    $dir = \App\Support\UatRecorder::root().'/'.$case['folder'];
    $this->info("Recording: {$case['folder']}");
    foreach (glob($dir.'/*.json') ?: [] as $f) {
        $this->line('  '.basename($f));
    }
})->purpose('Show the test case being recorded and its files');

Artisan::command('uat:stop', function () {
    \App\Support\UatRecorder::stop();
    $this->info('Stopped recording.');
})->purpose('Stop recording TripJack UAT logs');

Artisan::command('uat:zip', function () {
    $root = \App\Support\UatRecorder::root();
    $zipPath = $root.'/TYT-Luxe-TripJack-UAT-Logs-'.now()->format('Y-m-d').'.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $count = 0;
    foreach ([\App\Support\UatRecorder::FLIGHT_ROOT, \App\Support\UatRecorder::HOTEL_ROOT, \App\Support\UatRecorder::CAB_ROOT] as $top) {
        if (! is_dir("$root/$top")) {
            continue;
        }
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/$top", FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            $zip->addFile($file->getPathname(), str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($root) + 1)));
            $count++;
        }
    }
    $zip->close();
    $this->info("{$count} files zipped: {$zipPath}");
})->purpose('Zip all recorded UAT test cases for TripJack');
