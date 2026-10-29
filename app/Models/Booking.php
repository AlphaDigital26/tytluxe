<?php

namespace App\Models;

use App\Observers\BookingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([BookingObserver::class])]
class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'user_id', 'guest_email', 'guest_phone', 'guest_phone_code', 'vertical',
        'hotel_id', 'package_id', 'room_type_id', 'room_name', 'meal_basis',
        'tripjack_gst_type', 'tripjack_gst_info',
        'tripjack_booking_id', 'hotel_confirmation_number', 'tripjack_hold_id', 'tripjack_hold_expires_at',
        'tripjack_option_id', 'tripjack_room_traveller_payload', 'tripjack_confirm_attempted_at',
        'check_in', 'check_out', 'flight_route', 'pax_adults', 'pax_children', 'pax_infants',
        'flight_journey_type', 'flight_cabin_class', 'flight_fare_identifier',
        'flight_departure_date', 'flight_return_date', 'flight_segments_payload',
        'tripjack_flight_pnr', 'tripjack_flight_ticket_numbers',
        'flight_ssr_options_cache', 'flight_ssr_pending_selection', 'flight_ssr_amendment_ids',
        'flight_ssr_confirmed', 'flight_ssr_amount_paid', 'flight_ssr_status',
        'flight_legs', 'flight_itinerary', 'flight_partial_amendments', 'flight_reissued_at', 'flight_reissue_history',
        'flight_reissue_pending',
        'lead_guest_name', 'special_requests',
        'base_amount', 'tax_amount', 'discount_amount', 'total_amount',
        'tripjack_total_price', 'gst_slab', 'margin_amount', 'gst_on_margin', 'razorpay_recovery',
        'tripjack_mf', 'tripjack_mft', 'management_fee', 'management_fee_gst',
        'currency', 'offer_id', 'status', 'cancellation_reason', 'cancellation_requested_at', 'flight_cancel_amendment_id', 'admin_note',
        'manual_refund_due_at', 'admin_refund_amount',
        'package_departure_id', 'package_option', 'package_price_per_person', 'package_payment_plan',
        'package_balance_amount', 'package_balance_due_on', 'package_balance_reminded_at',
    ];

    protected $casts = [
        'manual_refund_due_at' => 'datetime',
        'tripjack_room_traveller_payload' => 'array',
        'tripjack_gst_info' => 'array',
        'flight_segments_payload' => 'array',
        'tripjack_flight_pnr' => 'array',
        'tripjack_flight_ticket_numbers' => 'array',
        'flight_ssr_options_cache' => 'array',
        'flight_ssr_pending_selection' => 'array',
        'flight_ssr_amendment_ids' => 'array',
        'flight_ssr_confirmed' => 'array',
        'flight_legs' => 'array',
        'flight_itinerary' => 'array',
        'flight_partial_amendments' => 'array',
        'flight_reissue_history' => 'array',
        'flight_reissue_pending' => 'array',
        'flight_reissued_at' => 'datetime',
        'tripjack_hold_expires_at' => 'datetime',
        'cancellation_requested_at' => 'datetime',
        'flight_departure_date' => 'date',
        'flight_return_date' => 'date',
        'package_balance_due_on' => 'date',
        'package_balance_reminded_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function agent() { return $this->belongsTo(User::class, 'agent_id'); }
    public function travelers() { return $this->hasMany(BookingTraveler::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function package() { return $this->belongsTo(Package::class); }
    public function packageDeparture() { return $this->belongsTo(PackageDeparture::class); }

    /** "2 travellers + 1 infant (free)" — package travellers as guests read them. */
    public function packageTravellersLabel(): string
    {
        $n = (int) $this->pax_adults;
        $inf = (int) $this->pax_infants;

        return $n.' traveller'.($n === 1 ? '' : 's').($inf ? ' + '.$inf.' infant'.($inf === 1 ? '' : 's').' (free)' : '');
    }

    /**
     * TripJack's Airline Automation Coverage Matrix: airlines whose
     * cancellation, date change and add-ons can't be done online through
     * TripJack — staff handle them with the airline.
     */
    public const FLIGHT_AIRLINES_OFFLINE = [
        'FZ', // flydubai
        'G9', '3O', '3L', // Air Arabia (and Maroc / Abu Dhabi)
        'VJ', 'VZ', // VietJet Air, Thai VietJet Air
        'TR', 'TZ', // Scoot
        'XY', // Flynas
        'PC', // Pegasus
        'K6', // Cambodia Angkor Air
        'DT', // TAAG Angola
    ];

    /** Matrix: cancellation online, but no date change or add-ons. */
    public const FLIGHT_AIRLINES_CANCEL_ONLY = ['S5', '9I']; // Star Air, Alliance Air

    /** @return string[] airline codes on this flight booking */
    public function flightAirlineCodes(): array
    {
        return collect($this->flight_itinerary['segments'] ?? [])->pluck('airlineCode')->filter()->unique()->values()->all();
    }

    /**
     * Whether TripJack can do this change online for the booking's
     * airline(s): 'cancel', 'reschedule' or 'extras'. Airlines missing from
     * the matrix are allowed (TripJack's own reply decides).
     */
    public function flightSupportsOnline(string $what): bool
    {
        $codes = $this->flightAirlineCodes();
        if (array_intersect($codes, self::FLIGHT_AIRLINES_OFFLINE)) {
            return false;
        }

        return $what === 'cancel' || ! array_intersect($codes, self::FLIGHT_AIRLINES_CANCEL_ONLY);
    }

    /** After this long on the payment page without paying, the guest has left. */
    public const ABANDONED_AFTER_MINUTES = 60;

    /**
     * Started booking, reached the payment page and never paid — long enough
     * ago that it's not someone paying right now. Fare and seat holds are
     * shorter than this, so nothing is still held for them.
     */
    public function paymentAbandoned(): bool
    {
        return $this->status === 'pending_payment' && $this->created_at
            && $this->created_at->lt(now()->subMinutes(self::ABANDONED_AFTER_MINUTES));
    }

    /** A package booked with a booking amount whose balance is still unpaid. */
    public function packageBalanceDue(): bool
    {
        return $this->vertical === 'package' && $this->status === 'confirmed'
            && $this->cancellation_requested_at === null && (float) $this->package_balance_amount > 0;
    }
    public function hotel() { return $this->belongsTo(Hotel::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }

    /**
     * admin_note with $note added on the end — staff add their own notes
     * here, so automatic notes must never replace what is already there.
     */
    public function adminNoteWith(string $note): string
    {
        return trim(($this->admin_note ? $this->admin_note."\n" : '').$note);
    }

    /** Payment statuses where the guest's money was actually taken (some may since be refunded). */
    public const PAID_PAYMENT_STATUSES = ['captured', 'refunded', 'partially_refunded'];

    /**
     * An invoice exists only once the guest has paid something — never for
     * an unpaid hold (on_hold, hold_expired, or a hold released before
     * payment), a pending payment or a failed one.
     */
    public function hasInvoice(): bool
    {
        return $this->payments()->whereIn('status', self::PAID_PAYMENT_STATUSES)->exists();
    }

    /**
     * What reschedules changed TripJack's cost by: each one's reissue TF
     * (negative for a much cheaper new flight). Taken from TripJack's own
     * amounts, not the guest's payments, which also hold our service
     * charge, its GST and the gateway fee.
     */
    public function reissueSupplierCost(): float
    {
        return round((float) collect($this->flight_reissue_history ?? [])
            ->sum(fn ($h) => (float) ($h['amountPaid'] ?? 0)), 2);
    }

    /**
     * The guest company's GST details given at checkout (business travel),
     * for "Billed To" on our invoice. Null when none were given.
     *
     * @return array{number: string, name: string}|null
     */
    public function guestGst(): ?array
    {
        $info = $this->tripjack_gst_info;

        return filled($info['gstNumber'] ?? null)
            ? ['number' => (string) $info['gstNumber'], 'name' => (string) ($info['registeredName'] ?? '')]
            : null;
    }

    /**
     * A flight e-ticket exists once the airline has ticketed the booking
     * (PNR received) and only while it stands — not after cancellation.
     */
    public function hasETicket(): bool
    {
        return $this->vertical === 'flight' && $this->status === 'confirmed'
            && $this->cancellation_requested_at === null && filled($this->tripjack_flight_pnr);
    }

    /**
     * Flight passengers with their ticket numbers by sector ("DEL-BOM"),
     * for the e-ticket. Tickets come from tripjack_flight_ticket_numbers
     * ([{name, tickets}]), else the itinerary's tickets keyed by name.
     *
     * @return array<int, array{name: string, type: string, tickets: array<string, string>}>
     */
    public function flightPassengers(): array
    {
        $stored = collect($this->tripjack_flight_ticket_numbers ?? []);
        $byName = $this->flight_itinerary['tickets'] ?? [];

        return collect($this->flight_segments_payload['travellerInfo'] ?? [])->map(function (array $t) use ($stored, $byName) {
            $name = trim(preg_replace('/\s+/', ' ', ($t['ti'] ?? '').' '.($t['fN'] ?? '').' '.($t['lN'] ?? '')));
            $entry = $stored->first(fn ($e) => is_array($e) && strcasecmp($e['name'] ?? '', $name) === 0);

            return [
                'name' => $name,
                'type' => ucfirst(strtolower($t['pt'] ?? 'adult')),
                'tickets' => (array) ($entry['tickets'] ?? $byName[strtoupper(trim(($t['fN'] ?? '').' '.($t['lN'] ?? '')))] ?? []),
            ];
        })->all();
    }

    /**
     * What a free cancellation keeps (hotels and flights): our management
     * fee alone — its GST is refunded. Bookings made before the fee existed
     * keep the old ₹100.
     */
    public function freeCancellationFee(): float
    {
        return $this->nonRefundableFee() ?? \App\Support\RefundPolicy::FREE_CANCELLATION_FEE;
    }

    /**
     * For RefundPolicy: our management fee (without its GST) — never
     * refunded on any cancellation. Null on bookings made before the fee
     * existed (they keep the older refund rule).
     */
    public function nonRefundableFee(): ?float
    {
        return $this->management_fee !== null ? round((float) $this->management_fee, 2) : null;
    }

    /**
     * Money actually paid and refunded on this booking, for the invoice.
     * total_amount is only the original booking; seats/meals/baggage bought
     * later (flight_ssr) and reschedule charges (flight_reissue) are their
     * own payments, and refunds sit on each payment's refund_amount.
     *
     * @return array{extras: array<string, float>, paid: float, refunded: float, net: float}
     */
    public function invoiceTotals(): array
    {
        $paid = $this->payments()->whereIn('status', self::PAID_PAYMENT_STATUSES)->get();
        $extras = array_filter([
            'Seats, meals & baggage added later' => round((float) $paid->where('purpose', 'flight_ssr')->sum('amount'), 2),
            'Reschedule charges' => round((float) $paid->where('purpose', 'flight_reissue')->sum('amount'), 2),
        ]);
        $paidTotal = round((float) $paid->sum('amount'), 2);
        $refunded = round((float) $paid->sum('refund_amount'), 2);

        return [
            'extras' => $extras,
            'paid' => $paidTotal,
            'refunded' => $refunded,
            'net' => round($paidTotal - $refunded, 2),
        ];
    }

    /** Booking status in the guest's words (raw values like "failed_needs_review" are for staff). */
    public function guestStatusLabel(): string
    {
        if ($this->status === 'confirmed' && $this->cancellation_requested_at !== null) {
            return 'Cancellation in progress';
        }

        return match ($this->status) {
            'pending_payment' => 'Awaiting payment',
            'payment_failed' => 'Payment failed',
            'pending_confirmation' => 'Awaiting hotel confirmation',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Not booked — refunded',
            'failed_needs_review' => 'Under review',
            'on_hold' => 'Held — payment pending',
            'hold_expired' => 'Hold expired',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    /**
     * What the guest is told about a cancellation. cancellation_reason is
     * written for staff (it can hold "needs manual review" or a raw Razorpay
     * error), so guests get this plain wording instead. Null when the
     * booking isn't cancelled or being cancelled.
     */
    public function guestCancellationMessage(): ?string
    {
        if ($this->status === 'confirmed' && $this->cancellation_requested_at !== null) {
            return 'Your cancellation request is being processed. Once it is complete, any refund due goes back to your original payment method.';
        }
        if ($this->status !== 'cancelled') {
            return null;
        }

        $reason = strtolower((string) $this->cancellation_reason);
        $refunded = (float) $this->payments()->sum('refund_amount');

        return match (true) {
            str_starts_with($reason, 'hold released by guest') => 'You released this held fare before paying, so no payment was taken.',
            str_starts_with($reason, 'hold released') => 'This held fare was released before payment, so no payment was taken. Please contact us if you would still like to travel.',
            str_contains($reason, 'manual') => 'This booking has been cancelled. Our team is processing your refund and it will be paid back to your original payment method. We will contact you if we need anything.',
            $refunded > 0 => sprintf('This booking has been cancelled and %s %s has been refunded to your original payment method. It can take 5–7 business days to show in your account.', $this->currency ?: 'INR', number_format($refunded, 2)),
            str_contains($reason, 'no refund') => 'This booking has been cancelled. Under the cancellation policy for this booking, no refund is due.',
            default => 'This booking has been cancelled. If a refund is due, it will be paid back to your original payment method.',
        };
    }
}
