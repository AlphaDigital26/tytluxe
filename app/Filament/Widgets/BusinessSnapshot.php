<?php

namespace App\Filament\Widgets;

use App\Support\DashboardData;
use App\Support\HotelSettings;
use App\Support\FlightSettings;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * This month's money and activity, each compared with last month and with a
 * 6-month trend line. Sales = hotel, flight and package bookings that went ahead.
 */
class BusinessSnapshot extends BaseWidget
{
    protected static ?int $sort = 2;

    /** Load with the page (one request) instead of one request per widget. */
    protected static bool $isLazy = false;

    /** No auto-refresh (Filament's default is every 5s) — monthly totals don't change by the second. */
    protected ?string $pollingInterval = null;

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
        // Same three as the sales chart below, so the two always agree.
        $sales = array_map(fn ($h, $f, $p) => $h + $f + $p, $m['hotel'], $m['flight'], $m['package']);

        $wallet = DashboardData::walletStatus();
        $bookingOn = HotelSettings::bookingEnabled() && FlightSettings::bookingEnabled();

        return [
            $this->stat('Sales', $sales, money: true)
                ->description($this->change($sales, true).' · hotels ₹'.number_format(end($m['hotel']), 0).', flights ₹'.number_format(end($m['flight']), 0).', packages ₹'.number_format(end($m['package']), 0)),
            // Packages are your own trips: what they cost you isn't recorded
            // here, so there is no mark-up to count — only hotels and flights.
            $this->stat('Your earnings (hotels & flights)', $m['earning'], money: true),
            $this->stat('Bookings', $m['bookings']),
            $this->stat('Enquiries received', $m['enquiries']),
            Stat::make('TripJack wallet', $wallet['value'])
                ->description($wallet['hint'])
                ->descriptionIcon($wallet['problem'] ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-wallet')
                ->color($wallet['problem'] ? 'danger' : 'gray')
                ->url($wallet['url']),
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
            ->description($this->change($series, $money))
            ->descriptionIcon($up ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->chart(array_values($series))
            ->color($up ? 'success' : 'warning');
    }

    /**
     * "Up 12% on last month" in plain words. Big jumps from a small month read
     * as a bug in percent ("Up 18036%"), so those say how many times larger,
     * or simply what last month was.
     */
    protected function change(array $series, bool $money = false): string
    {
        $now = (float) end($series);
        $before = (float) ($series[count($series) - 2] ?? 0);
        $last = $money ? '₹'.number_format($before, 0) : number_format($before);

        return match (true) {
            $before == 0 && $now == 0 => 'Same as last month',
            $before == 0 => 'Up from nothing last month',
            $now >= $before * 10 => 'Much higher than last month ('.$last.')',
            $now >= $before * 3 => 'About '.round($now / $before).' times last month',
            default => (($pct = round(($now - $before) / $before * 100)) >= 0 ? 'Up '.$pct : 'Down '.abs($pct)).'% on last month',
        };
    }
}
