<?php

namespace App\Support;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Plain-English status for a hotel booking in the admin, with a "what
 * should I do?" hint. Mirrors FlightBookingStatus for hotel statuses.
 */
class HotelBookingStatus
{
    /** A cancellation or hotel confirmation still unresolved after this long needs a person. */
    public const STUCK_HOURS = 2;

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
        $manualRefund = str_contains(strtolower((string) $booking->cancellation_reason), 'manual');
        $stuck = fn ($since) => $since && Carbon::parse($since)->lt(now()->subHours(self::STUCK_HOURS));

        return match (true) {
            $booking->status === 'failed_needs_review' => 'needs_review',
            $booking->status === 'cancelled' && ($manualRefund || $booking->manual_refund_due_at !== null) => 'cancelled_refund_pending',
            $booking->manual_refund_due_at !== null => 'refund_due',
            $booking->status === 'cancelled' => 'cancelled',
            $booking->status === 'confirmed' && $booking->cancellation_requested_at !== null => $stuck($booking->cancellation_requested_at) ? 'cancellation_stuck' : 'cancelling',
            $booking->status === 'confirmed' => 'confirmed',
            $booking->status === 'pending_confirmation' => $stuck($booking->updated_at) ? 'confirmation_stuck' : 'awaiting_hotel',
            $booking->status === 'on_hold' => 'on_hold',
            $booking->status === 'hold_expired' => 'hold_expired',
            $booking->status === 'refunded' => 'refunded',
            $booking->status === 'payment_failed' => 'payment_failed',
            default => 'awaiting_payment',
        };
    }

    /** Same rule as the "needs attention" keys, as a query — for the tab, badge and dashboard. */
    public static function applyNeedsAttention(Builder $query): Builder
    {
        $cutoff = now()->subHours(self::STUCK_HOURS);

        return $query->where(fn (Builder $q) => $q
            ->where('status', 'failed_needs_review')
            ->orWhereNotNull('manual_refund_due_at')
            ->orWhere(fn (Builder $q) => $q->where('status', 'cancelled')->where('cancellation_reason', 'like', '%manual%'))
            ->orWhere(fn (Builder $q) => $q->where('status', 'confirmed')->whereNotNull('cancellation_requested_at')->where('cancellation_requested_at', '<', $cutoff))
            ->orWhere(fn (Builder $q) => $q->where('status', 'pending_confirmation')->where('updated_at', '<', $cutoff)));
    }

    /** @return array<string, string> raw status => plain words, for filters */
    public static function filterOptions(): array
    {
        return [
            'confirmed' => 'Confirmed',
            'pending_confirmation' => 'Paid, waiting for the hotel',
            'pending_payment' => 'Waiting for payment',
            'payment_failed' => 'Payment failed',
            'failed_needs_review' => 'Needs your attention',
            'cancelled' => 'Cancelled',
            'refunded' => 'Booking failed, money refunded',
            'on_hold' => 'Reserved, not paid yet',
            'hold_expired' => 'Reservation expired',
        ];
    }

    protected const META = [
        'confirmed' => [
            'label' => 'Confirmed',
            'color' => 'success',
            'help' => 'Paid and confirmed by the hotel. Nothing to do.',
        ],
        'awaiting_hotel' => [
            'label' => 'Paid, waiting for the hotel',
            'color' => 'warning',
            'help' => 'The guest has paid and the hotel is confirming the room. This updates by itself within a few minutes.',
        ],
        'confirmation_stuck' => [
            'label' => 'Hotel confirmation taking too long',
            'color' => 'danger',
            'help' => 'The hotel has not confirmed for over 2 hours. Click "Check status with TripJack". If it does not change, contact TripJack support with the TripJack booking ID. If TripJack has no record of the booking, the guest is refunded automatically.',
        ],
        'cancelling' => [
            'label' => 'Cancellation in progress',
            'color' => 'warning',
            'help' => 'The hotel is processing a cancellation. The refund is paid to the guest automatically once it is done.',
        ],
        'cancellation_stuck' => [
            'label' => 'Cancellation taking too long',
            'color' => 'danger',
            'help' => 'A cancellation was requested over 2 hours ago and is not finished. Click "Check status with TripJack"; if it does not change, contact TripJack support.',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'color' => 'gray',
            'help' => 'Cancelled, and any refund due has been paid back to the guest.',
        ],
        'cancelled_refund_pending' => [
            'label' => 'Cancelled, refund needs action',
            'color' => 'danger',
            'help' => 'The booking was cancelled but the refund could not be paid automatically. Refund the guest from the Razorpay dashboard, then click "Record manual refund".',
        ],
        'refund_due' => [
            'label' => 'Refund needs action',
            'color' => 'danger',
            'help' => 'A payment could not be refunded automatically. Read the notes, refund the guest from the Razorpay dashboard, then click "Record manual refund".',
        ],
        'needs_review' => [
            'label' => 'Needs your attention',
            'color' => 'danger',
            'help' => 'Something went wrong with this booking. Read the notes below, click "Check status with TripJack", and contact TripJack support if it stays like this.',
        ],
        'on_hold' => [
            'label' => 'Reserved, not paid yet',
            'color' => 'info',
            'help' => 'The room is reserved but the guest has not paid yet. It is released automatically if they do not pay before the deadline.',
        ],
        'hold_expired' => [
            'label' => 'Reservation expired',
            'color' => 'gray',
            'help' => 'The guest did not pay in time, so the reservation was released. No money was taken.',
        ],
        'refunded' => [
            'label' => 'Booking failed, money refunded',
            'color' => 'gray',
            'help' => 'The hotel could not confirm the room, so the guest\'s payment was refunded in full automatically.',
        ],
        'payment_failed' => [
            'label' => 'Payment failed',
            'color' => 'gray',
            'help' => 'The guest\'s payment did not go through. Nothing was booked and no money was taken.',
        ],
        'awaiting_payment' => [
            'label' => 'Waiting for payment',
            'color' => 'gray',
            'help' => 'The guest started booking but has not paid. Nothing is booked with the hotel yet.',
        ],
    ];
}
