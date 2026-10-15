<?php

namespace App\Filament\Resources\PackageBookings\Pages;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\Bookings\HotelBookingActions;
use App\Filament\Resources\PackageBookings\PackageBookingActions;
use App\Filament\Resources\PackageBookings\PackageBookingResource;
use App\Models\Booking;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;

class ViewPackageBooking extends ViewRecord
{
    protected static string $resource = PackageBookingResource::class;

    public function getTitle(): string
    {
        return 'Booking '.$this->record->reference;
    }

    /** One line under the title: whose trip, where, when. */
    public function getSubheading(): ?string
    {
        $r = $this->record;

        return trim(($r->lead_guest_name ?: 'Guest').' · '.($r->package?->title ?? 'Package')
            .($r->check_in ? ' · '.\Illuminate\Support\Carbon::parse($r->check_in)->format('j M Y') : '')
            .' · '.$r->pax_adults.' '.((int) $r->pax_adults === 1 ? 'traveller' : 'travellers'));
    }

    protected function resolveRecord(int|string $key): Booking
    {
        return parent::resolveRecord($key)->load('package', 'travelers', 'payments.booking');
    }

    protected function getHeaderActions(): array
    {
        return [
            PackageBookingActions::markBalanceReceived(),
            HotelBookingActions::addNote(),
            HotelBookingActions::downloadInvoice(),
            ActionGroup::make([
                RecordManualRefundAction::make(),
                PackageBookingActions::cancelAndRefund(),
            ])->label('Cancel / refund')->icon('heroicon-o-ellipsis-vertical')->button()->color('gray'),
        ];
    }
}
