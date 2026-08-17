<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EnquiriesOverTimeChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = 'Enquiries Over Time (Last 12 Months)';

    protected function getData(): array
    {
        $data = Enquiry::select(
            DB::raw('count(*) as count'),
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
        )
        ->where('created_at', '>=', Carbon::now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $labels = [];
        $values = [];

        // Generate the last 12 months in order to ensure missing months are 0
        for ($i = 11; $i >= 0; $i--) {
            $monthString = Carbon::now()->subMonths($i)->format('Y-m');
            $displayString = Carbon::now()->subMonths($i)->format('M Y');
            $labels[] = $displayString;

            $record = $data->firstWhere('month', $monthString);
            $values[] = $record ? $record->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Enquiries',
                    'data' => $values,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(201, 168, 76, 0.2)', // gold transparent
                    'borderColor' => '#c9a84c', // gold
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
