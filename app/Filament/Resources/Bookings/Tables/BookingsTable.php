<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Filament\Actions\RecordManualRefundAction;
use App\Models\Booking;
use App\Services\Booking\BookingCancellationService;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\TripJackClient;
use App\Filament\Resources\Bookings\HotelBookingActions;
use App\Support\HotelBookingStatus;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Booking No.')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('lead_guest_name')
                    ->label('Guest')
                    ->description(fn (Booking $record) => $record->guest_phone)
                    ->searchable(['lead_guest_name', 'guest_email', 'guest_phone']),
                TextColumn::make('hotel.title')
                    ->label('Hotel')
                    ->description(fn (Booking $record) => $record->room_name)
                    ->placeholder('—')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('check_in')
                    ->label('Check-in')
                    ->date('D, j M Y')
                    ->description(fn (Booking $record) => $record->check_out ? 'Check-out '.\Illuminate\Support\Carbon::parse($record->check_out)->format('j M') : null)
                    ->sortable(),
                TextColumn::make('guests')
                    ->label('Guests')
                    ->state(fn (Booking $record) => (int) $record->pax_adults + (int) $record->pax_children),
                TextColumn::make('total_amount')
                    ->label('Guest paid')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (Booking $record) => HotelBookingStatus::for($record)['label'])
                    ->color(fn (Booking $record) => HotelBookingStatus::for($record)['color'])
                    ->tooltip(fn (Booking $record) => HotelBookingStatus::for($record)['help']),
                TextColumn::make('created_at')
                    ->label('Booked on')
                    ->dateTime('j M Y, g:i A')
                    ->sortable(),
                TextColumn::make('margin_amount')
                    ->label('Your earning')
                    ->money('INR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tripjack_booking_id')
                    ->label('TripJack ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('hotel_confirmation_number')
                    ->label('Hotel confirmation no.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('guest_email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search by booking no., guest, phone, email or hotel')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(HotelBookingStatus::filterOptions()),
                SelectFilter::make('hotel_id')
                    ->label('Hotel')
                    ->relationship('hotel', 'title')
                    ->searchable(),
                Filter::make('check_in')
                    ->label('Check-in date')
                    ->schema([
                        DatePicker::make('from')->label('Check-in from'),
                        DatePicker::make('until')->label('Check-in until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('check_in', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('check_in', '<=', $date))),
                Filter::make('booked_on')
                    ->label('Booked on')
                    ->schema([
                        DatePicker::make('from')->label('Booked from'),
                        DatePicker::make('until')->label('Booked until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make()->label('Open'),
                ActionGroup::make([
                    HotelBookingActions::checkStatus(),
                    HotelBookingActions::downloadInvoice(),
                    HotelBookingActions::addNote(),
                    EditAction::make()->label('Edit contact details'),
                    RecordManualRefundAction::make(onlyWhenDue: true),
                    static::cancelAndRefundAction(),
                ])->label('More')->button()->color('gray'),
            ])
            ->emptyStateHeading('No hotel bookings here')
            ->emptyStateDescription('Hotel bookings made by guests on the website will appear here.');
    }
    /**
     * Mirrors the guest-facing TripJack cancel-booking -> booking-details ->
     * Razorpay refund flow, but staff-triggered — see
     * App\Services\Booking\BookingCancellationService. Only shown for
     * confirmed bookings TripJack actually booked, and only to admins the
     * BookingPolicy already allows to update this record (Super Admin).
     */
    protected static function cancelAndRefundAction(): Action
    {
        return Action::make('cancelAndRefund')
            ->label('Cancel & Refund')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(function (Booking $record): bool {
                // Hotel API only — flights have their own screen and actions.
                return $record->vertical !== 'flight'
                    && $record->status === 'confirmed'
                    && filled($record->tripjack_booking_id)
                    && (bool) (auth('admin')->user()?->can('update', $record));
            })
            ->schema(function (Booking $record) {
                $preview = app(BookingCancellationService::class)->previewPenalty($record, app(TripJackClient::class));
                $penalty = $preview['penalty'];

                $penaltyText = match (true) {
                    $penalty === null => "Couldn't be determined right now — TripJack lookup failed or returned no data. Double-check manually before choosing a refund amount.",
                    $penalty['amount'] <= 0 => sprintf('%s 0.00 — currently within the free-cancellation window.', $record->currency),
                    default => sprintf('%s %.2f cancellation penalty currently applies.', $record->currency, $penalty['amount']),
                };

                return [
                    Placeholder::make('penalty_preview')
                        ->label("TripJack's current cancellation penalty")
                        ->content($penaltyText),
                    TextInput::make('refund_amount')
                        ->label('Refund amount to issue')
                        ->numeric()
                        ->minValue(0)
                        ->prefix($record->currency)
                        ->default($preview['estimatedRefund'])
                        ->helperText('Pre-filled using TripJack\'s penalty above. Adjust for a partial refund, or clear it to cancel without issuing any refund.'),
                ];
            })
            ->modalHeading('Cancel booking & process refund')
            ->modalDescription('This calls TripJack to cancel the reservation, then refunds the guest via Razorpay for the amount entered. This cannot be undone.')
            ->modalSubmitActionLabel('Confirm cancellation')
            ->action(function (array $data, Booking $record): void {
                $refundAmount = ($data['refund_amount'] ?? null) !== null && $data['refund_amount'] !== ''
                    ? (float) $data['refund_amount']
                    : null;

                $result = app(BookingCancellationService::class)->cancelAndResolve(
                    $record,
                    app(TripJackClient::class),
                    app(RazorpayService::class),
                    $refundAmount,
                );

                Notification::make()
                    ->title($result['success'] ? 'Cancellation processed' : 'Cancellation failed')
                    ->body($result['message'])
                    ->color($result['success'] ? 'success' : 'danger')
                    ->send();
            });
    }
}
