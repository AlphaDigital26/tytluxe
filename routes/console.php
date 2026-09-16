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
