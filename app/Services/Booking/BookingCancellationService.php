<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackClient;
use App\Services\TripJack\TripJackErrorCatalog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin-triggered mirror of the guest-facing cancel flow in
 * FrontendController (showCancellation/submitCancellation/finalizeCancellation)
 * — same TripJack cancel-booking -> booking-details -> Razorpay refund
 * sequence, but driven by a staff action instead of the guest's own click,
 * and letting staff override the automatic (penalty-scaled) refund amount.
 */
class BookingCancellationService
{
    /**
     * The penalty slab in force at $at (default now). Slab times are IST
     * with no offset, so they're parsed as Asia/Kolkata — parsing them in
     * the app's UTC put every boundary 5½ hours late. Callers finalizing a
     * cancellation pass the time it was *requested*, not when TripJack
     * finished processing it (which can be days later, in a dearer slab).
     */
    public function penaltyFor(array $bookingDetails, ?\DateTimeInterface $at = null): ?array
    {
        $op = $bookingDetails['itemInfos']['HOTEL']['hInfo']['ops'][0] ?? null;
        $slabs = $op['cnp']['pd'] ?? null;
        if (! is_array($slabs)) {
            return null;
        }

        $now = Carbon::instance($at ?? now());
        foreach ($slabs as $slab) {
            try {
                $from = Carbon::parse($slab['fdt'], 'Asia/Kolkata');
                $to = Carbon::parse($slab['tdt'], 'Asia/Kolkata');
            } catch (\Throwable) {
                continue;
            }
            if ($now->betweenIncluded($from, $to)) {
                return [
                    'amount' => (float) ($slab['am'] ?? 0),
                    'isRefundable' => (bool) ($op['cnp']['ifra'] ?? false),
                ];
            }
        }

        return null;
    }

    public function estimatedRefund(Booking $booking, array $penalty): ?float
    {
        if ((float) $booking->tripjack_total_price <= 0) {
            return null;
        }

        $penaltyRatio = min(1, $penalty['amount'] / (float) $booking->tripjack_total_price);

        return round((float) $booking->total_amount * (1 - $penaltyRatio), 2);
    }

    /**
     * Live-fetches TripJack's current cancellation penalty for a booking, for
     * display to an admin before they confirm the cancellation.
     *
     * @return array{penalty: ?array, estimatedRefund: ?float}
     */
    public function previewPenalty(Booking $booking, TripJackClient $client): array
    {
        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
            $penalty = $this->penaltyFor($details);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('admin_cancellation_penalty_lookup_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

            return ['penalty' => null, 'estimatedRefund' => null];
        }

        return [
            'penalty' => $penalty,
            'estimatedRefund' => $penalty !== null ? $this->estimatedRefund($booking, $penalty) : null,
        ];
    }

    /** The most that can still be refunded on the booking's own payment. */
    public static function refundableAmount(Booking $booking): float
    {
        $payment = $booking->payments()->whereIn('status', ['captured', 'partially_refunded'])->where('purpose', 'booking')->latest()->first();

        return $payment ? max(0.0, round((float) $payment->amount - (float) ($payment->refund_amount ?? 0), 2)) : 0.0;
    }

