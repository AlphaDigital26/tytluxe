<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Support\FlightSettings::class);
        $this->app->scoped(\App\Support\HotelSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(
            \Filament\Actions\Exports\Models\Export::class,
            \App\Policies\ExportPolicy::class
        );
    }
}
