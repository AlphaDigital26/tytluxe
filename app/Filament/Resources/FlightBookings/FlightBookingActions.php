<?php

namespace App\Filament\Resources\FlightBookings;

use App\Models\Booking;
use App\Services\FlightBookingService;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackFlightClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Carbon;

/**
 * The buttons staff use on a flight booking, shared by the list and the
 * booking page. Each one calls the same FlightBookingService code the guest
 * flow uses, so staff actions get the same refund and safety rules.
 */
class FlightBookingActions
{
    /**
     * TripJack's Full Refund remarks checklist — these exact strings trigger
     * their automated refund handling (Auto Full Refund doc).
     */
    public const FULL_REFUND_REASONS = [
        'Flight Cancelled by Airline' => 'The airline cancelled the flight',
        'Airline rescheduled flight, revised timings are not suitable' => 'The airline changed the timings and the new time doesn\'t suit the guest',
        'Already cancelled by directly contacting airline customer support team' => 'The guest already cancelled directly with the airline',
        'Airline confirmed, refund is already processed' => 'The airline has confirmed the refund is already processed',
        'Refund under DGCA policy' => 'Refund under DGCA rules',
        'Personal loss or bereavement' => 'Death in the family / personal loss',
        'Passenger is medically unfit for travel' => 'The passenger is medically unfit to travel',
        'Refund under empowerment policy' => 'Refund under the airline\'s empowerment policy',
    ];

    /** $ability: a BookingPolicy ability — checkStatus, addNote, cancel, … */
    protected static function canManage(Booking $record, string $ability): bool
    {
        return (bool) auth('admin')->user()?->can($ability, $record);
    }

    /**
     * Asks TripJack about a pending full cancellation and finishes it if
     * TripJack has decided. Returns false when there is no reference to check
     * (the normal ticket check then runs instead).
     */
    protected static function checkPendingCancellation(Booking $record): bool
    {
        // Older bookings only have the reference in their notes.
        $ref = $record->flight_cancel_amendment_id
            ?: (preg_match('/CANCELLATION amendment \((\w+)\)/', (string) $record->admin_note, $m) ? $m[1] : null);
        if (! $ref) {
            return false;
        }

        try {
            $details = app(TripJackFlightClient::class)->amendmentDetails($ref);
        } catch (TripJackException $e) {
            Notification::make()->title('Couldn\'t reach the airline system')
                ->body('TripJack did not answer just now. Please try again in a few minutes.')->danger()->send();

            return true;
        }

        $status = $details['amendmentStatus'] ?? 'UNKNOWN';
        if (in_array($status, ['SUCCESS', 'REJECTED'], true)) {
            app(FlightBookingService::class)->finalizeCancellation($record, $details, true, 'CANCELLATION');
            $record->refresh();
            Notification::make()
                ->title($status === 'SUCCESS' ? 'Cancellation completed' : 'The airline refused the cancellation')
                ->body($status === 'SUCCESS'
                    ? 'TripJack has finished the cancellation. The refund to the guest has been started; the booking page shows the result.'
                    : 'TripJack did not accept the cancellation, so the ticket stays valid. Tell the guest, or contact TripJack support with reference '.$ref.'.')
                ->color($status === 'SUCCESS' ? 'success' : 'warning')->send();

            return true;
        }

        Notification::make()
            ->title('Cancellation still waiting at TripJack')
            ->body('TripJack has not finished this cancellation yet (status: '.ucwords(strtolower(str_replace('_', ' ', $status))).'). Call TripJack support and quote cancellation reference '.$ref.'. Once they finish it, click "Check status with airline" again and the refund goes to the guest.')
            ->warning()->persistent()->send();

        return true;
    }

