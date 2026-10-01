<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Models\Booking;
use App\Services\Booking\BookingCancellationService;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\TripJackClient;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('guest_email')
                    ->searchable(),
                TextColumn::make('guest_phone')
                    ->searchable(),
                TextColumn::make('vertical')
                    ->badge(),
                TextColumn::make('hotel_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('room_type_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tripjack_booking_id')
                    ->searchable(),
                TextColumn::make('tripjack_hold_id')
                    ->searchable(),
                TextColumn::make('check_in')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_out')
                    ->date()
                    ->sortable(),
                TextColumn::make('flight_route')
                    ->searchable(),
                TextColumn::make('flight_journey_type')
                    ->label('Journey Type')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('flight_departure_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tripjack_flight_pnr')
                    ->label('PNR')
                    ->state(fn ($record) => collect($record->tripjack_flight_pnr ?? [])->implode(', ') ?: null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('flight_ssr_status')
                    ->label('Extras Status')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('flight_reissued_at')
                    ->label('Reissued')
                    ->dateTime('M j, Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pax_adults')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pax_children')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lead_guest_name')
                    ->label('Primary Guest')
                    ->searchable(),
                TextColumn::make('special_requests')
                    ->searchable(),
                TextColumn::make('base_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('offer_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('cancellation_reason')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                static::cancelAndRefundAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
                return $record->status === 'confirmed'
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
