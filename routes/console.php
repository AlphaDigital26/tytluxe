<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    User::whereNull('email_verified_at')
        ->where('created_at', '<=', now()->subHours(48))
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
