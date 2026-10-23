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
        // The live site never shows debug error pages (they reveal code and
        // settings), even if APP_DEBUG=true is left in its .env by mistake.
        if ($this->app->isProduction() && config('app.debug')) {
            config(['app.debug' => false]);
        }

        // One password rule for sign-up, reset and change-password.
        \Illuminate\Validation\Rules\Password::defaults(fn () => \Illuminate\Validation\Rules\Password::min(8)
            ->letters()->mixedCase()->numbers()->symbols());

        \Illuminate\Support\Facades\Gate::policy(
            \Filament\Actions\Exports\Models\Export::class,
            \App\Policies\ExportPolicy::class
        );
    }
}
