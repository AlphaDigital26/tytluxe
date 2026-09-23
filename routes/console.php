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