    /**
     * Cancels the booking with TripJack and resolves the refund.
     *
     * $refundOverride, when not null, is the exact amount an admin chose to
     * refund (0 to refund nothing) — this is what lets staff resolve the
     * "needs manual review" cases the guest-facing flow deliberately leaves
     * open. It is saved on the booking (admin_refund_amount), so whichever
     * path finishes the cancellation later — this action run again, the
     * guest's page or the scheduler — refunds that amount. When null, falls
     * back to the automatic penalty-based rule the guest flow uses.
     *
     * Run again while TripJack is still processing, it doesn't re-send the
     * cancellation — it re-checks TripJack and finishes it if it's done.
     *
     * @return array{success: bool, message: string}
     */
    public function cancelAndResolve(Booking $booking, TripJackClient $client, RazorpayService $razorpay, ?float $refundOverride = null): array
    {
        $refundable = self::refundableAmount($booking);
        if ($refundOverride !== null && $refundOverride > $refundable) {
            return ['success' => false, 'message' => sprintf("The refund can't be more than %s %.2f — what the guest paid, less anything already refunded.", $booking->currency, $refundable)];
        }

        $claim = DB::transaction(function () use ($booking, $refundOverride) {
            $fresh = Booking::whereKey($booking->id)->lockForUpdate()->first();
            if ($fresh->status !== 'confirmed') {
                return 'done';
            }
            $alreadySent = $fresh->cancellation_requested_at !== null;
            $fresh->update(array_filter([
                'cancellation_requested_at' => $alreadySent ? null : now(),
                'admin_refund_amount' => $refundOverride,
            ], fn ($v) => $v !== null));

            return $alreadySent ? 'resume' : 'new';
        });

        if ($claim === 'done') {
            return ['success' => false, 'message' => 'This booking is no longer active, so there is nothing to cancel.'];
        }

        if ($claim === 'new') {
            try {
                $response = $client->cancelBooking($booking->tripjack_booking_id);
            } catch (TripJackException $e) {
                $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
                $described = TripJackErrorCatalog::describe($errorCode, "TripJack couldn't process this cancellation right now.");
                Log::channel('tripjack')->{$described['logLevel']}('admin_cancel_booking_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
                $booking->update(['cancellation_requested_at' => null, 'admin_refund_amount' => null]);

                return ['success' => false, 'message' => $described['message']];
            }

            if (! ($response['status']['success'] ?? false)) {
                $errorCode = TripJackErrorCatalog::codeFromResponse($response);
                $described = TripJackErrorCatalog::describe($errorCode, 'TripJack could not cancel this booking.');
                Log::channel('tripjack')->{$described['logLevel']}('admin_cancel_booking_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
                $booking->update(['cancellation_requested_at' => null, 'admin_refund_amount' => null]);

                return ['success' => false, 'message' => $described['message']];
            }
        }

        $later = 'It will finish by itself once TripJack confirms (the site checks every few minutes), or click "Finish cancellation & refund" to check again. The refund amount you chose is kept.';

        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('admin_booking_details_failed', ['bookingId' => $booking->tripjack_booking_id, 'message' => $e->getMessage()]);

            return ['success' => true, 'message' => "Cancellation sent to TripJack, but its outcome couldn't be checked just now. {$later}"];
        }

        $liveStatus = $details['order']['status'] ?? null;
        if ($liveStatus !== 'CANCELLED') {
            $booking->update(['cancellation_reason' => "Cancellation requested — TripJack reports status: {$liveStatus}. Refund not yet processed."]);

            return ['success' => true, 'message' => "Cancellation sent. TripJack currently reports: {$liveStatus} — their team may take a while to finish it. {$later}"];
        }

        return $this->finalize($booking, $details, $razorpay);
    }

    /**
     * Finishes a cancellation TripJack has confirmed: refunds the amount an
     * admin chose (admin_refund_amount, or $refundOverride), else the
     * automatic penalty-based amount. Locks the booking first, so the guest
     * page, the scheduler and an admin finishing it at the same moment can
     * never refund twice.
     *
     * @return array{success: bool, message: string}
     */
    public function finalize(Booking $booking, array $bookingDetails, RazorpayService $razorpay, ?float $refundOverride = null): array
    {
        $result = DB::transaction(function () use ($booking, $bookingDetails, $razorpay, $refundOverride) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();
            if ($locked->status === 'cancelled') {
                return ['success' => true, 'message' => 'This booking is already cancelled.'];
            }

            return $this->finalizeLocked($locked, $bookingDetails, $razorpay, $refundOverride ?? ($locked->admin_refund_amount !== null ? (float) $locked->admin_refund_amount : null));
        });
        $booking->refresh();

        return $result;
    }

    /**
     * @return array{success: bool, message: string}
     */
    protected function finalizeLocked(Booking $booking, array $bookingDetails, RazorpayService $razorpay, ?float $refundOverride): array
    {
        $penalty = $this->penaltyFor($bookingDetails, $booking->cancellation_requested_at ?? now());
        $payment = $booking->payments()->where('status', 'captured')->latest()->first();

        // Default: what the guest paid, minus the penalty's share of
        // TripJack's price (full refund when the penalty is zero).
        $refundAmount = $refundOverride;
        if ($refundAmount === null && $penalty !== null && $payment && (float) $booking->tripjack_total_price > 0) {
            $penaltyRatio = min(1, max(0, $penalty['amount'] / (float) $booking->tripjack_total_price));
            $refundAmount = round((float) $payment->amount * (1 - $penaltyRatio), 2);
        }

        if ($payment && $refundAmount !== null && $refundAmount > 0) {
            $refundAmount = min($refundAmount, round((float) $payment->amount, 2));
            try {
                $razorpay->refund($payment->razorpay_payment_id, $refundAmount);
                $payment->update([
                    'status' => $refundAmount >= (float) $payment->amount ? 'refunded' : 'partially_refunded',
                    'refund_amount' => $refundAmount,
                    'refund_reason' => $refundOverride !== null ? 'Refund amount chosen by admin.' : 'Cancellation — refund after the hotel\'s cancellation penalty.',
                ]);
                $booking->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => sprintf('Cancelled. Refunded %s %.2f.', $booking->currency, $refundAmount),
                ]);
                Log::channel('tripjack')->info('admin_booking_cancelled_and_refunded', ['booking_id' => $booking->id, 'amount' => $refundAmount]);

                return ['success' => true, 'message' => "Booking cancelled and {$booking->currency} {$refundAmount} refunded."];
            } catch (\Throwable $e) {
                Log::channel('tripjack')->critical('admin_cancellation_refund_failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
                $booking->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Cancelled — refund attempt failed and needs manual processing: '.$e->getMessage(),
                    'manual_refund_due_at' => now(),
                ]);

                return ['success' => false, 'message' => 'Cancelled with TripJack, but the Razorpay refund failed: '.$e->getMessage().' Refund the guest in Razorpay, then click "Record manual refund".'];
            }
        }

        $note = match (true) {
            $refundOverride !== null => 'Cancelled by admin. No refund issued.',
            $penalty === null => 'Cancelled by admin. Cancellation penalty could not be determined automatically.',
            default => sprintf('Cancelled by admin with a cancellation penalty of %s %.2f. No refund issued.', $booking->currency, $penalty['amount']),
        };

        $booking->update(['status' => 'cancelled', 'cancellation_reason' => $note]);
        Log::channel('tripjack')->info('admin_booking_cancelled_no_refund', ['booking_id' => $booking->id, 'penalty' => $penalty]);

        return ['success' => true, 'message' => 'Booking cancelled with TripJack. No refund was issued.'];
    }
}
