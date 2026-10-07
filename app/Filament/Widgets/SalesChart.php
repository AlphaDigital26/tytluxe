<?php

namespace App\Filament\Widgets;

use App\Support\DashboardData;
use Filament\Widgets\ChartWidget;

/** Hotel and flight sales per month, stacked, so growth is visible at a glance. */
class SalesChart extends ChartWidget
{
    protected static ?int $sort = 5;

    /** Load with the page (one request) instead of one request per widget. */
    protected static bool $isLazy = false;

    protected ?string $heading = 'Sales over the last 6 months';

    protected ?string $description = 'Money guests paid for bookings that went ahead.';

    protected ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return BusinessSnapshot::canView();
    }

    protected function getData(): array
    {
        $m = DashboardData::monthly(6);

        return [
            'datasets' => [
                ['label' => 'Hotels', 'data' => $m['hotel'], 'backgroundColor' => '#c9a84c', 'borderRadius' => 6, 'stack' => 'sales'],
                ['label' => 'Flights', 'data' => $m['flight'], 'backgroundColor' => '#3b82f6', 'borderRadius' => 6, 'stack' => 'sales'],
            ],
            'labels' => $m['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => [
                'x' => ['stacked' => true, 'grid' => ['display' => false]],
                'y' => ['stacked' => true, 'beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
