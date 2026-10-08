<?php

namespace App\Filament\Resources\Bookings;

use App\Http\Controllers\FrontendController;
use App\Models\Booking;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\TripJackClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * Staff buttons on a hotel booking, shared by the list and the booking page.
 * "Check status" runs the same refresh the guest's confirmation page and the
 * 5-minute scheduler use, so staff get the same refund/confirm rules.
 */
class HotelBookingActions
{
    public static function checkStatus(?\Closure $after = null): Action
    {
        return Action::make('checkStatus')
            ->label('Check status with TripJack')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->visible(fn (Booking $record) => filled($record->tripjack_booking_id))
            ->action(function (Booking $record) use ($after): void {
                $live = app(FrontendController::class)->refreshHotelBookingStatus($record, app(TripJackClient::class), app(RazorpayService::class));
                $record->refresh();

                if ($live === null) {
                    Notification::make()
                        ->title('Couldn\'t get an answer from TripJack')
                        ->body('TripJack did not reply just now. Please try again in a few minutes.')
                        ->danger()
                        ->send();

                    return;
                }

                $meaning = match ($live) {
                    'SUCCESS' => 'The hotel has confirmed this booking.',
                    'PENDING', 'IN_PROGRESS' => 'The hotel is still confirming. Check again in a few minutes.',
                    'ON_HOLD' => 'The room is reserved but not yet paid for.',
                    'CANCELLED' => 'The booking is cancelled with the hotel.',
                    'CANCELLATION_PENDING' => 'The hotel is processing the cancellation.',
                    'FAILED', 'ABORTED' => 'The hotel could not confirm this booking. The guest\'s payment is refunded automatically.',
                    default => 'TripJack returned an unexpected status ('.$live.'). Please contact TripJack support.',
                };

                Notification::make()
                    ->title('TripJack status: '.ucwords(strtolower(str_replace('_', ' ', $live))))
                    ->body($meaning)
                    ->color($live === 'SUCCESS' ? 'success' : 'warning')
                    ->send();

                if ($after) {
                    $after($record);
                }
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
                $booking = $record->loadMissing(['hotel.destination', 'roomType', 'travelers']);
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
            ->visible(fn (Booking $record) => (bool) auth('admin')->user()?->can('update', $record))
            ->modalHeading('Add an internal note')
            ->modalDescription('Notes are only visible to your team, never to the guest.')
            ->schema([
                Textarea::make('note')->label('Note')->rows(3)->required()->maxLength(1000),
            ])
            ->action(function (array $data, Booking $record): void {
                $stamp = Carbon::now()->format('j M Y, g:i A').' — '.(auth('admin')->user()?->name ?? 'admin').': ';
                $record->update(['admin_note' => trim(($record->admin_note ? $record->admin_note."\n" : '').$stamp.trim($data['note']))]);

                Notification::make()->title('Note added')->success()->send();
            });
    }
}
