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

    /**
     * Cancels the booking with TripJack and resolves the refund.
     *
     * $refundOverride, when not null, is the exact amount an admin chose to
     * refund (0 to refund nothing) — this is what lets staff resolve the
     * "needs manual review" cases the guest-facing flow deliberately leaves
     * open. When null, falls back to the same automatic rule the guest flow
     * uses: refund in full only if TripJack's penalty is unambiguously zero.
     *
     * @return array{success: bool, message: string}
     */
    public function cancelAndResolve(Booking $booking, TripJackClient $client, RazorpayService $razorpay, ?float $refundOverride = null): array
    {
        $claimed = DB::transaction(function () use ($booking) {
            $fresh = Booking::whereKey($booking->id)->lockForUpdate()->first();
            if ($fresh->cancellation_requested_at !== null) {
                return false;
            }
            $fresh->update(['cancellation_requested_at' => now()]);

            return true;
        });

        if (! $claimed) {
            return ['success' => false, 'message' => 'A cancellation is already in progress for this booking.'];
        }

        try {
            $response = $client->cancelBooking($booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackErrorCatalog::describe($errorCode, "TripJack couldn't process this cancellation right now.");
            Log::channel('tripjack')->{$described['logLevel']}('admin_cancel_booking_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
            $booking->update(['cancellation_requested_at' => null]);

            return ['success' => false, 'message' => $described['message']];
        }

        if (! ($response['status']['success'] ?? false)) {
            $errorCode = TripJackErrorCatalog::codeFromResponse($response);
            $described = TripJackErrorCatalog::describe($errorCode, 'TripJack could not cancel this booking.');
            Log::channel('tripjack')->{$described['logLevel']}('admin_cancel_booking_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
            $booking->update(['cancellation_requested_at' => null]);

            return ['success' => false, 'message' => $described['message']];
        }

        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('admin_booking_details_failed', ['bookingId' => $booking->tripjack_booking_id, 'message' => $e->getMessage()]);

            return ['success' => true, 'message' => "Cancellation requested with TripJack, but the outcome couldn't be confirmed yet — re-run this action shortly to complete the refund."];
        }

        $liveStatus = $details['order']['status'] ?? null;
        if ($liveStatus !== 'CANCELLED') {
            $booking->update(['cancellation_reason' => "Cancellation requested — TripJack reports status: {$liveStatus}. Refund not yet processed."]);

            return ['success' => true, 'message' => "Cancellation requested. TripJack currently reports: {$liveStatus}. Their Ops team may take a while to finalize offline — re-run this action later to complete the refund."];
        }

        return $this->finalize($booking, $details, $razorpay, $refundOverride);
    }

    /**
     * @return array{success: bool, message: string}
     */
    protected function finalize(Booking $booking, array $bookingDetails, RazorpayService $razorpay, ?float $refundOverride): array
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
            try {
                $razorpay->refund($payment->razorpay_payment_id, $refundAmount);
                $payment->update([
                    'status' => $refundAmount >= (float) $payment->amount ? 'refunded' : 'partially_refunded',
                    'refund_amount' => $refundAmount,
                    'refund_reason' => $refundOverride !== null ? 'Manually refunded by admin.' : 'Cancellation — refund after the hotel\'s cancellation penalty.',
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
                ]);

                return ['success' => false, 'message' => 'Cancelled with TripJack, but the Razorpay refund failed: '.$e->getMessage()];
            }
        }

        $note = $penalty === null
            ? 'Cancelled by admin. Cancellation penalty could not be determined automatically.'
            : sprintf('Cancelled by admin with a cancellation penalty of %s %.2f. No refund issued.', $booking->currency, $penalty['amount']);

        $booking->update(['status' => 'cancelled', 'cancellation_reason' => $note]);
        Log::channel('tripjack')->info('admin_booking_cancelled_no_refund', ['booking_id' => $booking->id, 'penalty' => $penalty]);

        return ['success' => true, 'message' => 'Booking cancelled with TripJack. No refund was issued.'];
    }
}
