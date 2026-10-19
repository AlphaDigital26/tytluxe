<?php

namespace App\Filament\Resources\PackageBookings\Pages;

use App\Filament\Resources\PackageBookings\PackageBookingResource;
use App\Support\PackageBookingStatus;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPackageBookings extends ListRecords
{
    protected static string $resource = PackageBookingResource::class;

    public function getSubheading(): ?string
    {
        return 'Every group package booked on your website. Open a booking to see what its status means and what to do, or use "More" for actions like cancelling or marking a balance as received.';
    }

    public function getTabs(): array
    {
        $base = PackageBookingResource::getEloquentQuery();

        return [
            'all' => Tab::make('All'),
            'attention' => Tab::make('Needs your attention')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(PackageBookingStatus::applyNeedsAttention(clone $base)->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => PackageBookingStatus::applyNeedsAttention($query)),
            'upcoming' => Tab::make('Upcoming departures')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', 'confirmed')
                    ->whereDate('check_in', '>=', today())
                    ->reorder('check_in')),
            'balance' => Tab::make('Balance due')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', 'confirmed')
                    ->whereNull('cancellation_requested_at')
                    ->where('package_balance_amount', '>', 0)
                    ->reorder('package_balance_due_on')),
            'cancelled' => Tab::make('Cancelled & refunded')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['cancelled', 'refunded'])),
            'unpaid' => Tab::make('Not completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['pending_payment', 'payment_failed'])),
        ];
    }
}
