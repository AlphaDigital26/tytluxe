<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\HotelListingSettings;
use App\Filament\Resources\Bookings\BookingResource;
use App\Support\DashboardData;
use App\Support\HotelBookingStatus;
use App\Support\HotelSettings;

/** Hotel bookings at a glance on the dashboard — database only, no TripJack calls. */
class HotelsOverview extends MetricCardsWidget
{
    protected static ?int $sort = 3;

    /** Includes sales figures — same audience as the monthly numbers. */
    public static function canView(): bool
    {
        return BusinessSnapshot::canView() && BookingResource::canViewAny();
    }

    protected function heading(): array
    {
        return ['Hotel bookings', '🏨'];
    }

    protected function link(): ?array
    {
        return ['label' => 'All hotel bookings', 'url' => BookingResource::getUrl('index')];
    }

    protected function cards(): array
    {
        $hotels = BookingResource::getEloquentQuery();
        $url = BookingResource::getUrl('index');

        $attention = HotelBookingStatus::applyNeedsAttention(clone $hotels)->count();
        $checkIns = (clone $hotels)->whereIn('status', DashboardData::SOLD)
            ->whereNull('cancellation_requested_at')
            ->whereBetween('check_in', [today(), today()->addDays(7)])
            ->count();
        $today = (clone $hotels)->whereIn('status', DashboardData::SOLD)->whereDate('created_at', today())->count();

        $month = (clone $hotels)->whereIn('status', DashboardData::SOLD)->where('created_at', '>=', now()->startOfMonth());
        $on = HotelSettings::bookingEnabled();

        return [
            ['icon' => $attention ? '⚠️' : '✅', 'label' => 'Needs your attention', 'value' => (string) $attention,
                'hint' => $attention ? 'Click to see which bookings' : 'All good', 'tone' => $attention ? 'red' : 'green',
                'url' => $url.'?activeTab=attention'],
            ['icon' => '🛎️', 'label' => 'Check-ins in the next 7 days', 'value' => (string) $checkIns,
                'hint' => $today.' new booking'.($today === 1 ? '' : 's').' today', 'tone' => 'blue',
                'url' => $url.'?activeTab=upcoming'],
            ['icon' => '💰', 'label' => 'Hotel sales this month', 'value' => '₹'.number_format((float) (clone $month)->sum('total_amount')),
                'hint' => 'You earned ₹'.number_format((float) (clone $month)->sum('margin_amount')), 'tone' => 'gold'],
            ['icon' => $on ? '🟢' : '⏸️', 'label' => 'Online hotel booking', 'value' => $on ? 'ON' : 'OFF',
                'hint' => $on ? 'Guests can book hotels' : 'Guests cannot book right now', 'tone' => $on ? 'green' : 'amber',
                'url' => HotelListingSettings::canAccess() ? HotelListingSettings::getUrl() : null],
        ];
    }
}
