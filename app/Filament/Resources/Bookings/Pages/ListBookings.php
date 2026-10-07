<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Support\HotelBookingStatus;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    public function getSubheading(): ?string
    {
        return 'Every hotel booked on your website. Hover over a status to see what it means, and use "More" for actions like cancelling or downloading the invoice.';
    }

    public function getTabs(): array
    {
        $base = BookingResource::getEloquentQuery();

        return [
            'all' => Tab::make('All'),
            'attention' => Tab::make('Needs your attention')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(HotelBookingStatus::applyNeedsAttention(clone $base)->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => HotelBookingStatus::applyNeedsAttention($query)),
            'upcoming' => Tab::make('Upcoming check-ins')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereIn('status', ['confirmed', 'pending_confirmation'])
                    ->whereNull('cancellation_requested_at')
                    ->whereDate('check_in', '>=', today())
                    ->reorder('check_in')),
            'cancelled' => Tab::make('Cancelled & refunded')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['cancelled', 'refunded'])),
            'unpaid' => Tab::make('Not completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['pending_payment', 'payment_failed', 'hold_expired'])),
        ];
    }
}
