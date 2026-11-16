<?php

namespace App\Support;

use App\Models\Payment;
use App\Services\Payment\RazorpayService;
use Illuminate\Support\Facades\Log;

/**
 * A real payment the booking can no longer use: a second payment from an
 * old tab after the booking was already paid, a payment for a booking that
 * was cancelled meanwhile, or extras/reschedule choices that changed after
 * this order was made. The guest gets it all back; the booking itself is
 * left exactly as it is.
 */
class UnusedPayment
{
    public static function refund(Payment $payment, string $why): void
    {
        $booking = $payment->booking;
        $amount = 'INR '.number_format((float) $payment->amount, 2);

        try {
            app(RazorpayService::class)->refund((string) $payment->razorpay_payment_id, (float) $payment->amount);
            $payment->update(['status' => 'refunded', 'refund_amount' => $payment->amount, 'refund_reason' => $why]);
            $booking->update(['admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A')." — {$why} The extra payment of {$amount} was refunded automatically.")]);
            Log::channel('tripjack')->warning('unused_payment_refunded', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $why]);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'captured', 'refund_reason' => $why]);
            $booking->update([
                'manual_refund_due_at' => now(),
                'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A')." — {$why} The automatic refund of this extra payment ({$amount}) failed ({$e->getMessage()}). Refund it in the Razorpay dashboard, then click \"Record manual refund\"."),
            ]);
            Log::channel('tripjack')->critical('unused_payment_refund_failed', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $why, 'error' => $e->getMessage()]);
        }
    }
}
