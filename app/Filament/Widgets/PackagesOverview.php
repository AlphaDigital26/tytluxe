<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PackageBookings\PackageBookingResource;
use App\Support\DashboardData;
use App\Support\PackageBookingStatus;

/** Group package bookings at a glance on the dashboard — database only. */
class PackagesOverview extends MetricCardsWidget
{
    protected static ?int $sort = 4;

    /** Includes sales figures — same audience as the monthly numbers. */
    public static function canView(): bool
    {
        return BusinessSnapshot::canView() && PackageBookingResource::canViewAny();
    }

    protected function heading(): array
    {
        return ['Package bookings', '🧳'];
    }

    protected function link(): ?array
    {
        return ['label' => 'All package bookings', 'url' => PackageBookingResource::getUrl('index')];
    }

    protected function cards(): array
    {
        $packages = PackageBookingResource::getEloquentQuery();
        $url = PackageBookingResource::getUrl('index');

        $attention = PackageBookingStatus::applyNeedsAttention(clone $packages)->count();
        $departures = (clone $packages)->where('status', 'confirmed')
            ->whereNull('cancellation_requested_at')
            ->whereBetween('check_in', [today(), today()->addDays(7)])
            ->count();
        $balances = (clone $packages)->where('status', 'confirmed')
            ->whereNull('cancellation_requested_at')
            ->where('package_balance_amount', '>', 0)
            ->whereDate('package_balance_due_on', '<=', today()->addDays(7));
        $balanceCount = (clone $balances)->count();
        $month = (clone $packages)->whereIn('status', DashboardData::SOLD)->where('created_at', '>=', now()->startOfMonth());
        $monthCount = (clone $month)->count();

        return [
            ['icon' => $attention ? '⚠️' : '✅', 'label' => 'Needs your attention', 'value' => (string) $attention,
                'hint' => $attention ? 'Cancellation requests, balances due, refunds' : 'All good', 'tone' => $attention ? 'red' : 'green',
                'url' => $url.'?tab=attention'],
            ['icon' => '🚌', 'label' => 'Departures in the next 7 days', 'value' => (string) $departures,
                'hint' => 'Guests travelling soon', 'tone' => 'blue',
                'url' => $url.'?tab=upcoming'],
            ['icon' => '⏳', 'label' => 'Balances due in the next 7 days', 'value' => (string) $balanceCount,
                'hint' => $balanceCount ? 'Remind these guests to pay' : 'None due', 'tone' => $balanceCount ? 'amber' : 'green',
                'url' => $url.'?tab=balance'],
            ['icon' => '💰', 'label' => 'Package sales this month', 'value' => '₹'.number_format((float) (clone $month)->sum('total_amount')),
                'hint' => $monthCount.' booking'.($monthCount === 1 ? '' : 's').' this month', 'tone' => 'gold'],
        ];
    }
}
