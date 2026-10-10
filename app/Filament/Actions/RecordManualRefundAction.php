<?php

namespace App\Filament\Actions;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Booking\ManualRefundService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;

/**
 * "Record manual refund" on hotel and flight bookings — for after staff
 * have refunded the guest themselves in the Razorpay dashboard. See
 * ManualRefundService; no money moves from here.
 */
class RecordManualRefundAction
{
    /**
     * @param  bool  $onlyWhenDue  list menus: show only on bookings flagged as
     *                             waiting for a refund, not on every paid one
     */
    public static function make(bool $onlyWhenDue = false): Action
    {
        return Action::make('recordManualRefund')
            ->label('Record manual refund')
            ->icon('heroicon-o-banknotes')
            ->color('warning')
            ->visible(fn (Booking $record) => (bool) auth('admin')->user()?->can('recordRefund', $record)
                && (! $onlyWhenDue || self::refundDue($record))
                && ManualRefundService::refundablePayments($record)->isNotEmpty())
            ->modalHeading('Record a refund you made in Razorpay')
            ->modalDescription('Use this after you have refunded the guest yourself in the Razorpay dashboard. It does not send any money — it records the refund here so the booking, the guest\'s page and the invoice show it, and clears the "refund needs action" warning.')
            ->modalSubmitActionLabel('Record refund')
            ->schema(function (Booking $record) {
                $payments = ManualRefundService::refundablePayments($record);

                return [
                    Select::make('payment_id')
                        ->label('Which payment did you refund?')
                        ->options($payments->mapWithKeys(fn (Payment $p) => [$p->id => self::paymentLabel($p)])->all())
                        ->default($payments->count() === 1 ? $payments->first()->id : null)
                        ->required()
                        ->live(),
                    TextInput::make('amount')
                        ->label('Amount refunded')
                        ->prefix($record->currency ?: 'INR')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->maxValue(fn (Get $get) => ManualRefundService::remaining($payments->firstWhere('id', (int) $get('payment_id')) ?? new Payment))
                        ->helperText('Cannot be more than what is still unrefunded on that payment.'),
                    TextInput::make('razorpay_refund_id')
                        ->label('Razorpay refund ID (optional)')
                        ->placeholder('rfnd_…')
                        ->maxLength(40),
                    Textarea::make('note')
                        ->label('Note (optional)')
                        ->rows(2)
                        ->maxLength(500),
                ];
            })
            ->action(function (array $data, Booking $record): void {
                $payment = $record->payments()->findOrFail($data['payment_id']);

                try {
                    app(ManualRefundService::class)->record(
                        $record,
                        $payment,
                        (float) $data['amount'],
                        auth('admin')->user()?->name ?? 'admin',
                        filled($data['razorpay_refund_id'] ?? null) ? trim($data['razorpay_refund_id']) : null,
                        $data['note'] ?? null,
                    );
                } catch (\InvalidArgumentException $e) {
                    Notification::make()->title('Refund not recorded')->body($e->getMessage())->danger()->send();

                    return;
                }

                Notification::make()->title('Refund recorded')->body('The booking and invoice now show this refund.')->success()->send();
            });
    }

    /** The booking is flagged as waiting for a refund someone must make by hand. */
    public static function refundDue(Booking $record): bool
    {
        return $record->manual_refund_due_at !== null
            || $record->status === 'failed_needs_review'
            || $record->flight_ssr_status === 'needs_review'
            || ($record->status === 'cancelled' && str_contains(strtolower((string) $record->cancellation_reason), 'manual'));
    }

    protected static function paymentLabel(Payment $payment): string
    {
        $what = match ($payment->purpose) {
            'flight_ssr' => 'Seats/meals/baggage payment',
            'flight_reissue' => 'Reschedule payment',
            default => 'Booking payment',
        };

        return sprintf('%s — %s %s paid, %s not refunded yet (%s)',
            $what,
            $payment->currency ?: 'INR',
            number_format((float) $payment->amount, 2),
            number_format(ManualRefundService::remaining($payment), 2),
            $payment->created_at?->format('j M Y'),
        );
    }
}
