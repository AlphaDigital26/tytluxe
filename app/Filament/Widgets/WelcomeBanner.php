<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\FlightSettings;
use App\Filament\Pages\HotelListingSettings;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Support\DashboardData;
use Filament\Widgets\Widget;

/** Greeting, today's date, a one-line summary and the most-used shortcuts. */
class WelcomeBanner extends Widget
{
    protected string $view = 'filament.widgets.welcome-banner';

    protected static ?int $sort = 0;

    /** Load with the page (one request) instead of one request per widget. */
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $hour = (int) now('Asia/Kolkata')->format('G');
        $todo = DashboardData::todo();

        $links = array_values(array_filter([
            ['label' => 'View website', 'icon' => '🌐', 'url' => url('/'), 'new_tab' => true],
            EnquiryResource::canCreate() ? ['label' => 'Add phone enquiry', 'icon' => '➕', 'url' => EnquiryResource::getUrl('create'), 'new_tab' => false] : null,
            HotelListingSettings::canAccess() ? ['label' => 'Hotel settings', 'icon' => '🏨', 'url' => HotelListingSettings::getUrl(), 'new_tab' => false] : null,
            FlightSettings::canAccess() ? ['label' => 'Flight settings', 'icon' => '✈️', 'url' => FlightSettings::getUrl(), 'new_tab' => false] : null,
        ]));

        return [
            'greeting' => $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening'),
            'name' => strtok((string) auth('admin')->user()?->name, ' ') ?: 'there',
            'date' => now('Asia/Kolkata')->format('l, j F Y'),
            'todoCount' => count($todo),
            'links' => $links,
        ];
    }
}
