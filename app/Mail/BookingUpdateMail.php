<?php

namespace App\Mail;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The guest's email for a booking event — sent by BookingObserver whatever
 * caused the change (guest, background job, scheduler or admin). TripJack
 * never emails passengers (Flights FAQ: notifications go to the partner
 * only), so this is the guest's only copy of their booking details.
 */
class BookingUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public const CONFIRMED = 'confirmed';

    public const RESCHEDULED = 'rescheduled';



    public const CANCELLED = 'cancelled';

    public const FAILED_REFUNDED = 'failed_refunded';

    /** Group package: the balance after the booking amount has been paid. */
    public const BALANCE_PAID = 'balance_paid';

    /** Group package: the guest asked to cancel online; the team will confirm. */
    public const CANCEL_REQUESTED = 'cancel_requested';

    /** Group package: the balance falls due soon (sent by packages:balance-reminders). */
    public const BALANCE_REMINDER = 'balance_reminder';

    /** Seats/meals/baggage bought after booking — wording follows flight_ssr_status. */
    public const EXTRAS = 'extras';

    /** flight_ssr_status when the event happened — the model is re-read when the queued mail is sent. */
    public ?string $ssrStatus;

    public function __construct(public Booking $booking, public string $kind)
    {
        $this->ssrStatus = $booking->flight_ssr_status;
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        $flight = $this->booking->vertical === 'flight';
        $package = $this->booking->vertical === 'package';
        $ref = $this->booking->reference;

        $replyTo = config('services.booking_emails.reply_to');

        return new Envelope(replyTo: filled($replyTo) ? [new Address($replyTo, 'TYT Luxe')] : [], subject: match ($this->kind) {
            self::CONFIRMED => ($flight ? 'Your flight is booked' : ($package && $this->booking->packageBalanceDue() ? 'Your seats are booked' : 'Your booking is confirmed'))." — {$ref}",
            self::BALANCE_PAID => "Your trip is fully paid — {$ref}",
            self::CANCEL_REQUESTED => "We received your cancellation request — {$ref}",
            self::BALANCE_REMINDER => "Reminder: your balance is due — {$ref}",
            self::RESCHEDULED => "Your new flight details — {$ref}",
            self::CANCELLED => "Your booking has been cancelled — {$ref}",
            self::FAILED_REFUNDED => "We couldn't confirm your booking — {$ref}",
            self::EXTRAS => match ($this->ssrStatus) {
                'confirmed' => "Your seats, meals & baggage are confirmed — {$ref}",
                'partially_confirmed' => "Some of your extras couldn't be added — {$ref}",
                default => "We couldn't add your extras — {$ref}",
            },
            default => "Update on your booking — {$ref}",
        });
    }

    public function content(): Content
    {
        return new Content(view: 'emails.booking_update');
    }

    /**
     * The e-ticket for a ticketed (or rescheduled) flight, and the invoice
     * for confirmed, rescheduled and cancelled bookings.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $booking = $this->booking;
        $attachments = [];

        if (in_array($this->kind, [self::CONFIRMED, self::RESCHEDULED], true) && $booking->hasETicket()) {
            $attachments[] = Attachment::fromData(fn () => Pdf::loadView('pdf.e-ticket', ['booking' => $booking])->setPaper('a4', 'portrait')->output(), "e-ticket-{$booking->reference}.pdf")
                ->withMime('application/pdf');
        }

        if (in_array($this->kind, [self::CONFIRMED, self::RESCHEDULED, self::CANCELLED, self::BALANCE_PAID], true) && $booking->hasInvoice()) {
            $booking->loadMissing(['hotel.destination', 'roomType', 'travelers', 'package']);
            $attachments[] = Attachment::fromData(fn () => Pdf::loadView('pdf.invoice', ['booking' => $booking])->setPaper('a4', 'portrait')->output(), "invoice-{$booking->reference}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
