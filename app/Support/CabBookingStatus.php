<?php

namespace App\Support;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;

/**
 * Plain-English status for a cab booking, for the admin's Cab Bookings
 * screens, with a "what should I do?" hint (same idea as FlightBookingStatus).
 */
class CabBookingStatus
{
    /**
     * @return array{key: string, label: string, color: string, help: string}
     */
    public static function for(Booking $booking): array
    {
        $key = self::key($booking);

        return ['key' => $key] + self::META[$key];
    }

    public static function key(Booking $booking): string
    {
        $ride = strtoupper((string) ($booking->cab_details['live']['rideStatus'] ?? ''));

        return match (true) {
            $booking->status === 'failed_needs_review' && $booking->manual_refund_due_at !== null => 'failed_refund_due',
            $booking->status === 'failed_needs_review' => 'needs_review',
            $booking->status === 'cancelled' && $booking->manual_refund_due_at !== null => 'cancelled_refund_due',
            $booking->manual_refund_due_at !== null => 'refund_due',
            $booking->status === 'cancelled' => 'cancelled',
            $booking->status === 'confirmed' && $booking->cancellation_requested_at !== null => 'cancelling',
            $booking->status === 'confirmed' && in_array($ride, ['COMPLETED', 'ENDED', 'FINISHED'], true) => 'completed',
            $booking->status === 'confirmed' => 'confirmed',
            $booking->status === 'refunded' => 'refunded',
            $booking->status === 'payment_failed' => 'payment_failed',
            $booking->paymentAbandoned() => 'left_without_paying',
            default => 'awaiting_payment',
        };
    }

    /** As a query, for the "Needs your attention" tab and the menu badge. */
    public static function applyNeedsAttention(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('status', 'failed_needs_review')
            ->orWhereNotNull('manual_refund_due_at')
            ->orWhere(fn (Builder $q) => $q->where('status', 'confirmed')->whereNotNull('cancellation_requested_at')));
    }

    /** @return array<string, string> */
    public static function filterOptions(): array
    {
        return [
            'confirmed' => 'Confirmed',
            'pending_payment' => 'Waiting for payment',
            'payment_failed' => 'Payment failed',
            'failed_needs_review' => 'Needs your attention',
            'cancelled' => 'Cancelled',
            'refunded' => 'Booking failed, money refunded',
        ];
    }

    protected const META = [
        'confirmed' => [
            'label' => 'Confirmed',
            'color' => 'success',
            'help' => 'Paid by the guest and confirmed with TripJack (paid from your TripJack wallet). The driver\'s details are shared with the guest before pickup. Nothing to do.',
        ],
        'completed' => [
            'label' => 'Trip completed',
            'color' => 'success',
            'help' => 'The ride has finished. Nothing to do.',
        ],
        'cancelling' => [
            'label' => 'Cancellation in progress',
            'color' => 'warning',
            'help' => 'A cancellation was sent to TripJack but is not finished yet. Click "Check status with TripJack". Once TripJack confirms it, refund the guest the amount shown in the notes from the Razorpay dashboard and click "Record manual refund".',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'color' => 'gray',
            'help' => 'Cancelled, and any refund due has been paid back to the guest.',
        ],
        'cancelled_refund_due' => [
            'label' => 'Cancelled, refund needs action',
            'color' => 'danger',
            'help' => 'The cab was cancelled but the refund could not be paid automatically. Refund the guest from the Razorpay dashboard (the amount is in the notes), then click "Record manual refund".',
        ],
        'refund_due' => [
            'label' => 'Refund needs action',
            'color' => 'danger',
            'help' => 'A payment could not be refunded automatically. Read the notes, refund the guest from the Razorpay dashboard, then click "Record manual refund".',
        ],
        'failed_refund_due' => [
            'label' => 'Booking failed, refund needs action',
            'color' => 'danger',
            'help' => 'The cab could not be booked, and the automatic refund to the guest also failed. Refund the guest from the Razorpay dashboard (the amount is in the notes below), then click "Record manual refund".',
        ],
        'needs_review' => [
            'label' => 'Needs your attention',
            'color' => 'danger',
            'help' => 'The guest has paid, but TripJack did not answer while booking or paying for the cab, so we don\'t know if it went through. Read the notes below and click "Check status with TripJack". If TripJack has no paid booking, refund the guest from the Razorpay dashboard.',
        ],
        'refunded' => [
            'label' => 'Booking failed, money refunded',
            'color' => 'gray',
            'help' => 'TripJack could not book the cab, so the guest\'s payment was refunded in full automatically. The notes say why.',
        ],
        'payment_failed' => [
            'label' => 'Payment failed',
            'color' => 'gray',
            'help' => 'The guest\'s payment did not go through. No cab was booked and no money was taken.',
        ],
        'left_without_paying' => [
            'label' => 'Left without paying',
            'color' => 'gray',
            'help' => 'The guest reached the payment page over an hour ago and did not pay. Nothing was booked. Nothing to do.',
        ],
        'awaiting_payment' => [
            'label' => 'Waiting for payment',
            'color' => 'gray',
            'help' => 'The guest started booking but has not paid. Nothing is booked with TripJack yet.',
        ],
    ];
}
