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

    /** No auto-refresh (Filament's default is every 5s). */
    protected ?string $pollingInterval = null;

    /** Filament's chart, with gold period pills in place of the dropdown. */
    protected string $view = 'filament.widgets.sales-chart';

    /** The period picked in the chart's top-right menu. */
    public ?string $filter = '6m';

    protected function getFilters(): ?array
    {
        return DashboardData::SALES_PERIODS;
    }

    public function getHeading(): ?string
    {
        return 'Sales — '.strtolower(DashboardData::SALES_PERIODS[$this->filter] ?? 'last 6 months');
    }

    public function getDescription(): ?string
    {
        $s = DashboardData::salesSeries($this->filter ?? '6m');

        return 'Money guests paid for bookings that went ahead: ₹'.number_format($s['total']).' in total'
            .(in_array($this->filter, ['7d', '30d'], true) ? ', by day.' : ($this->filter === '3m' ? ', by week.' : ', by month.'));
    }

    protected ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return BusinessSnapshot::canView();
    }

    protected function getData(): array
    {
        $m = DashboardData::salesSeries($this->filter ?? '6m');

        return [
            'datasets' => [
                ['label' => 'Hotels', 'data' => $m['hotel'], 'backgroundColor' => '#c9a84c', 'borderRadius' => 6, 'stack' => 'sales'],
                ['label' => 'Flights', 'data' => $m['flight'], 'backgroundColor' => '#3b82f6', 'borderRadius' => 6, 'stack' => 'sales'],
                ['label' => 'Packages', 'data' => $m['package'], 'backgroundColor' => '#10b981', 'borderRadius' => 6, 'stack' => 'sales'],
                ['label' => 'Cabs', 'data' => $m['cab'], 'backgroundColor' => '#8b5cf6', 'borderRadius' => 6, 'stack' => 'sales'],
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
