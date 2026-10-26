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
        if (! $kind) {
            return;
        }

        // The team gets a hidden copy (config services.booking_emails) — or
        // the only copy when the guest left no email address.
        $team = config('services.booking_emails.team_copy');
        $guest = $booking->guest_email;

        if (filled($guest)) {
            $mail = Mail::to($guest);
            if (filled($team) && strcasecmp($team, $guest) !== 0) {
                $mail->bcc($team);
            }
            $mail->queue(new BookingUpdateMail($booking, $kind));
        } elseif (filled($team)) {
            Mail::to($team)->queue(new BookingUpdateMail($booking, $kind));
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

        // Group package: balance cleared (paid online, or marked received by staff).
        if ($booking->vertical === 'package' && $status === 'confirmed' && $booking->wasChanged('package_balance_amount')
            && (float) $booking->getOriginal('package_balance_amount') > 0 && (float) $booking->package_balance_amount <= 0) {
            return BookingUpdateMail::BALANCE_PAID;
        }

        // Group package: guest asked to cancel online (the team decides the
        // refund later). Not when staff cancel directly — that changes status
        // and sends the "cancelled" email instead.
        if ($booking->vertical === 'package' && $status === 'confirmed' && ! $booking->wasChanged('status')
            && $booking->wasChanged('cancellation_requested_at') && $booking->getOriginal('cancellation_requested_at') === null
            && $booking->cancellation_requested_at !== null) {
            return BookingUpdateMail::CANCEL_REQUESTED;
        }

        if (! $booking->wasChanged('status')) {
            return null;
        }

        return match (true) {
            ! $flight && $status === 'confirmed' => BookingUpdateMail::CONFIRMED,
            $status === 'cancelled' => BookingUpdateMail::CANCELLED,
            $status === 'refunded' => BookingUpdateMail::FAILED_REFUNDED,
            default => null,
        };
    }
}
