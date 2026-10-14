<?php

namespace App\Filament\Resources\PackageBookings;

use App\Models\Booking;
use App\Services\PackageBookingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

/**
 * Staff buttons on a group package booking (list and booking page). No
 * supplier is involved, so cancelling only moves money back through
 * Razorpay — the refund amount is the team's decision (client's rule).
 */
class PackageBookingActions
{
    public static function cancelAndRefund(): Action
    {
        return Action::make('cancelPackage')
            ->label('Cancel & refund')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Booking $record) => in_array($record->status, ['confirmed', 'pending_payment', 'payment_failed'], true)
                && (bool) auth('admin')->user()?->can('cancel', $record))
            ->modalHeading('Cancel this package booking')
            ->modalDescription('The booking is cancelled, its seats are freed, and the refund you enter is paid back to the guest\'s original payment method through Razorpay. The guest gets an email. This cannot be undone.')
            ->modalSubmitActionLabel('Yes, cancel and refund')
            ->schema(function (Booking $record) {
                $refundable = PackageBookingService::refundableAmount($record);

                return [
                    Placeholder::make('paid')
                        ->label('Guest has paid (not yet refunded)')
                        ->content('₹'.number_format($refundable, 2)
                            .($record->cancellation_requested_at ? ' · Guest asked to cancel on '.$record->cancellation_requested_at->format('j M Y') : '')),
                    TextInput::make('refund_amount')
                        ->label('Refund to the guest')
                        ->prefix('₹')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue($refundable)
                        ->default($refundable > 0 ? null : 0)
                        ->helperText('Enter 0 for no refund. Most you can refund: ₹'.number_format($refundable, 2).'.'),
                ];
            })
            ->action(function (array $data, Booking $record): void {
                $result = app(PackageBookingService::class)->cancelAndRefund(
                    $record,
                    (float) $data['refund_amount'],
                    auth('admin')->user()?->name ?? 'admin',
                );

                Notification::make()
                    ->title($result['success'] ? 'Booking cancelled' : 'Needs your attention')
                    ->body($result['message'])
                    ->color($result['success'] ? 'success' : 'danger')
                    ->send();
            });
    }

    public static function markBalanceReceived(): Action
    {
        return Action::make('markBalanceReceived')
            ->label('Mark balance as received')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->visible(fn (Booking $record) => $record->packageBalanceDue()
                && (bool) (auth('admin')->user()?->can('cancel', $record) || auth('admin')->user()?->can('recordRefund', $record)))
            ->modalHeading('Balance received outside the website?')
            ->modalDescription('Use this only if the guest paid the balance to you directly (bank transfer, UPI, cash). The booking becomes fully paid, the reminders stop and it will not be cancelled on the departure day. No money moves from here.')
            ->modalSubmitActionLabel('Mark as received')
            ->schema([
                Textarea::make('note')
                    ->label('How was it paid? (for your records)')
                    ->placeholder('e.g. UPI to company account, ref 4521…')
                    ->rows(2)
                    ->required()
                    ->maxLength(300),
            ])
            ->action(function (array $data, Booking $record): void {
                app(PackageBookingService::class)->markBalancePaidOffline($record, auth('admin')->user()?->name ?? 'admin', $data['note']);

                Notification::make()->title('Balance marked as received')->body('The booking is now fully paid.')->success()->send();
            });
    }
}
