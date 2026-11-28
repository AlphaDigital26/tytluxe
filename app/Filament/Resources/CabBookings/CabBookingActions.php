<?php

namespace App\Filament\Resources\CabBookings;

use App\Models\Booking;
use App\Services\CabBookingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * The buttons staff use on a cab booking, shared by the list and the booking
 * page. They call the same CabBookingService code as the guest pages, so the
 * refund rules are the same.
 */
class CabBookingActions
{
    protected static function canManage(Booking $record, string $ability): bool
    {
        return (bool) auth('admin')->user()?->can($ability, $record);
    }

    public static function checkStatus(): Action
    {
        return Action::make('checkStatus')
            ->label('Check status with TripJack')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->visible(fn (Booking $record) => filled($record->tripjack_booking_id) && static::canManage($record, 'checkStatus'))
            ->action(function (Booking $record): void {
                $live = app(CabBookingService::class)->refreshStatus($record);
                if ($live === null) {
                    Notification::make()->title('Couldn\'t reach TripJack')->body('TripJack did not answer just now. Please try again in a few minutes.')->danger()->send();

                    return;
                }

                Notification::make()
                    ->title('TripJack: '.str_replace('_', ' ', strtolower((string) ($live['status'] ?? 'unknown'))))
                    ->body(collect([
                        'Payment: '.strtolower((string) ($live['paymentStatus'] ?? '—')),
                        'Ride: '.str_replace('_', ' ', strtolower((string) ($live['rideStatus'] ?? '—'))),
                    ])->implode(' · '))
                    ->success()->send();
            });
    }

    public static function cancelAndRefund(): Action
    {
        return Action::make('cancelCab')
            ->label('Cancel & refund')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Booking $record) => $record->status === 'confirmed'
                && $record->cancellation_requested_at === null
                && filled($record->tripjack_booking_id)
                && static::canManage($record, 'cancel'))
            ->modalHeading('Cancel this cab booking')
            ->modalDescription('This cancels the cab with TripJack and refunds the guest their share automatically, following the cab\'s cancellation policy. This cannot be undone.')
            ->modalSubmitActionLabel('Yes, cancel this cab')
            ->modalCancelActionLabel('Go back')
            ->schema(function (Booking $record) {
                $quote = app(CabBookingService::class)->cancellationQuote($record);

                return [
                    Placeholder::make('quote')
                        ->label('Refund to the guest if cancelled now')
                        ->content($quote
                            ? sprintf('₹%s (%s%% of the cab fare%s). The online payment charge isn\'t refunded.', number_format($quote['refund'], 2), rtrim(rtrim(number_format($quote['percent'], 2), '0'), '.'), $quote['description'] ? ' — '.$quote['description'] : '')
                            : 'TripJack didn\'t give an estimate right now. The refund follows the cab\'s cancellation policy.'),
                ];
            })
            ->action(function (Booking $record): void {
                $result = app(CabBookingService::class)->cancel($record, 'by '.(auth('admin')->user()?->name ?? 'admin'));

                Notification::make()
                    ->title($result['ok'] ? 'Cancellation sent' : 'Couldn\'t cancel')
                    ->body($result['message'])
                    ->{$result['ok'] ? 'success' : 'danger'}()
                    ->send();
            });
    }

    public static function downloadInvoice(): Action
    {
        return Action::make('downloadInvoice')
            ->label('Download invoice')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->visible(fn (Booking $record) => $record->hasInvoice())
            ->action(function (Booking $record) {
                $booking = $record->loadMissing('travelers');
                $pdf = Pdf::loadView('pdf.invoice', compact('booking'))->setPaper('a4', 'portrait');

                return response()->streamDownload(fn () => print($pdf->output()), "invoice-{$record->reference}.pdf");
            });
    }

    public static function addNote(): Action
    {
        return Action::make('addNote')
            ->label('Add note')
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->visible(fn (Booking $record) => static::canManage($record, 'addNote'))
            ->modalHeading('Add an internal note')
            ->modalDescription('Notes are only visible to your team, never to the guest.')
            ->schema([
                Textarea::make('note')->label('Note')->rows(3)->required()->maxLength(1000),
            ])
            ->action(function (array $data, Booking $record): void {
                $stamp = Carbon::now()->format('j M Y, g:i A').' — '.(auth('admin')->user()?->name ?? 'admin').': ';
                Booking::withoutTimestamps(fn () => $record->update(['admin_note' => $record->adminNoteWith($stamp.trim($data['note']))]));

                Notification::make()->title('Note added')->success()->send();
            });
    }
}
