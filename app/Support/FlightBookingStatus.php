<?php

namespace App\Support;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Plain-English status for a flight booking, for the admin's Flight Bookings
 * screens. bookings.status alone doesn't say enough (e.g. 'confirmed' is
 * also the status while a cancellation is being processed), so this reads
 * the related columns too and adds a "what should I do?" hint.
 */
class FlightBookingStatus
{
    /** A cancellation still unresolved after this long needs a person to look. */
    public const STUCK_CANCELLATION_HOURS = 2;

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
            $booking->status === 'cancelled' && $manualRefund => 'cancelled_refund_pending',
            $booking->status === 'cancelled' => 'cancelled',
            $booking->status === 'confirmed' && $booking->cancellation_requested_at !== null => self::isStuck($booking) ? 'cancellation_stuck' : 'cancelling',
            $booking->status === 'confirmed' && blank($booking->tripjack_flight_pnr) => 'ticketing',
            $booking->status === 'confirmed' => 'ticketed',
            $booking->status === 'on_hold' => 'reserved',
            $booking->status === 'hold_expired' => 'reservation_expired',
            $booking->status === 'refunded' => 'refunded',
            $booking->status === 'payment_failed' => 'payment_failed',
            default => 'awaiting_payment',
        };
    }

    public static function needsAttention(Booking $booking): bool
    {
        return in_array(self::key($booking), ['needs_review', 'cancelled_refund_pending', 'cancellation_stuck'], true)
            || $booking->flight_ssr_status === 'needs_review';
    }

    /** Same rule as needsAttention(), as a query — for the tab and dashboard counts. */
    public static function applyNeedsAttention(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('status', 'failed_needs_review')
            ->orWhere('flight_ssr_status', 'needs_review')
            ->orWhere(fn (Builder $q) => $q->where('status', 'cancelled')->where('cancellation_reason', 'like', '%manual%'))
            ->orWhere(fn (Builder $q) => $q->where('status', 'confirmed')
                ->whereNotNull('cancellation_requested_at')
                ->where('cancellation_requested_at', '<', now()->subHours(self::STUCK_CANCELLATION_HOURS))));
    }

    /**
     * Status filter options in plain words, keyed by the raw status column.
     *
     * @return array<string, string>
     */
    public static function filterOptions(): array
    {
        return [
            'confirmed' => 'Confirmed / ticketed',
            'on_hold' => 'Reserved, not paid yet',
            'pending_payment' => 'Waiting for payment',
            'payment_failed' => 'Payment failed',
            'failed_needs_review' => 'Needs your attention',
            'cancelled' => 'Cancelled',
            'refunded' => 'Booking failed, money refunded',
            'hold_expired' => 'Reservation expired',
        ];
    }

    protected static function isStuck(Booking $booking): bool
    {
        return Carbon::parse($booking->cancellation_requested_at)->lt(now()->subHours(self::STUCK_CANCELLATION_HOURS));
    }

    protected const META = [
        'ticketed' => [
            'label' => 'Confirmed',
            'color' => 'success',
            'help' => 'Paid and ticketed by the airline. Nothing to do.',
        ],
        'ticketing' => [
            'label' => 'Paid, ticket being issued',
            'color' => 'warning',
            'help' => 'The guest has paid and the airline is issuing the ticket. This normally takes a few minutes and updates by itself. If it still shows this after an hour, click "Check status with airline".',
        ],
        'cancelling' => [
            'label' => 'Cancellation in progress',
            'color' => 'warning',
            'help' => 'The airline is processing a cancellation. The refund is paid to the guest automatically once it is done.',
        ],
        'cancellation_stuck' => [
            'label' => 'Cancellation taking too long',
            'color' => 'danger',
            'help' => 'A cancellation was requested over 2 hours ago and the airline has not finished it. Click "Check status with airline"; if it does not change, contact TripJack support with the TripJack booking ID.',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'color' => 'gray',
            'help' => 'Cancelled, and any refund due has been paid back to the guest.',
        ],
        'cancelled_refund_pending' => [
            'label' => 'Cancelled, refund needs action',
            'color' => 'danger',
            'help' => 'The ticket was cancelled but the refund could not be paid automatically. Please refund the guest from the Razorpay dashboard and add a note here.',
        ],
        'needs_review' => [
            'label' => 'Needs your attention',
            'color' => 'danger',
            'help' => 'Something went wrong (for example, the airline did not reply in time). The site keeps checking automatically, but please look at the notes below and click "Check status with airline". Contact TripJack support if it stays like this.',
        ],
        'reserved' => [
            'label' => 'Reserved, not paid yet',
            'color' => 'info',
            'help' => 'The guest has reserved a seat without paying. It is released automatically if they do not pay before the deadline.',
        ],
        'reservation_expired' => [
            'label' => 'Reservation expired',
            'color' => 'gray',
            'help' => 'The guest did not pay in time, so the reserved seat was released. No money was taken.',
        ],
        'refunded' => [
            'label' => 'Booking failed, money refunded',
            'color' => 'gray',
            'help' => 'The airline could not issue the ticket, so the guest\'s payment was refunded in full automatically.',
        ],
        'payment_failed' => [
            'label' => 'Payment failed',
            'color' => 'gray',
            'help' => 'The guest\'s payment did not go through. No ticket was booked and no money was taken.',
        ],
        'awaiting_payment' => [
            'label' => 'Waiting for payment',
            'color' => 'gray',
            'help' => 'The guest started booking but has not paid. Nothing is booked with the airline yet.',
        ],
    ];
}
