<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EnquiriesByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Enquiries by Category';

    protected function getData(): array
    {
        $data = Enquiry::select('vertical', DB::raw('count(*) as total'))
            ->groupBy('vertical')
            ->pluck('total', 'vertical')
            ->toArray();

        $labels = array_map('ucfirst', array_keys($data));
        $values = array_values($data);

        return [
            'datasets' => [
                [
                    'label' => 'Enquiries',
                    'data' => $values,
                    'backgroundColor' => [
                        '#c9a84c', // gold
                        '#3b82f6', // blue-500
                        '#10b981', // emerald-500
                        '#ef4444', // red-500
                        '#f59e0b', // amber-500
                        '#8b5cf6', // violet-500
                        '#64748b', // slate-500
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
