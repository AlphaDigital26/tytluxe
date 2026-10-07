<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard of the admin. Widgets (in order): welcome banner, "needs your
 * attention" list, this month's numbers, sales chart + upcoming trips,
 * enquiry mix + latest enquiries, website content counts.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }
}
