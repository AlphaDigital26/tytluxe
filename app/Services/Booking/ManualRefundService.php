<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Records a refund staff made themselves in the Razorpay dashboard (after an
 * automatic refund failed or couldn't be worked out). It moves no money —
 * it updates the payment's refunded amount, so invoices and guest messages
 * are right, and clears the booking's "needs attention" refund flags.
 */
class ManualRefundService
{
    /** What is still refundable on a payment the guest actually paid. */
    public static function remaining(Payment $payment): float
    {
        if (! in_array($payment->status, ['captured', 'partially_refunded'], true)) {
            return 0.0;
        }

        return max(0.0, round((float) $payment->amount - (float) ($payment->refund_amount ?? 0), 2));
    }

    /** @return \Illuminate\Support\Collection<int, Payment> */
    public static function refundablePayments(Booking $booking)
    {
        return $booking->payments()->get()->filter(fn (Payment $p) => self::remaining($p) > 0)->values();
    }

    public function record(Booking $booking, Payment $payment, float $amount, string $staff, ?string $razorpayRefundId = null, ?string $note = null): void
    {
        DB::transaction(function () use ($booking, $payment, $amount, $staff, $razorpayRefundId, $note) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            $remaining = self::remaining($payment);
            $amount = round($amount, 2);
            if ($payment->booking_id !== $booking->id || $amount <= 0 || $amount > $remaining) {
                throw new InvalidArgumentException("The refund must be between 0.01 and {$remaining}.");
            }

            $totalRefunded = round((float) ($payment->refund_amount ?? 0) + $amount, 2);
            $fully = $totalRefunded >= (float) $payment->amount - 0.01;
            $payment->update([
                'status' => $fully ? 'refunded' : 'partially_refunded',
                'refund_amount' => $totalRefunded,
                'refund_reason' => trim('Refunded by '.$staff.' in the Razorpay dashboard.'.($razorpayRefundId ? ' Refund ID: '.$razorpayRefundId.'.' : '')),
            ]);

            $updates = ['manual_refund_due_at' => null];

            // The booking itself failed and its payment is now fully back with the guest.
            if ($booking->status === 'failed_needs_review' && $fully && ! in_array($payment->purpose, ['flight_ssr', 'flight_reissue'], true)) {
                $updates['status'] = 'refunded';
            }
            // Clears the "Cancelled, refund needs action" flag, which keys on
            // the word "manual" in the staff reason.
            if ($booking->status === 'cancelled' && str_contains(strtolower((string) $booking->cancellation_reason), 'manual')) {
                $updates['cancellation_reason'] = sprintf('Cancelled. Refunded %s %s by our team.', $booking->currency ?: 'INR', number_format($totalRefunded, 2, '.', ''));
            }
            if ($booking->flight_ssr_status === 'needs_review' && $payment->purpose === 'flight_ssr') {
                $updates['flight_ssr_status'] = $fully ? 'refunded' : 'partially_confirmed';
            }

            $updates['admin_note'] = $booking->adminNoteWith(sprintf(
                '%s — %s: recorded a refund of %s %s made in Razorpay%s.%s',
                now()->format('j M Y, g:i A'),
                $staff,
                $booking->currency ?: 'INR',
                number_format($amount, 2),
                $razorpayRefundId ? ' (refund ID '.$razorpayRefundId.')' : '',
                $note ? ' '.trim($note) : '',
            ));
            $booking->update($updates);

            Log::channel('tripjack')->info('manual_refund_recorded', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'amount' => $amount, 'by' => $staff]);
        });
    }
}
