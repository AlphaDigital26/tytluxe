<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\Destination;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Leads', Enquiry::count())
                ->description('All-time enquiries')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('primary'),
                
            Stat::make('Open Enquiries', Enquiry::where('status', 'open')->orWhere('status', 'New')->count())
                ->description('Needs your attention')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
                
            Stat::make('Total Packages', Package::count())
                ->description('Curated experiences')
                ->descriptionIcon('heroicon-m-gift')
                ->color('info'),
                
            Stat::make('Total Hotels', Hotel::count())
                ->description('Active listed properties')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
                
            Stat::make('Total Destinations', Destination::count())
                ->description('Global reach')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('warning'),
                
            Stat::make('Total Reviews', Review::count())
                ->description('Customer feedback')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),
        ];
    }
}
