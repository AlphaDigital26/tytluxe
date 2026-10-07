<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\FlightSettings as FlightSettingsPage;
use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Support\DashboardData;
use App\Support\FlightBookingStatus;
use App\Support\FlightSettings;
use Illuminate\Support\Carbon;

/**
 * Flights at a glance on the dashboard. Reads only the database — the
 * TripJack wallet balance shown is the last one the 15-minute health check
 * saved, so the dashboard never waits on TripJack.
 */
class FlightsOverview extends MetricCardsWidget
{
    protected static ?int $sort = 4;

    /** Includes sales figures — same audience as the monthly numbers. */
    public static function canView(): bool
    {
        return BusinessSnapshot::canView() && FlightBookingResource::canViewAny();
    }

    protected function heading(): array
    {
        return ['Flights', '✈️'];
    }

    protected function link(): ?array
    {
        return ['label' => 'All flight bookings', 'url' => FlightBookingResource::getUrl('index')];
    }

    /** Five cards fit on one row on a laptop screen. */
    protected function minCardWidth(): int
    {
        return 170;
    }

    protected function cards(): array
    {
        $flights = FlightBookingResource::getEloquentQuery();
        $url = FlightBookingResource::getUrl('index');

        $attention = FlightBookingStatus::applyNeedsAttention(clone $flights)->count();
        $upcoming = (clone $flights)->where('status', 'confirmed')
            ->whereBetween('flight_departure_date', [today(), today()->addDays(7)])
            ->count();
        $today = (clone $flights)->where('status', 'confirmed')->whereDate('created_at', today())->count();
        $month = (clone $flights)->where('status', 'confirmed')->where('created_at', '>=', now()->startOfMonth());

        $wallet = DashboardData::walletBalance();
        $on = FlightSettings::bookingEnabled();
        $settingsUrl = FlightSettingsPage::canAccess() ? FlightSettingsPage::getUrl() : null;

        return [
            ['icon' => $attention ? '⚠️' : '✅', 'label' => 'Needs your attention', 'value' => (string) $attention,
                'hint' => $attention ? 'Click to see which bookings' : 'All good', 'tone' => $attention ? 'red' : 'green',
                'url' => $url.'?activeTab=attention'],
            ['icon' => '🛫', 'label' => 'Flights in the next 7 days', 'value' => (string) $upcoming,
                'hint' => $today.' new booking'.($today === 1 ? '' : 's').' today', 'tone' => 'blue',
                'url' => $url.'?activeTab=upcoming'],
            ['icon' => '💰', 'label' => 'Flight sales this month', 'value' => '₹'.number_format((float) (clone $month)->sum('total_amount')),
                'hint' => 'You earned ₹'.number_format((float) (clone $month)->sum('margin_amount')), 'tone' => 'gold'],
            ['icon' => '👛', 'label' => 'TripJack wallet', 'value' => $wallet === null ? '—' : '₹'.number_format($wallet['amount']),
                'hint' => match (true) {
                    $wallet === null => 'Checked every 15 minutes',
                    $wallet['low'] => 'Low — please top up',
                    default => 'Checked '.Carbon::parse($wallet['checked_at'])->diffForHumans(),
                },
                'tone' => $wallet && $wallet['low'] ? 'red' : 'violet', 'url' => $settingsUrl],
            ['icon' => $on ? '🟢' : '⏸️', 'label' => 'Online flight booking', 'value' => $on ? 'ON' : 'OFF',
                'hint' => $on ? 'Guests can book flights' : 'Guests cannot book right now', 'tone' => $on ? 'green' : 'amber',
                'url' => $settingsUrl],
        ];
    }
}
