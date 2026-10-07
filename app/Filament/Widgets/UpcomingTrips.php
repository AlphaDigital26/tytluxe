<?php

namespace App\Filament\Widgets;

use App\Support\DashboardData;
use Filament\Widgets\Widget;

/** Guests checking in or flying in the next 7 days — so nobody is forgotten. */
class UpcomingTrips extends Widget
{
    protected string $view = 'filament.widgets.upcoming-trips';

    protected static ?int $sort = 6;

    /** Load with the page (one request) instead of one request per widget. */
    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        return ['trips' => DashboardData::upcomingTrips(7, 8)];
    }
}
