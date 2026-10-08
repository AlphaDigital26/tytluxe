<?php

namespace App\Mail;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
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

    public const HELD = 'held';

    public const HOLD_EXPIRED = 'hold_expired';

    public const CANCELLED = 'cancelled';

    public const FAILED_REFUNDED = 'failed_refunded';

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
        $ref = $this->booking->reference;

        return new Envelope(subject: match ($this->kind) {
            self::CONFIRMED => ($flight ? 'Your flight is booked' : 'Your booking is confirmed')." — {$ref}",
            self::RESCHEDULED => "Your new flight details — {$ref}",
            self::HELD => "Your fare is on hold — {$ref}",
            self::HOLD_EXPIRED => "Your held fare has expired — {$ref}",
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

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        if (! in_array($this->kind, [self::CONFIRMED, self::RESCHEDULED, self::CANCELLED], true) || ! $this->booking->hasInvoice()) {
            return [];
        }

        $booking = $this->booking->loadMissing(['hotel.destination', 'roomType', 'travelers']);

        return [
            Attachment::fromData(fn () => Pdf::loadView('pdf.invoice', ['booking' => $booking])->setPaper('a4', 'portrait')->output(), "invoice-{$booking->reference}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
