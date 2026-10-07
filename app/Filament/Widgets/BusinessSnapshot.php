<?php

namespace App\Filament\Widgets;

use App\Support\DashboardData;
use App\Support\HotelSettings;
use App\Support\FlightSettings;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * This month's money and activity, each compared with last month and with a
 * 6-month trend line. Sales = hotel + flight bookings that went ahead.
 */
class BusinessSnapshot extends BaseWidget
{
    protected static ?int $sort = 2;

    /** Load with the page (one request) instead of one request per widget. */
    protected static bool $isLazy = false;

    protected ?string $heading = 'This month at a glance';

    protected ?string $description = 'Compared with last month. The small line shows the last 6 months.';

    protected int|string|array $columnSpan = 'full';

    /** Money figures are for the people who run the business. */
    public static function canView(): bool
    {
        return in_array(auth('admin')->user()?->role, ['Super Admin', 'Operations', 'Finance', 'Analyst'], true);
    }

    protected function getStats(): array
    {
        $m = DashboardData::monthly(6);
        $sales = array_map(fn ($h, $f) => $h + $f, $m['hotel'], $m['flight']);

        $wallet = DashboardData::walletBalance();
        $bookingOn = HotelSettings::bookingEnabled() && FlightSettings::bookingEnabled();

        return [
            $this->stat('Sales', $sales, money: true)
                ->description($this->change($sales).' · hotels ₹'.number_format(end($m['hotel']), 0).', flights ₹'.number_format(end($m['flight']), 0)),
            $this->stat('Your earnings', $m['earning'], money: true),
            $this->stat('Bookings', $m['bookings']),
            $this->stat('Enquiries received', $m['enquiries']),
            Stat::make('TripJack wallet', $wallet ? '₹'.number_format($wallet['amount'], 0) : 'Not checked yet')
                ->description($wallet
                    ? ($wallet['low'] ? 'Low — please top up' : 'Enough for new bookings')
                    : 'Checked automatically every 15 minutes')
                ->descriptionIcon($wallet && $wallet['low'] ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-wallet')
                ->color($wallet && $wallet['low'] ? 'danger' : 'gray'),
            Stat::make('Online booking', $bookingOn ? 'ON' : 'Partly OFF')
                ->description($bookingOn ? 'Guests can book hotels and flights' : (HotelSettings::bookingEnabled() ? 'Flights are switched off' : (FlightSettings::bookingEnabled() ? 'Hotels are switched off' : 'Hotels and flights are switched off')))
                ->descriptionIcon($bookingOn ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle')
                ->color($bookingOn ? 'success' : 'warning'),
        ];
    }

    /** @param array<int, float|int> $series last element = this month */
    protected function stat(string $label, array $series, bool $money = false): Stat
    {
        $now = end($series);
        $up = count($series) > 1 && $now >= $series[count($series) - 2];

        return Stat::make($label, $money ? '₹'.number_format($now, 0) : number_format($now))
            ->description($this->change($series))
            ->descriptionIcon($up ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->chart(array_values($series))
            ->color($up ? 'success' : 'warning');
    }

    /** "Up 12% on last month" in plain words. */
    protected function change(array $series): string
    {
        $now = (float) end($series);
        $before = (float) ($series[count($series) - 2] ?? 0);

        return match (true) {
            $before == 0 && $now == 0 => 'Same as last month',
            $before == 0 => 'Up from nothing last month',
            default => (($pct = round(($now - $before) / $before * 100)) >= 0 ? 'Up '.$pct : 'Down '.abs($pct)).'% on last month',
        };
    }
}
