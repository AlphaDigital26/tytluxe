<?php

namespace App\Filament\Resources\FlightBookings\Pages;

use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Support\FlightBookingStatus;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFlightBookings extends ListRecords
{
    protected static string $resource = FlightBookingResource::class;

    public function getSubheading(): ?string
    {
        return 'Every flight booked on your website. Hover over a status to see what it means, and use "More" for actions like cancelling or downloading the invoice.';
    }

    public function getTabs(): array
    {
        $base = FlightBookingResource::getEloquentQuery();

        return [
            'all' => Tab::make('All'),
            'attention' => Tab::make('Needs your attention')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(FlightBookingStatus::applyNeedsAttention(clone $base)->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => FlightBookingStatus::applyNeedsAttention($query)),
            'upcoming' => Tab::make('Upcoming trips')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', 'confirmed')
                    ->whereDate('flight_departure_date', '>=', today())
                    ->reorder('flight_departure_date')),
            'reserved' => Tab::make('Reserved, not paid')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'on_hold')),
            'cancelled' => Tab::make('Cancelled & refunded')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['cancelled', 'refunded'])),
            'unpaid' => Tab::make('Not completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['pending_payment', 'payment_failed', 'hold_expired'])),
        ];
    }
}
