<?php

namespace App\Filament\Resources\CabBookings\Pages;

use App\Filament\Resources\CabBookings\CabBookingResource;
use App\Support\CabBookingStatus;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCabBookings extends ListRecords
{
    protected static string $resource = CabBookingResource::class;

    public function getSubheading(): ?string
    {
        return 'Every cab booked on your website. Open a booking to see what its status means and what to do.';
    }

    public function getTabs(): array
    {
        $base = CabBookingResource::getEloquentQuery();

        return [
            'all' => Tab::make('All'),
            'attention' => Tab::make('Needs your attention')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(CabBookingStatus::applyNeedsAttention(clone $base)->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => CabBookingStatus::applyNeedsAttention($query)),
            'today' => Tab::make('Pickups today')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed')
                    ->whereBetween('cab_pickup_at', [today(), today()->endOfDay()])->reorder('cab_pickup_at')),
            'upcoming' => Tab::make('Upcoming')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed')
                    ->where('cab_pickup_at', '>=', now())->reorder('cab_pickup_at')),
            'cancelled' => Tab::make('Cancelled & refunded')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['cancelled', 'refunded'])),
            'unpaid' => Tab::make('Not completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['pending_payment', 'payment_failed'])),
        ];
    }
}