    public static function checkStatus(): Action
    {
        return Action::make('checkStatus')
            ->label('Check status with airline')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            // Can confirm, cancel or refund the booking as a side effect, so
            // it needs the same permission as the other booking actions.
            ->visible(fn (Booking $record) => filled($record->tripjack_booking_id) && static::canManage($record, 'checkStatus'))
            ->action(function (Booking $record): void {
                // A cancellation is pending: the ticket itself still reads
                // "valid" until it completes, so check the cancellation.
                if ($record->cancellation_requested_at !== null && $record->status === 'confirmed' && static::checkPendingCancellation($record)) {
                    return;
                }

                try {
                    $details = app(TripJackFlightClient::class)->bookingDetails($record->tripjack_booking_id);
                } catch (TripJackException $e) {
                    // TripJack did answer: it has no booking under this number,
                    // so no ticket exists. Retrying won't change that.
                    if (str_contains(strtolower($e->getMessage()), 'no order found') && in_array($record->status, ['failed_needs_review', 'confirmed'], true) && blank($record->tripjack_flight_pnr)) {
                        $record->update([
                            'status' => 'failed_needs_review',
                            'manual_refund_due_at' => $record->manual_refund_due_at ?? now(),
                            'admin_note' => $record->adminNoteWith(now()->format('j M Y, g:i A').' — TripJack has no booking under '.$record->tripjack_booking_id.', so no ticket was issued. Confirm with TripJack support, then refund the guest in Razorpay and click "Record manual refund".'),
                        ]);
                        Notification::make()
                            ->title('TripJack has no booking under this number')
                            ->body('No ticket was issued for this booking. Confirm with TripJack support, then refund the guest from the Razorpay dashboard and click "Record manual refund". A note has been added to the booking.')
                            ->warning()->persistent()->send();

                        return;
                    }

                    Notification::make()
                        ->title('Couldn\'t reach the airline system')
                        ->body('TripJack did not answer just now. Please try again in a few minutes. ('.$e->getMessage().')')
                        ->danger()
                        ->send();

                    return;
                }

                $flights = app(FlightBookingService::class);
                $flights->storeItinerary($record, $details);
                if (in_array($record->status, ['confirmed', 'failed_needs_review'], true) && $record->cancellation_requested_at === null) {
                    $flights->applyBookingDetails($record, $details, app(RazorpayService::class), resolvingUncertain: $record->status === 'failed_needs_review');
                }
                $record->refresh();

                $airlineStatus = $details['order']['status'] ?? 'UNKNOWN';
                $meaning = match ($airlineStatus) {
                    'SUCCESS' => 'The ticket is issued and valid.',
                    'PENDING', 'IN_PROGRESS' => 'The airline is still issuing the ticket. Check again in a few minutes.',
                    'ON_HOLD' => 'The seat is reserved but not yet paid for.',
                    'CANCELLED' => 'The ticket is cancelled with the airline.',
                    'FAILED', 'ABORTED' => 'The airline could not issue this ticket. The guest\'s payment is refunded automatically.',
                    'UNCONFIRMED' => 'The reservation was released and is no longer held.',
                    default => 'The airline returned an unexpected status ('.$airlineStatus.'). Please contact TripJack support.',
                };

                Notification::make()
                    ->title('Airline status: '.ucwords(strtolower(str_replace('_', ' ', $airlineStatus))))
                    ->body($meaning)
                    ->color($airlineStatus === 'SUCCESS' ? 'success' : 'warning')
                    ->send();
            });
    }

