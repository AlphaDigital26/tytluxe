<?php

namespace App\Support;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;

/**
 * Plain-English status for a group package booking in the admin, with a
 * "what should I do?" hint. Mirrors HotelBookingStatus / FlightBookingStatus.
 */
class PackageBookingStatus
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
        $manualRefund = str_contains(strtolower((string) $booking->cancellation_reason), 'manual');

        return match (true) {
            $booking->status === 'failed_needs_review' => 'needs_review',
            $booking->manual_refund_due_at !== null || ($booking->status === 'cancelled' && $manualRefund) => 'refund_due',
            $booking->status === 'confirmed' && $booking->cancellation_requested_at !== null => 'cancel_requested',
            $booking->status === 'confirmed' && (float) $booking->package_balance_amount > 0
                && $booking->package_balance_due_on && $booking->package_balance_due_on->lt(today()) => 'balance_overdue',
            $booking->status === 'confirmed' && (float) $booking->package_balance_amount > 0 => 'balance_due',
            $booking->status === 'confirmed' => 'paid',
            $booking->status === 'cancelled' => 'cancelled',
            $booking->status === 'refunded' => 'refunded',
            $booking->status === 'payment_failed' => 'payment_failed',
            $booking->paymentAbandoned() => 'left_without_paying',
            default => 'awaiting_payment',
        };
    }

    /** Same rule as the "needs attention" keys, as a query — for the tab and menu badge. */
    public static function applyNeedsAttention(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('status', 'failed_needs_review')
            ->orWhereNotNull('manual_refund_due_at')
            ->orWhere(fn (Builder $q) => $q->where('status', 'cancelled')->where('cancellation_reason', 'like', '%manual%'))
            ->orWhere(fn (Builder $q) => $q->where('status', 'confirmed')->whereNotNull('cancellation_requested_at'))
            ->orWhere(fn (Builder $q) => $q->where('status', 'confirmed')->whereNull('cancellation_requested_at')
                // Due today or tomorrow too, so the team can call the guest in time.
                ->where('package_balance_amount', '>', 0)->whereDate('package_balance_due_on', '<=', today()->addDay())));
    }

    /** @return array<string, string> raw status => plain words, for filters */
    public static function filterOptions(): array
    {
        return [
            'confirmed' => 'Booked (paid or part-paid)',
            'pending_payment' => 'Waiting for payment',
            'payment_failed' => 'Payment failed',
            'failed_needs_review' => 'Needs your attention',
            'cancelled' => 'Cancelled',
            'refunded' => 'Seats not available, refunded',
        ];
    }

    protected const META = [
        'paid' => [
            'label' => 'Fully paid',
            'color' => 'success',
            'help' => 'The guest has paid in full. Nothing to do.',
        ],
        'balance_due' => [
            'label' => 'Booking amount paid, balance due',
            'color' => 'info',
            'help' => 'The seats are booked. The guest pays the balance from their booking page; they get reminder emails before the due date. If they pay you directly, click "Mark balance as received".',
        ],
        'balance_overdue' => [
            'label' => 'Balance overdue',
            'color' => 'danger',
            'help' => 'The balance due date has passed. Contact the guest. If still unpaid on the departure day, the booking is cancelled automatically and the booking amount is kept.',
        ],
        'cancel_requested' => [
            'label' => 'Guest asked to cancel',
            'color' => 'danger',
            'help' => 'The guest asked to cancel. Decide the refund under your cancellation policy, then click "Cancel & refund". The seats stay booked until you do.',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'color' => 'gray',
            'help' => 'Cancelled. Any refund chosen has been paid back to the guest. The seats are free again.',
        ],
        'refund_due' => [
            'label' => 'Refund needs action',
            'color' => 'danger',
            'help' => 'A refund could not be paid automatically. Read the notes, refund the guest from the Razorpay dashboard, then click "Record manual refund".',
        ],
        'needs_review' => [
            'label' => 'Needs your attention',
            'color' => 'danger',
            'help' => 'Something went wrong with this booking. Read the notes below.',
        ],
        'refunded' => [
            'label' => 'Seats not available, refunded',
            'color' => 'gray',
            'help' => 'The seats were taken before the guest\'s payment completed, so the payment was refunded in full automatically.',
        ],
        'payment_failed' => [
            'label' => 'Payment failed',
            'color' => 'gray',
            'help' => 'The guest\'s payment did not go through. Nothing was booked and no money was taken.',
        ],
        'left_without_paying' => [
            'label' => 'Left without paying',
            'color' => 'gray',
            'help' => 'The guest reached the payment page over an hour ago and did not pay. Their seats were released. No money was taken. Nothing to do — you can call them if you would like to help them finish booking.',
        ],
        'awaiting_payment' => [
            'label' => 'Waiting for payment',
            'color' => 'gray',
            'help' => 'The guest started booking but has not paid. Their seats are held for 30 minutes, then released.',
        ],
    ];
}
