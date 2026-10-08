<?php

namespace App\Observers;

use App\Mail\BookingUpdateMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;

/**
 * Emails the guest when their booking reaches a state they need to know
 * about. Watching the model (rather than each place that changes a status)
 * covers every path: guest pages, background jobs, the scheduler and the
 * admin panel.
 *
 * Deliberately silent for failed_needs_review: a Book that timed out usually
 * resolves to confirmed within minutes, and a worrying email followed by a
 * confirmation does more harm than good — the confirmation page explains it.
 */
class BookingObserver
{
    public function updated(Booking $booking): void
    {
        $kind = $this->kindFor($booking);

        if ($kind && filled($booking->guest_email)) {
            Mail::to($booking->guest_email)->queue(new BookingUpdateMail($booking, $kind));
        }
    }

    protected function kindFor(Booking $booking): ?string
    {
        $flight = $booking->vertical === 'flight';
        $status = $booking->status;

        // Flights: "booked" means ticketed — sent when the PNR first arrives
        // (Booking Details SUCCESS), not when status turns confirmed right
        // after Book, so the email carries the PNR and ticket numbers. A
        // reschedule clears the PNR and fills it again with the new one.
        if ($flight && $status === 'confirmed' && $booking->wasChanged('tripjack_flight_pnr')
            && blank($booking->getOriginal('tripjack_flight_pnr')) && filled($booking->tripjack_flight_pnr)) {
            return $booking->flight_reissued_at !== null ? BookingUpdateMail::RESCHEDULED : BookingUpdateMail::CONFIRMED;
        }

        // Seats/meals/baggage bought after booking, once the airline has
        // answered (needs_review is for staff, not the guest).
        if ($flight && $booking->wasChanged('flight_ssr_status')
            && in_array($booking->flight_ssr_status, ['confirmed', 'partially_confirmed', 'refunded', 'failed'], true)) {
            return BookingUpdateMail::EXTRAS;
        }

        if (! $booking->wasChanged('status')) {
            return null;
        }

        return match (true) {
            ! $flight && $status === 'confirmed' => BookingUpdateMail::CONFIRMED,
            $flight && $status === 'on_hold' => BookingUpdateMail::HELD,
            $flight && $status === 'hold_expired' => BookingUpdateMail::HOLD_EXPIRED,
            $status === 'cancelled' => BookingUpdateMail::CANCELLED,
            $status === 'refunded' => BookingUpdateMail::FAILED_REFUNDED,
            default => null,
        };
    }
}