    public static function cancelAndRefund(): Action
    {
        return Action::make('cancelFlight')
            ->label('Cancel & refund')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Booking $record) => $record->status === 'confirmed'
                && $record->cancellation_requested_at === null
                && filled($record->tripjack_booking_id)
                && static::canManage($record, 'cancel'))
            ->modalHeading('Cancel this flight booking')
            ->modalDescription('This cancels the whole booking (all passengers and all flights) with the airline. The refund is paid back to the guest\'s card automatically once the airline confirms — usually within a few minutes. This cannot be undone.')
            ->modalSubmitActionLabel('Yes, cancel this booking')
            ->modalCancelActionLabel('Go back')
            ->schema(function (Booking $record) {
                $flights = app(FlightBookingService::class);
                $scope = $flights->cancellationScope($record, 'full');
                $quote = $scope ? $flights->cancellationQuote($record, $scope['trips'], $scope['paxCounts']) : null;

                $quoteText = $quote
                    ? sprintf('Airline cancellation charges: ₹%s · Our service fee kept: ₹%s · Guest will get back about ₹%s', number_format($quote['airline_charges'], 2), number_format($quote['service_fee'], 2), number_format($quote['refund'], 2))
                    : 'The airline didn\'t give an estimate right now. The exact refund is worked out by the airline when you cancel.';

                return [
                    Radio::make('kind')
                        ->label('Why is this booking being cancelled?')
                        ->options([
                            'normal' => 'The guest wants to cancel (normal airline charges apply)',
                            'full_refund' => 'Special case — the guest should get a full refund',
                        ])
                        ->default('normal')
                        ->required()
                        ->live(),
                    Placeholder::make('quote')
                        ->label('Estimated refund')
                        ->content($quoteText)
                        ->visible(fn (Get $get) => $get('kind') === 'normal'),
                    Select::make('full_refund_reason')
                        ->label('Reason for the full refund')
                        ->options(self::FULL_REFUND_REASONS)
                        ->required(fn (Get $get) => $get('kind') === 'full_refund')
                        ->visible(fn (Get $get) => $get('kind') === 'full_refund')
                        ->helperText('The airline/TripJack checks this reason before approving a full refund.'),
                ];
            })
            ->action(function (array $data, Booking $record): void {
                $flights = app(FlightBookingService::class);
                $result = ($data['kind'] ?? 'normal') === 'full_refund'
                    ? $flights->submitFullRefund($record, (string) $data['full_refund_reason'])
                    : $flights->submitCancellation($record, remarks: 'Cancelled by TYT Luxe team on the guest\'s request.');

                $note = 'Cancellation requested by '.(auth('admin')->user()?->name ?? 'admin').' on '.now()->format('j M Y, g:i A').'.';
                if ($result['success']) {
                    $record->update(['admin_note' => trim(($record->admin_note ? $record->admin_note."\n" : '').$note)]);
                }

                Notification::make()
                    ->title($result['success'] ? 'Cancellation sent to the airline' : 'Cancellation could not be sent')
                    ->body($result['success']
                        ? 'The status will change to "Cancelled" and the refund will be paid automatically once the airline confirms.'
                        : ($result['message'] ?? 'A cancellation for this booking is already being processed.'))
                    ->color($result['success'] ? 'success' : 'danger')
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

    public static function downloadETicket(): Action
    {
        return Action::make('downloadETicket')
            ->label('Download e-ticket')
            ->icon('heroicon-o-ticket')
            ->color('gray')
            ->visible(fn (Booking $record) => $record->hasETicket())
            ->action(function (Booking $record) {
                $pdf = Pdf::loadView('pdf.e-ticket', ['booking' => $record])->setPaper('a4', 'portrait');

                return response()->streamDownload(fn () => print($pdf->output()), "e-ticket-{$record->reference}.pdf");
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
                Textarea::make('note')
                    ->label('Note')
                    ->rows(3)
                    ->required()
                    ->maxLength(1000),
            ])
            ->action(function (array $data, Booking $record): void {
                $stamp = Carbon::now()->format('j M Y, g:i A').' — '.(auth('admin')->user()?->name ?? 'admin').': ';
                // A note isn't booking activity — updated_at drives the
                // "taking too long" warnings, which a note must not reset.
                Booking::withoutTimestamps(fn () => $record->update(['admin_note' => $record->adminNoteWith($stamp.trim($data['note']))]));

                Notification::make()->title('Note added')->success()->send();
            });
    }
}
