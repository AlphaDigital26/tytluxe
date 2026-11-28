<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\CabSettings as CabSettingsPage;
use App\Filament\Resources\CabBookings\CabBookingResource;
use App\Services\CabBookingService;
use App\Support\CabBookingStatus;
use App\Support\CabSettings;

/**
 * Cabs at a glance on the dashboard. Database only — never waits on TripJack.
 */
class CabsOverview extends MetricCardsWidget
{
    protected static ?int $sort = 4;

    /** Includes sales figures — same audience as the monthly numbers. */
    public static function canView(): bool
    {
        return BusinessSnapshot::canView() && CabBookingResource::canViewAny();
    }

    protected function heading(): array
    {
        return ['Cabs', '🚕'];
    }

    protected function link(): ?array
    {
        return ['label' => 'All cab bookings', 'url' => CabBookingResource::getUrl('index')];
    }

    protected function minCardWidth(): int
    {
        return 170;
    }

    protected function cards(): array
    {
        $cabs = CabBookingResource::getEloquentQuery();
        $url = CabBookingResource::getUrl('index');

        $attention = CabBookingStatus::applyNeedsAttention(clone $cabs)->count();
        $confirmed = (clone $cabs)->where('status', 'confirmed')->whereNull('cancellation_requested_at');
        $pickupsToday = (clone $confirmed)->whereBetween('cab_pickup_at', [today(), today()->endOfDay()])->count();
        $nextWeek = (clone $confirmed)->whereBetween('cab_pickup_at', [now(), today()->addDays(7)->endOfDay()])->count();
        $month = (clone $cabs)->where('status', 'confirmed')->where('created_at', '>=', now()->startOfMonth());

        $on = CabSettings::bookingEnabled();
        $ready = CabBookingService::configured();
        $settingsUrl = CabSettingsPage::canAccess() ? CabSettingsPage::getUrl() : null;
        $markup = CabSettings::markup();

        return [
            ['icon' => $attention ? '⚠️' : '✅', 'label' => 'Needs your attention', 'value' => (string) $attention,
                'hint' => $attention ? 'Click to see which bookings' : 'All good', 'tone' => $attention ? 'red' : 'green',
                'url' => $url.'?tab=attention'],
            ['icon' => '🚕', 'label' => 'Pickups today', 'value' => (string) $pickupsToday,
                'hint' => $nextWeek.' in the next 7 days', 'tone' => 'blue',
                'url' => $url.'?tab='.($pickupsToday ? 'today' : 'upcoming')],
            ['icon' => '💰', 'label' => 'Cab sales this month', 'value' => '₹'.number_format((float) (clone $month)->sum('total_amount')),
                'hint' => 'You earned ₹'.number_format((float) (clone $month)->sum('margin_amount')), 'tone' => 'gold'],
            ['icon' => '🏷️', 'label' => 'Your earning per trip', 'value' => '₹'.number_format($markup),
                'hint' => $markup > 0 ? 'Added to each one-way cab' : 'Set it in Cab Settings', 'tone' => $markup > 0 ? 'violet' : 'amber',
                'url' => $settingsUrl],
            ['icon' => ! $ready ? '🛠️' : ($on ? '🟢' : '⏸️'), 'label' => 'Online cab booking',
                'value' => ! $ready ? 'NOT SET UP' : ($on ? 'ON' : 'OFF'),
                'hint' => ! $ready ? 'TripJack user ID missing' : ($on ? 'Guests can book cabs' : 'Guests cannot book right now'),
                'tone' => $ready && $on ? 'green' : 'amber', 'url' => $settingsUrl],
        ];
    }
}
