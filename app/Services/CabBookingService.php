<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\Exceptions\TripJackTimeoutException;
use App\Services\TripJack\TripJackCabClient;
use App\Support\UnusedPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * TripJack cabs, from search to cancellation.
 *
 * The guest pays us first (Razorpay). Only then is the cab booked with
 * TripJack (Booking API — it comes back PAYMENT_PENDING) and paid for from
 * our TripJack wallet (Payment API), which confirms it. If either step
 * fails for certain, the guest is refunded in full straight away; if the
 * outcome is unknown (a timeout), the booking goes to staff
 * (failed_needs_review) rather than risk booking or charging twice.
 *
 * Booking columns used: cab_pickup_at, cab_details (trip, chosen cab,
 * passenger, TripJack's latest details), tripjack_booking_id,
 * tripjack_total_price (what TripJack charges us), margin_amount,
 * gst_on_margin, razorpay_recovery, total_amount.
 */
class CabBookingService
{
    /** Pickup must be at least this far away when searching: TripJack needs 2 hours, the rest covers paying. */
    public const MIN_LEAD_MINUTES = 150;

    /** TripJack allows 1–10 passengers. */
    public const MAX_PASSENGERS = 10;

    public const JOURNEY_TYPES = [
        'airport_transfer' => 'Airport Transfer',
        'outstation' => 'Outstation',
        'local' => 'Local',
    ];

    public function __construct(protected TripJackCabClient $client, protected RazorpayService $razorpay)
    {
    }

    /** Our TripJack agent ID is needed to book and to pay from the wallet. */
    public static function configured(): bool
    {
        return filled(config('services.tripjack.cab.agent_id'));
    }

    /**
     * A place the guest picked from the suggestions, as TripJack's location
     * object (coordinates and address come from the Lat Long API).
     */
    public function location(string $placeId, string $label): array
    {
        $data = $this->client->latLong($placeId);
        $address = array_filter([
            'city' => $data['address']['city'] ?? null,
            'country' => $data['address']['country'] ?? null,
            'postalCode' => $data['address']['postalCode'] ?? null,
        ]);

        return [
            'type' => 'location',
            'displayAddress' => $label,
            'lat' => (string) ($data['location']['lat'] ?? ''),
            'long' => (string) ($data['location']['lng'] ?? ''),
            'address' => $address,
        ];
    }

    /**
     * Searches cabs. $search: journeyType, roundTrip, pickupAt, returnAt,
     * passengers, origin and destination (location objects from location()).
     *
     * @return array{journeyInfo: array, routeDetails: array, options: array<int, array>}
     */
    public function search(array $search): array
    {
        $payload = array_filter([
            'pickupDate' => Carbon::parse($search['pickupAt'])->format('Y-m-d H:i'),
            'returnDate' => $search['roundTrip'] ? Carbon::parse($search['returnAt'])->format('Y-m-d H:i') : null,
            'origin' => $search['origin'],
            'destination' => $search['destination'],
            'journeyType' => $search['journeyType'],
            'tripType' => $search['roundTrip'] ? 'roundtrip' : 'oneway',
            'passengers' => (int) $search['passengers'],
            'quoteFilter' => ['paxCount' => (int) $search['passengers']],
        ], fn ($v) => $v !== null);

        $data = $this->client->quotes($payload);
        $markup = CabPricingService::markupFor((bool) $search['roundTrip']);

        $options = [];
        foreach ($data['quotesInfo'] ?? [] as $group) {
            foreach ($group['quotes'] ?? [] as $quote) {
                $fare = $quote['fareBreakup'] ?? [];
                $supplier = (float) ($fare['totalFare'] ?? 0);
                if ($supplier <= 0) {
                    continue;
                }
                $options[] = [
                    'key' => (string) ($quote['quoteChildId'] ?? Str::random(8)),
                    'label' => $group['label'] ?? trim(($group['vehicleCategory'] ?? '').' '.($group['vehicleType'] ?? '')),
                    'vehicleType' => $group['vehicleType'] ?? null,
                    'vehicleCategory' => $group['vehicleCategory'] ?? null,
                    'similar' => $group['similarType'] ?? $group['modelName'] ?? null,
                    'image' => $group['vehicleImages'][0] ?? null,
                    'seats' => (int) ($quote['paxCount'] ?? $group['paxCapacity'] ?? 0),
                    'bags' => (int) ($quote['luggageCount'] ?? $group['luggageCapacity'] ?? 0),
                    'vendorId' => $quote['vendorId'] ?? null,
                    'quotationId' => $quote['quotationId'] ?? null,
                    'quoteChildId' => $quote['quoteChildId'] ?? null,
                    'fare' => $fare,
                    'policies' => $quote['policies'] ?? [],
                    'price' => CabPricingService::price($supplier, $markup),
                ];
            }
        }
        usort($options, fn ($a, $b) => $a['price']['total'] <=> $b['price']['total']);

        return [
            'journeyInfo' => $data['journeyInfo'] ?? [],
            'routeDetails' => $data['routeDetails'] ?? [],
            'options' => $options,
        ];
    }

    /**
     * The guest's booking, awaiting payment. $passenger: firstName,
     * lastName, email, phone, phoneCode, flightNumber, request.
     */
    public function createBooking(int $userId, array $search, array $result, array $option, array $passenger): Booking
    {
        $price = $option['price'];

        return Booking::create([
            'reference' => 'TYT'.strtoupper(Str::random(8)),
            'user_id' => $userId,
            'vertical' => 'cab',
            'status' => 'pending_payment',
            'lead_guest_name' => trim($passenger['firstName'].' '.$passenger['lastName']),
            'guest_email' => $passenger['email'],
            'guest_phone' => $passenger['phone'],
            'guest_phone_code' => $passenger['phoneCode'] ?? '91',
            'special_requests' => $passenger['request'] ?? null,
            'pax_adults' => (int) $search['passengers'],
            'cab_pickup_at' => Carbon::parse($search['pickupAt']),
            'check_in' => Carbon::parse($search['pickupAt'])->toDateString(),
            'check_out' => $search['roundTrip'] ? Carbon::parse($search['returnAt'])->toDateString() : null,
            'flight_route' => self::shortPlace($search['origin']['displayAddress']).' → '.self::shortPlace($search['destination']['displayAddress']),
            // base + tax = total: the cab fare as the guest sees it (ours
            // included), then GST on our markup and the online payment charge.
            'base_amount' => round($price['supplier'] + $price['markup'], 2),
            'tax_amount' => round($price['gst'] + $price['gateway'], 2),
            'total_amount' => $price['total'],
            'tripjack_total_price' => $price['supplier'],
            'margin_amount' => $price['markup'],
            'gst_on_margin' => $price['gst'],
            'razorpay_recovery' => $price['gateway'],
            'currency' => 'INR',
            'cab_details' => [
                'journeyType' => $search['journeyType'],
                'roundTrip' => (bool) $search['roundTrip'],
                'pickupAt' => Carbon::parse($search['pickupAt'])->toIso8601String(),
                'returnAt' => $search['roundTrip'] ? Carbon::parse($search['returnAt'])->toIso8601String() : null,
                'from' => $search['origin']['displayAddress'],
                'to' => $search['destination']['displayAddress'],
                'journeyInfo' => $result['journeyInfo'],
                'routeDetails' => $result['routeDetails'],
                'option' => $option,
                'passenger' => [
                    'firstName' => $passenger['firstName'],
                    'lastName' => $passenger['lastName'],
                    'flightNumber' => $passenger['flightNumber'] ?? null,
                ],
            ],
        ]);
    }

    /** Razorpay order for the booking (an unpaid one is reused on a page reload). */
    public function paymentFor(Booking $booking): Payment
    {
        $payment = $booking->payments()->where('status', 'created')->where('purpose', 'booking')->latest()->first();
        if ($payment && abs((float) $payment->amount - (float) $booking->total_amount) < 0.01) {
            return $payment;
        }

        $order = $this->razorpay->createOrder((float) $booking->total_amount, $booking->reference);

        return Payment::create([
            'booking_id' => $booking->id,
            'razorpay_order_id' => $order['id'],
            'amount' => $booking->total_amount,
            'currency' => 'INR',
            'status' => 'created',
            'purpose' => 'booking',
        ]);
    }

    /**
     * Payment captured (Checkout callback or webhook — either may come
     * first, or both): books and pays for the cab with TripJack. Locked and
     * claimed first, so it runs once per booking.
     */
    public function confirmAfterPayment(Payment $payment): void
    {
        $booking = DB::transaction(function () use ($payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->first();

            if (in_array($payment->status, Booking::PAID_PAYMENT_STATUSES, true)) {
                return null; // already handled by the other path
            }
            $payment->update(['status' => 'captured']);

            if (! in_array($booking->status, ['pending_payment', 'payment_failed'], true) || $booking->tripjack_confirm_attempted_at !== null) {
                UnusedPayment::refund($payment, 'A payment arrived after this cab booking was already '.str_replace('_', ' ', $booking->status).'.');

                return null;
            }
            $booking->update(['tripjack_confirm_attempted_at' => now()]);

            return $booking;
        });

        if ($booking) {
            $this->bookWithTripJack($booking, $payment->fresh());
        }
    }

    protected function bookWithTripJack(Booking $booking, Payment $payment): void
    {
        // 1. Create the booking at TripJack.
        try {
            $created = $this->client->book($this->bookingPayload($booking));
        } catch (TripJackTimeoutException $e) {
            $this->needsReview($booking, 'TripJack did not answer when booking the cab, so we do not know whether it was booked. Check TripJack\'s dashboard for a cab booking for '.$booking->lead_guest_name.': if there is one, pay for it there or cancel it; then confirm or refund this booking.', $e);

            return;
        } catch (TripJackException $e) {
            $this->refundGuest($booking, $payment, 'TripJack could not book the cab ('.$this->reason($e).').');

            return;
        }

        $tripjackId = (string) ($created['id'] ?? '');
        $charge = (float) ($created['totalPrice'] ?? $created['priceBreakup']['agentNetPayable'] ?? 0);
        $booking->update(['tripjack_booking_id' => $tripjackId ?: null, 'cab_details' => array_merge($booking->cab_details ?? [], ['created' => $created])]);

        if ($tripjackId === '' || $charge <= 0) {
            $this->refundGuest($booking, $payment, 'TripJack did not return a booking ID or price for the cab.');

            return;
        }

        // The price went up between search and booking: don't pay more than
        // the guest was charged for. The unpaid TripJack booking lapses.
        if ($charge > (float) $booking->tripjack_total_price + 1) {
            $this->refundGuest($booking, $payment, sprintf('TripJack\'s price changed from INR %s to INR %s before the cab could be booked.', number_format((float) $booking->tripjack_total_price, 2), number_format($charge, 2)));

            return;
        }

        // 2. Pay TripJack from our wallet — this confirms the cab.
        try {
            $this->client->pay($tripjackId, $charge, (string) config('services.tripjack.cab.agent_id'));
        } catch (TripJackTimeoutException $e) {
            $this->needsReview($booking, "TripJack did not answer when paying for cab booking {$tripjackId} from the wallet. Check it in TripJack's dashboard: if it is paid, click \"Check status with TripJack\"; if not, pay or cancel it there.", $e);

            return;
        } catch (TripJackException $e) {
            $this->refundGuest($booking, $payment, 'Paying TripJack for the cab from the wallet failed ('.$this->reason($e).'). The wallet balance may be too low.');

            return;
        }

        $booking->update(['status' => 'confirmed', 'tripjack_total_price' => $charge]);
        $this->refreshStatus($booking);
    }

    /**
     * Booking API request body (TripJack Cabs doc, Booking API). netAmount /
     * grossAmount: the quote's fare without and with TripJack's taxes.
     */
    public function bookingPayload(Booking $booking): array
    {
        $d = $booking->cab_details;
        $option = $d['option'];
        $fare = $option['fare'] ?? [];
        $gross = (float) ($fare['totalFare'] ?? $booking->tripjack_total_price);
        $code = ltrim((string) ($booking->guest_phone_code ?: '91'), '+');
        $phone = preg_replace('/\D+/', '', (string) $booking->guest_phone);
        if (str_starts_with($phone, $code) && strlen($phone) > 10) {
            $phone = substr($phone, strlen($code));
        }
        $vendorId = $option['vendorId'] ?? null;

        return [
            'journeyInfo' => $d['journeyInfo'],
            'routeDetail' => $d['routeDetails'],
            'addons' => [],
            'quotationInfo' => [
                'vehicleType' => $option['vehicleType'],
                'vehicleCategory' => $option['vehicleCategory'],
                'quoteId' => $option['quotationId'],
                'childQuoteId' => $option['quoteChildId'],
                'paxCount' => $option['seats'],
                'luggageCount' => $option['bags'],
                'vendorId' => $vendorId,
            ],
            'pricingInfo' => [
                'netAmount' => number_format($gross - (float) ($fare['totalTax'] ?? 0), 2, '.', ''),
                'addonsPrice' => '0.00',
                // Some operators (e.g. vendor 4) fail with a server error
                // without these two (see the doc's Embedded API sample).
                'tjTaxAmount' => number_format((float) ($fare['totalTax'] ?? 0), 2, '.', ''),
                'tjManagementFee' => '0.00',
                'agentMarkup' => 0,
                'agentMarkupSplitup' => ['onwardJourneyMarkup' => 0, 'returnJourneyMarkup' => 0],
                'grossAmount' => number_format($gross, 2, '.', ''),
            ],
            'passengerDetail' => array_filter([
                'firstName' => $d['passenger']['firstName'],
                'lastName' => $d['passenger']['lastName'],
                'email' => $booking->guest_email,
                'phone' => '+'.$code.$phone,
                'flightDetails' => filled($d['passenger']['flightNumber'] ?? null) ? ['number' => strtoupper(str_replace(' ', '', $d['passenger']['flightNumber']))] : null,
            ]),
            'serviceRequest' => (string) ($booking->special_requests ?? ''),
            'consent' => 'yes',
            'agentEmail' => (string) config('services.tripjack.cab.agent_email'),
            'agentPhone' => (string) config('services.tripjack.cab.agent_phone'),
            'agentId' => (int) config('services.tripjack.cab.agent_id'),
            'vendorId' => $vendorId,
        ];
    }

    /**
     * TripJack's latest details for the booking (status, ride status, driver
     * tracking link) saved on cab_details.live. Returns them, or null when
     * TripJack can't be reached.
     */
    public function refreshStatus(Booking $booking): ?array
    {
        if (! $booking->tripjack_booking_id) {
            return null;
        }

        try {
            $details = $this->client->bookingDetails($booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('cab_details_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

            return null;
        }

        $order = $details['order'] ?? [];
        $live = [
            'status' => $order['status'] ?? null,
            'paymentStatus' => $order['paymentStatus'] ?? null,
            'rideStatus' => $order['rideStatus'] ?? null,
            'trackingLink' => $order['trackingLink'] ?? null,
            'helpline' => $order['helpline'] ?? null,
            'checkedAt' => now()->toIso8601String(),
        ];
        $updates = ['cab_details' => array_merge($booking->cab_details ?? [], ['live' => $live])];

        // Paid at TripJack (e.g. staff paid it by hand after a timeout).
        if ($booking->status === 'failed_needs_review' && ($live['paymentStatus'] === 'SUCCESS' || $live['status'] === 'PAYMENT_SUCCESS')) {
            $updates['status'] = 'confirmed';
            $updates['admin_note'] = $booking->adminNoteWith(now()->format('j M Y, g:i A').' — TripJack shows this cab as paid; booking confirmed.');
        }
        $booking->update($updates);

        return $live;
    }

    /** Can the guest cancel this booking online right now? */
    public static function guestCanCancel(Booking $booking): bool
    {
        return $booking->vertical === 'cab' && $booking->status === 'confirmed'
            && $booking->cancellation_requested_at === null && filled($booking->tripjack_booking_id)
            && $booking->cab_pickup_at?->isFuture()
            && \App\Support\CabSettings::allowGuestCancel();
    }

    /**
     * What cancelling now would refund the guest: TripJack's refund share
     * of our price, without the online payment charge (Razorpay keeps its
     * fee). Null when TripJack can't say.
     *
     * @return array{refund: float, percent: float, description: string, tripjackRefund: float}|null
     */
    public function cancellationQuote(Booking $booking): ?array
    {
        try {
            $charges = $this->client->cancellationCharges((string) $booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('cab_cancel_quote_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

            return null;
        }

        return $this->refundFor($booking, $charges);
    }

    /** @return array{refund: float, percent: float, description: string, tripjackRefund: float} */
    protected function refundFor(Booking $booking, array $amendment): array
    {
        $paid = (float) ($amendment['amountPaid'] ?? $booking->tripjack_total_price);
        $tripjackRefund = (float) ($amendment['refundAmount'] ?? 0);
        $share = $paid > 0 ? min(1, max(0, $tripjackRefund / $paid)) : 0;
        $ours = (float) $booking->total_amount - (float) $booking->razorpay_recovery;

        return [
            'refund' => round($ours * $share, 2),
            'percent' => round($share * 100, 2),
            'description' => (string) ($amendment['description'] ?? $amendment['appliedAmendmentConfig']['description'] ?? ''),
            'tripjackRefund' => $tripjackRefund,
        ];
    }

    /**
     * Cancels the cab at TripJack and refunds the guest their share. Claims
     * the booking first, so a double submit can't cancel twice.
     *
     * @return array{ok: bool, message: string}
     */
    public function cancel(Booking $booking, string $by = 'guest'): array
    {
        $claimed = Booking::whereKey($booking->id)->where('status', 'confirmed')->whereNull('cancellation_requested_at')
            ->update(['cancellation_requested_at' => now()]);
        if (! $claimed) {
            return ['ok' => false, 'message' => 'This booking is already being cancelled.'];
        }
        $booking->refresh();

        try {
            $result = $this->client->cancel((string) $booking->tripjack_booking_id);
        } catch (TripJackTimeoutException $e) {
            $booking->update(['admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A')." — Cancellation sent to TripJack ({$by}) but TripJack did not answer. Check the booking in TripJack's dashboard, then refund the guest.")]);
            Log::channel('tripjack')->critical('cab_cancel_outcome_unknown', ['booking_id' => $booking->id]);

            return ['ok' => true, 'message' => 'Your cancellation request has been sent. We will confirm it and process any refund shortly.'];
        } catch (TripJackException $e) {
            $booking->update(['cancellation_requested_at' => null]);
            Log::channel('tripjack')->error('cab_cancel_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'We could not cancel this cab right now ('.$this->reason($e).'). Please try again or contact us.'];
        }

        if (strtoupper((string) ($result['amendStatus'] ?? '')) !== 'SUCCESS') {
            $booking->update([
                'cab_details' => array_merge($booking->cab_details ?? [], ['cancellation' => $result]),
                'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A')." — Cancellation requested ({$by}); TripJack status: ".($result['amendStatus'] ?? 'unknown').'. Refund the guest once TripJack confirms.'),
            ]);

            return ['ok' => true, 'message' => 'Your cancellation request has been sent. We will confirm it and process any refund shortly.'];
        }

        $refund = $this->refundFor($booking, $result);
        $payment = $booking->payments()->where('purpose', 'booking')->where('status', 'captured')->latest()->first();
        $note = now()->format('j M Y, g:i A')." — Cancelled ({$by}). TripJack refunds INR ".number_format($refund['tripjackRefund'], 2)." ({$refund['percent']}%).";
        $updates = [
            'status' => 'cancelled',
            'cab_details' => array_merge($booking->cab_details ?? [], ['cancellation' => $result]),
        ];

        if ($refund['refund'] > 0 && $payment && ! $payment->isOffline()) {
            try {
                $this->razorpay->refund((string) $payment->razorpay_payment_id, $refund['refund']);
                $payment->update(['status' => $refund['refund'] >= (float) $payment->amount ? 'refunded' : 'partially_refunded', 'refund_amount' => $refund['refund'], 'refund_reason' => 'Cab cancelled']);
                $updates['cancellation_reason'] = 'Cancelled — INR '.number_format($refund['refund'], 2).' refunded.';
                $note .= ' INR '.number_format($refund['refund'], 2).' refunded to the guest automatically.';
            } catch (\Throwable $e) {
                $updates['manual_refund_due_at'] = now();
                $updates['admin_refund_amount'] = $refund['refund'];
                $updates['cancellation_reason'] = 'Cancelled — refund being processed manually.';
                $note .= ' The automatic refund of INR '.number_format($refund['refund'], 2).' failed: refund it in the Razorpay dashboard, then click "Record manual refund".';
                Log::channel('tripjack')->error('cab_cancel_refund_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);
            }
        } else {
            $updates['cancellation_reason'] = $refund['refund'] > 0 ? 'Cancelled — refund being processed manually.' : 'Cancelled — no refund under the cancellation policy.';
            if ($refund['refund'] > 0) {
                $updates['manual_refund_due_at'] = now();
                $updates['admin_refund_amount'] = $refund['refund'];
                $note .= ' Refund INR '.number_format($refund['refund'], 2).' to the guest by hand.';
            }
        }

        $updates['admin_note'] = $booking->adminNoteWith($note);
        $booking->update($updates);

        return ['ok' => true, 'message' => $refund['refund'] > 0
            ? 'Your cab has been cancelled. ₹'.number_format($refund['refund'], 2).' will be refunded to your original payment method within 5–7 business days.'
            : 'Your cab has been cancelled. Under the cancellation policy, no refund is due.'];
    }

    /** Full refund to the guest after TripJack could not book or be paid. */
    protected function refundGuest(Booking $booking, Payment $payment, string $why): void
    {
        Log::channel('tripjack')->error('cab_booking_failed', ['booking_id' => $booking->id, 'reason' => $why]);

        try {
            $this->razorpay->refund((string) $payment->razorpay_payment_id);
            $payment->update(['status' => 'refunded', 'refund_amount' => $payment->amount, 'refund_reason' => $why]);
            $booking->update([
                'status' => 'refunded',
                'cancellation_reason' => $why.' Refunded in full.',
                'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A').' — '.$why.' INR '.number_format((float) $payment->amount, 2).' refunded automatically.'),
            ]);
        } catch (\Throwable $e) {
            Log::channel('tripjack')->error('cab_refund_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);
            $booking->update([
                'status' => 'failed_needs_review',
                'manual_refund_due_at' => now(),
                'admin_refund_amount' => $payment->amount,
                'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A').' — '.$why.' The automatic refund of INR '.number_format((float) $payment->amount, 2).' failed: refund it in the Razorpay dashboard, then click "Record manual refund".'),
            ]);
        }
    }

    protected function needsReview(Booking $booking, string $note, \Throwable $e): void
    {
        Log::channel('tripjack')->critical('cab_booking_outcome_unknown', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);
        $booking->update([
            'status' => 'failed_needs_review',
            'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A').' — '.$note),
        ]);
    }

    protected function reason(TripJackException $e): string
    {
        if ($e instanceof TripJackApiException) {
            return (string) ($e->body['error']['message'] ?? $e->body['message'] ?? 'error '.$e->status);
        }

        return 'TripJack refused the request';
    }

    /** "IGI Airport Terminal 3, Delhi" → "IGI Airport Terminal 3". */
    public static function shortPlace(string $place): string
    {
        return Str::limit(trim(explode(',', $place)[0]), 40, '…');
    }
}
