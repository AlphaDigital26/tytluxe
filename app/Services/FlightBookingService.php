<?php

namespace App\Services;

use App\Jobs\PollFlightAmendmentJob;
use App\Jobs\PollFlightBookingStatusJob;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\Exceptions\TripJackTimeoutException;
use App\Services\TripJack\TripJackFlightClient;
use App\Services\TripJack\TripJackFlightErrorCatalog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Holds every flight-specific piece of the pay-first-then-book flow, so
 * FrontendController's existing hotel methods only need a thin
 * `if ($booking->vertical === 'flight') { return $this->flights->...(); }`
 * guard at the top — the hotel code path below that guard is completely
 * unreached for a flight booking and stays exactly as it was. Mirrors the
 * hotel flow's logic in FrontendController (confirmBookingAfterPayment,
 * bookingConfirmation, submitCancellation, finalizeCancellation) but against
 * TripJackFlightClient/TripJackFlightErrorCatalog and flight order statuses.
 */
class FlightBookingService
{
    /**
     * Order statuses that mean "nothing more will change on its own" — see
     * TripJack's Flights Booking Details doc. UNCONFIRMED (hold released)
     * doesn't apply in Phase 1 (Instant Book only, no hold), included for
     * completeness/forward-compat.
     */
    public function isTerminal(?string $status): bool
    {
        return in_array($status, ['SUCCESS', 'CANCELLED', 'FAILED', 'ABORTED', 'UNCONFIRMED'], true);
    }

    /**
     * Marks the payment captured and calls TripJack's flight Book API
     * (always Instant Book — paymentInfos included — in Phase 1). Mirrors
     * FrontendController::confirmBookingAfterPayment()'s locking/idempotency
     * pattern exactly, but without the hotel-only ON_HOLD/confirm-book
     * branch (Phase 1 never holds a flight).
     */
    public function confirmAfterPayment(Payment $payment, RazorpayService $razorpay): void
    {
        $client = app(TripJackFlightClient::class);

        DB::transaction(function () use ($payment, $client, $razorpay) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->first();

            if (in_array($payment->status, ['captured', 'refunded', 'partially_refunded'], true)
                || in_array($booking->status, ['confirmed', 'refunded', 'failed_needs_review'], true)) {
                return; // already processed by a racing callback/webhook
            }

            $payment->update(['status' => 'captured']);

            // Flights' deliveryInfo.contacts wants the country code inlined
            // into the number itself (doc example: "+919500112233") — unlike
            // hotels' deliveryInfo, which takes a separate parallel `code`
            // array. No dialCodes param on TripJackFlightClient::book() for
            // that reason.
            $phoneDigits = preg_replace('/\D/', '', (string) $booking->guest_phone);
            $contactNumber = '+91'.$phoneDigits;
            $segments = $booking->flight_segments_payload ?? [];

            try {
                $response = $client->book(
                    $booking->tripjack_hold_id, // Review's bookingId — same column hotels use for the same concept
                    (float) $booking->tripjack_total_price, // TripJack's raw TF, not the marked-up customer price
                    $segments['travellerInfo'] ?? [],
                    [$booking->guest_email],
                    [$contactNumber],
                    gstInfo: $booking->tripjack_gst_info,
                    contactInfo: $segments['contactInfo'] ?? null,
                );
            } catch (TripJackException $e) {
                $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;

                // Timed out / 5xx: TripJack may still have ticketed it, so
                // a refund now could refund a real ticket — find out first.
                if (self::isUncertainOutcome($e)) {
                    $this->markOutcomeUnknown($booking, $booking->tripjack_hold_id, 'flight_book_outcome_unknown', $e);

                    return;
                }

                $described = TripJackFlightErrorCatalog::describe($errorCode);
                $this->logFailure($described['logLevel'], 'flight_book_after_payment_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
                $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

                return;
            }

            if (! ($response['status']['success'] ?? false)) {
                $errorCode = TripJackFlightErrorCatalog::codeFromResponse($response);
                $described = TripJackFlightErrorCatalog::describe($errorCode, 'The airline declined this booking after payment.');
                $this->logFailure($described['logLevel'], 'flight_book_after_payment_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
                $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

                return;
            }

            // TripJack accepted the request — the guest paid and the booking
            // is confirmed on our side. The real order status, PNR and
            // ticket numbers come from Booking Details, which TripJack's doc
            // says to call only after 5 seconds; PollFlightBookingStatusJob
            // does that and refunds if it ends FAILED/ABORTED.
            $booking->update([
                'tripjack_booking_id' => $response['bookingId'] ?? $booking->tripjack_hold_id,
                'tripjack_confirm_attempted_at' => now(),
                'status' => 'confirmed',
            ]);

            PollFlightBookingStatusJob::dispatch($booking->id)->delay(now()->addSeconds(5))->afterCommit();
        });
    }

    /**
     * Book / Confirm-Book timed out or 5xx'd — TripJack may or may not have
     * acted on it. The payment stays captured and the booking goes to
     * failed_needs_review (the confirmation page shows "We're Reviewing Your
     * Booking") while PollFlightBookingStatusJob checks Booking Details:
     * SUCCESS confirms it, FAILED/ABORTED/not-found refunds it.
     */
    protected function markOutcomeUnknown(Booking $booking, ?string $tripjackBookingId, string $event, TripJackException $e): void
    {
        $booking->update([
            'tripjack_booking_id' => $tripjackBookingId,
            'tripjack_confirm_attempted_at' => now(),
            'status' => 'failed_needs_review',
            'admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '')
                .'TripJack did not answer the booking request in time — the site is checking Booking Details and will confirm or refund automatically.'),
        ]);
        Log::channel('tripjack')->critical($event, ['booking_id' => $booking->id, 'tripjack_booking_id' => $tripjackBookingId, 'message' => $e->getMessage()]);

        PollFlightBookingStatusJob::dispatch($booking->id, verifyUnconfirmed: true)->delay(now()->addSeconds(5))->afterCommit();
    }

    /**
     * A timeout (no response) or a 5xx means the request may have reached
     * TripJack and been acted on. Anything else — a 4xx, or a 200 with
     * status.success false — is TripJack's definite "no".
     */
    public static function isUncertainOutcome(TripJackException $e): bool
    {
        return $e instanceof TripJackTimeoutException
            || ($e instanceof TripJackApiException && $e->status >= 500);
    }

    /**
     * Booking Details' travellers. Confirmed against live sandbox responses
     * (and every other reader in this codebase): they're under
     * itemInfos.AIR.travellerInfos — the doc's field table drops the
     * itemInfos.AIR prefix.
     */
    public static function airTravellers(array $details): array
    {
        return $details['itemInfos']['AIR']['travellerInfos'] ?? [];
    }

    /**
     * Acts on a Booking Details response for a booking we've asked TripJack
     * to ticket — shared by PollFlightBookingStatusJob and the confirmation
     * page's own polling. SUCCESS saves the PNR/ticket numbers (and, when
     * $resolvingUncertain, confirms a booking whose Book call timed out);
     * FAILED/ABORTED refunds the ticket payment; CANCELLED mirrors it.
     *
     * @return bool true once TripJack's status is final
     */
    public function applyBookingDetails(Booking $booking, array $details, RazorpayService $razorpay, bool $resolvingUncertain = false): bool
    {
        $status = $details['order']['status'] ?? null;
        $expected = $resolvingUncertain ? 'failed_needs_review' : 'confirmed';

        if ($booking->status !== $expected) {
            return $this->isTerminal($status);
        }

        if ($status === 'SUCCESS') {
            $traveller = self::airTravellers($details)[0] ?? [];
            $booking->update([
                'status' => 'confirmed',
                'tripjack_flight_pnr' => $traveller['pnrDetails'] ?? $booking->tripjack_flight_pnr,
                'tripjack_flight_ticket_numbers' => $traveller['ticketNumberDetails'] ?? $booking->tripjack_flight_ticket_numbers,
            ]);
            if ($resolvingUncertain) {
                Log::channel('tripjack')->info('flight_booking_confirmed_after_timeout', ['booking_id' => $booking->id]);
            }

            return true;
        }

        if (in_array($status, ['FAILED', 'ABORTED'], true)) {
            $this->refundUnconfirmedBooking($booking, $razorpay, "TripJack reported this booking as {$status}.");

            return true;
        }

        if ($status === 'CANCELLED' && ! $resolvingUncertain) {
            $booking->update(['status' => 'cancelled']);

            return true;
        }

        return false; // PENDING / ON_HOLD / unknown — check again later
    }

    /**
     * Refunds the ticket payment of a booking TripJack never ticketed.
     */
    public function refundUnconfirmedBooking(Booking $booking, RazorpayService $razorpay, string $reason): void
    {
        $payment = $this->farePayment($booking);
        if (! $payment) {
            $booking->update(['status' => 'failed_needs_review']);
            Log::channel('tripjack')->critical('flight_unconfirmed_booking_no_payment_to_refund', ['booking_id' => $booking->id, 'reason' => $reason]);

            return;
        }

        $this->refundAndMarkFailed($booking, $payment, $razorpay, $reason);
    }

    /**
     * Fare Validate before Confirm-Book — call this before creating the
     * Razorpay order so an expired/changed hold is caught before the guest
     * even reaches the payment screen, not after.
     *
     * @return array{ok: bool, message: ?string}
     */
    public function validateHoldFare(Booking $booking): array
    {
        $client = app(TripJackFlightClient::class);

        try {
            $response = $client->fareValidateHold($booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            $errorCode = $e instanceof \App\Services\TripJack\Exceptions\TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'We couldn\'t verify this fare right now. Please try again.');
            $this->logFailure($described['logLevel'], 'flight_hold_fare_validate_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);

            return ['ok' => false, 'message' => $described['message']];
        }

        if (! ($response['status']['success'] ?? false) || ($response['iobfe'] ?? false)) {
            $booking->update(['status' => 'hold_expired']);

            return ['ok' => false, 'message' => 'This held fare is no longer available. Please search again.'];
        }

        return ['ok' => true, 'message' => null];
    }

    /**
     * Releases a held (unpaid) PNR at the guest's request — POST /air/unhold.
     * Doesn't touch payments (Hold never captured any), just the booking
     * status and the supplier-side PNR.
     */
    public function releaseHold(Booking $booking): void
    {
        $client = app(TripJackFlightClient::class);
        $pnrs = array_values((array) ($booking->tripjack_flight_pnr ?? []));

        try {
            $client->unhold($booking->tripjack_booking_id, $pnrs);
        } catch (TripJackException $e) {
            $errorCode = $e instanceof \App\Services\TripJack\Exceptions\TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode);
            $this->logFailure($described['logLevel'], 'flight_unhold_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
            // Still mark it cancelled on our side — the guest asked to walk
            // away, and a stuck hold auto-expires with the supplier anyway
            // (timeLimit); this only stops us from double-billing, which was
            // never at risk here since Hold never captured payment.
        }

        $booking->update(['status' => 'cancelled', 'cancellation_reason' => 'Hold released by guest before payment.']);
    }

    /**
     * Called after a Confirm-Book payment is captured — Confirm-Book only
     * echoes {status} (confirmed live, no bookingId/PNR in the response), so
     * this always follows up with bookingDetails() for the real PNR/ticket,
     * same as Instant Book's own confirmAfterPayment() above. Reuses
     * tripjack_confirm_attempted_at as the idempotency claim, same column
     * and same pattern FrontendController::resolveOnHoldBooking() uses for
     * hotels' distinct ON_HOLD case.
     */
    public function confirmHoldAfterPayment(Payment $payment, RazorpayService $razorpay): void
    {
        $client = app(TripJackFlightClient::class);

        $claimed = DB::transaction(function () use ($payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->first();

            if (in_array($payment->status, ['captured', 'refunded', 'partially_refunded'], true)
                || $booking->tripjack_confirm_attempted_at !== null) {
                return null; // already processed by a racing callback/webhook
            }

            $payment->update(['status' => 'captured']);
            $booking->update(['tripjack_confirm_attempted_at' => now()]);

            return $booking->id;
        });

        if (! $claimed) {
            return;
        }

        $booking = Booking::findOrFail($claimed);

        try {
            $response = $client->confirmBook($booking->tripjack_booking_id, (float) $booking->tripjack_total_price);
        } catch (TripJackException $e) {
            if (self::isUncertainOutcome($e)) {
                $this->markOutcomeUnknown($booking, $booking->tripjack_booking_id, 'flight_confirm_book_outcome_unknown', $e);

                return;
            }

            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode);
            $this->logFailure($described['logLevel'], 'flight_confirm_book_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
            $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

            return;
        }

        if (! ($response['status']['success'] ?? false)) {
            $errorCode = TripJackFlightErrorCatalog::codeFromResponse($response);
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'This held fare could not be confirmed after payment.');
            $this->logFailure($described['logLevel'], 'flight_confirm_book_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
            $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

            return;
        }

        // Ticket numbers (and the final status) follow via Booking Details
        // after the doc's 5-second wait — same as Instant Book.
        $booking->update(['status' => 'confirmed']);

        PollFlightBookingStatusJob::dispatch($booking->id)->delay(now()->addSeconds(5));
    }

    protected function refundAndMarkFailed(Booking $booking, Payment $payment, RazorpayService $razorpay, string $reason): void
    {
        try {
            $razorpay->refund($payment->razorpay_payment_id, (float) $payment->amount);
            $payment->update([
                'status' => 'refunded',
                'refund_amount' => $payment->amount,
                'refund_reason' => $reason,
            ]);
            $booking->update(['status' => 'refunded']);
            Log::channel('tripjack')->critical('flight_booking_refunded_after_payment', [
                'booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $reason,
            ]);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed', 'refund_reason' => $reason]);
            $booking->update(['status' => 'failed_needs_review']);
            Log::channel('tripjack')->critical('flight_refund_after_booking_failure_errored', [
                'booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $reason, 'refund_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Re-reads live status from TripJack for the confirmation page, same
     * spirit as FrontendController::bookingConfirmation()'s polling but
     * against flight statuses (no ON_HOLD/confirm-book branch in Phase 1).
     *
     * @return array{liveStatus: ?string, stillPolling: bool}
     */
    public function refreshLiveStatus(Booking $booking, int $pollingSince, RazorpayService $razorpay): array
    {
        $liveStatus = null;

        // Doc: Booking Details only after 5 seconds from Book/Confirm-Book —
        // the first page load right after payment skips the lookup.
        $tooSoon = $booking->tripjack_confirm_attempted_at !== null
            && \Illuminate\Support\Carbon::parse($booking->tripjack_confirm_attempted_at)->gt(now()->subSeconds(5));

        if ($booking->tripjack_booking_id && ! $tooSoon) {
            try {
                $client = app(TripJackFlightClient::class);
                $details = $client->bookingDetails($booking->tripjack_booking_id);
                $liveStatus = $details['order']['status'] ?? null;

                $this->applyBookingDetails($booking, $details, $razorpay);
                $booking->refresh();
            } catch (TripJackException $e) {
                Log::channel('tripjack')->warning('flight_booking_details_failed', ['bookingId' => $booking->tripjack_booking_id, 'message' => $e->getMessage()]);
            }
        }

        $stillPolling = ! in_array($booking->status, ['payment_failed', 'refunded', 'failed_needs_review', 'cancelled'], true)
            && $booking->cancellation_requested_at === null
            && ! $this->isTerminal($liveStatus)
            && (now()->timestamp - $pollingSince) < 180;

        return ['liveStatus' => $liveStatus, 'stillPolling' => $stillPolling];
    }

    /**
     * Probes Auto Void eligibility by actually calling Get Amendment Charges
     * with type=VOIDED — confirmed live that this is the only reliable way
     * to know: TripJack rejects it outright for LCC bookings ("Void cannot
     * be raised for LCC booking") and presumably once the airline's void
     * window has closed, so guessing a window client-side would be wrong as
     * often as right. Returns null (silently) on any failure — Void is a
     * bonus option, never a blocker to the guest's normal cancellation.
     */
    public function checkVoidEligibility(Booking $booking): ?array
    {
        $client = app(TripJackFlightClient::class);

        try {
            $response = $client->amendmentCharges($booking->tripjack_booking_id, 'VOIDED', 'Same-day void eligibility check');
        } catch (TripJackException $e) {
            return null;
        }

        return ($response['status']['success'] ?? false) ? $response : null;
    }

    /**
     * Turns the cancel page's choice (entire booking / specific travellers /
     * specific flights) into the amendment `trips` payload, plus how many of
     * each pax type it covers (for the charges quote). Null when a partial
     * scope has nothing valid selected.
     *
     * @return array{trips: array, paxCounts: array<string, int>}|null
     */
    public function cancellationScope(Booking $booking, string $scope, array $legIndexes = [], array $travellerIndexes = []): ?array
    {
        $legs = $booking->flight_legs ?? [];
        $travellers = $booking->flight_segments_payload['travellerInfo'] ?? [];
        $bookingPax = array_filter([
            'ADULT' => (int) $booking->pax_adults,
            'CHILD' => (int) $booking->pax_children,
            'INFANT' => (int) $booking->pax_infants,
        ]);

        if ($scope === 'travellers') {
            // Infants go with their associated adult (confirmed live —
            // see infantPartners()): ignore infants picked on their own,
            // and add each selected adult's infant automatically.
            $partners = $this->infantPartners($travellers);
            $indexes = collect($travellerIndexes)
                ->map(fn ($i) => (int) $i)
                ->filter(fn ($i) => isset($travellers[$i]) && ! $this->isInfant($travellers[$i]))
                ->flatMap(fn ($i) => isset($partners[$i]) ? [$i, $partners[$i]] : [$i])
                ->unique()
                ->sort()
                ->values();

            $selected = $indexes->map(fn ($i) => $travellers[$i]);

            if ($selected->isEmpty() || ! $legs) {
                return null;
            }

            $names = $selected->map(fn ($t) => ['fn' => $t['fN'] ?? '', 'ln' => $t['lN'] ?? ''])->all();

            // Doc: specific-traveller scope still nests inside trips[] —
            // apply the selected travellers across every leg of the
            // booking, i.e. "remove this person from the whole trip".
            return [
                'trips' => collect($legs)->map(fn ($leg) => [
                    'src' => $leg['src'], 'dest' => $leg['dest'], 'departureDate' => $leg['departureDate'],
                    'travellers' => $names,
                ])->all(),
                'paxCounts' => $selected->countBy(fn ($t) => strtoupper($t['pt'] ?? 'ADULT'))->all(),
            ];
        }

        if ($scope === 'trip') {
            $trips = collect($legIndexes)
                ->map(fn ($i) => $legs[(int) $i] ?? null)
                ->filter()
                ->unique(fn ($leg) => $leg['src'].$leg['dest'].$leg['departureDate'])
                ->values()
                ->all();

            return $trips ? ['trips' => $trips, 'paxCounts' => $bookingPax] : null;
        }

        return ['trips' => [], 'paxCounts' => $bookingPax];
    }

    /**
     * Which adult each infant is attached to, by booking order (1st infant
     * → 1st adult, …). Confirmed live against a 2-adult + 1-infant sandbox
     * booking: the infant can't be cancelled alone, its adult can't be
     * cancelled without it ("Infant Associated Adult has to be selected for
     * amendment"), and the other adult can be cancelled on their own.
     *
     * @return array<int, int> adult travellerInfo index => infant index
     */
    public function infantPartners(array $travellers): array
    {
        $adults = collect($travellers)->filter(fn ($t) => strtoupper($t['pt'] ?? 'ADULT') === 'ADULT')->keys()->values();
        $infants = collect($travellers)->filter(fn ($t) => $this->isInfant($t))->keys()->values();

        $pairs = [];
        foreach ($infants as $n => $infantIndex) {
            if (isset($adults[$n])) {
                $pairs[$adults[$n]] = $infantIndex;
            }
        }

        return $pairs;
    }

    protected function isInfant(array $traveller): bool
    {
        return strtoupper($traveller['pt'] ?? '') === 'INFANT';
    }

    /**
     * Live cancellation quote from Get Amendment Charges (type
     * CANCELLATION) for the given scope. Nothing is cancelled. Confirmed
     * live response shape: trips[].amendmentInfo[PaxType] with
     * amendmentCharges / refundAmount / totalFare, which per the doc are
     * per pax of that type — so each is multiplied by the pax count.
     *
     * Converted into the guest's terms with the same ratio
     * finalizeCancellation() uses to refund, so the estimate shown matches
     * what is actually refunded. Null when TripJack won't quote.
     *
     * @return array{charges: float, refund: float, currency: string}|null
     */
    public function cancellationQuote(Booking $booking, array $trips, array $paxCounts): ?array
    {
        $cacheKey = 'flight_cancel_quote:'.$booking->id.':'.md5(json_encode([$trips, $paxCounts]));

        $airline = Cache::remember($cacheKey, now()->addMinutes(3), function () use ($booking, $trips, $paxCounts) {
            try {
                $response = app(TripJackFlightClient::class)
                    ->amendmentCharges($booking->tripjack_booking_id, 'CANCELLATION', 'Cancellation charge quote for guest', $trips);
            } catch (TripJackException $e) {
                Log::channel('tripjack')->warning('flight_cancel_quote_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

                return false;
            }

            if (! ($response['status']['success'] ?? false) || empty($response['trips'])) {
                return false;
            }

            $charges = 0.0;
            $refund = 0.0;
            foreach ($response['trips'] as $trip) {
                foreach (($trip['amendmentInfo'] ?? []) as $paxType => $info) {
                    $count = max(1, (int) ($paxCounts[strtoupper($paxType)] ?? 1));
                    $charges += (float) ($info['amendmentCharges'] ?? 0) * $count;
                    $refund += (float) ($info['refundAmount'] ?? 0) * $count;
                }
            }

            return ['charges' => $charges, 'refund' => $refund];
        });

        $supplierTotal = (float) $booking->tripjack_total_price;
        if (! $airline || $supplierTotal <= 0) {
            return null;
        }

        $payment = $this->farePayment($booking);
        $paid = $payment ? (float) $payment->amount : (float) $booking->total_amount;
        $stillRefundable = max(0, $paid - (float) ($payment->refund_amount ?? 0));

        $refund = min($stillRefundable, round($paid * min(1, $airline['refund'] / $supplierTotal), 2));
        $charges = round($paid * min(1, $airline['charges'] / $supplierTotal), 2);

        return ['charges' => $charges, 'refund' => $refund, 'currency' => $booking->currency ?? 'INR'];
    }

    /**
     * Cancellation / Auto Void — full booking by default, or scoped via
     * $trips (src/dest/departureDate, optionally with nested travellers[]
     * fn/ln) for a partial cancellation. Submits the amendment and hands off
     * the async poll (TripJack's own doc: "if REQUESTED, poll 4-5x with
     * 10-second intervals") to a queued job rather than blocking this
     * request on it.
     *
     * Only a FULL-booking cancellation claims cancellation_requested_at —
     * that flag drives the confirmation page's "Cancellation In Progress"
     * full-page state, which would be wrong to show while other travellers/
     * legs on the same booking are still flying.
     *
     * @param  array<int, array{src?: string, dest?: string, departureDate?: string, travellers?: array}>  $trips
     * @return array{success: bool, message: ?string}
     */
    public function submitCancellation(Booking $booking, array $trips = [], string $type = 'CANCELLATION', ?string $remarks = null): array
    {
        $isFullBooking = empty($trips);
        $remarks ??= $type === 'VOIDED' ? 'Same-day void requested by guest via TYTLUXE website.' : 'Cancelled by guest via TYTLUXE website.';

        if ($isFullBooking) {
            $claimed = DB::transaction(function () use ($booking) {
                $fresh = Booking::whereKey($booking->id)->lockForUpdate()->first();
                if ($fresh->cancellation_requested_at !== null) {
                    return false;
                }
                $fresh->update(['cancellation_requested_at' => now()]);

                return true;
            });

            if (! $claimed) {
                return ['success' => false, 'message' => null];
            }

            $booking->refresh();
        }

        // A partial scope doesn't claim cancellation_requested_at (other
        // travellers/legs stay live), so guard it with an atomic claim on
        // this exact scope instead — a double-click or resubmit can't raise
        // a second amendment while the first is still being polled (~72s).
        $partialClaimKey = $isFullBooking ? null : 'flight_partial_cancel:'.$booking->id.':'.md5(json_encode([$type, $trips]));
        if ($partialClaimKey && ! Cache::add($partialClaimKey, true, now()->addMinutes(3))) {
            return ['success' => false, 'message' => 'This cancellation is already being processed. Please check your booking page in a minute.'];
        }

        $client = app(TripJackFlightClient::class);

        try {
            $response = $client->submitAmendment($booking->tripjack_booking_id, $remarks, $type, $trips);
        } catch (TripJackException $e) {
            // Timed out / 5xx: the amendment may have been raised without us
            // getting its amendmentId — so nothing would poll or refund it.
            // Keep the claim (a resubmit would only hit "already raised") and
            // flag it for a manual check.
            if (self::isUncertainOutcome($e)) {
                $booking->update(['admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '')
                    ."Flight {$type} request got no answer from TripJack — check whether the amendment was raised, then finalise/refund manually.")]);
                Log::channel('tripjack')->critical('flight_amendment_submit_outcome_unknown', ['booking_id' => $booking->id, 'type' => $type, 'trips' => $trips, 'message' => $e->getMessage()]);

                return ['success' => false, 'message' => 'We\'ve sent your request to the airline but haven\'t had a reply yet. Our team is checking it and will update you — please don\'t submit it again.'];
            }

            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'We couldn\'t process this request right now. Please try again.');
            $this->logFailure($described['logLevel'], 'flight_cancel_failed', ['booking_id' => $booking->id, 'type' => $type, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
            if ($isFullBooking) {
                $booking->update(['cancellation_requested_at' => null]);
            } else {
                Cache::forget($partialClaimKey);
            }

            return ['success' => false, 'message' => $described['message']];
        }

        if (! ($response['status']['success'] ?? false) || empty($response['amendmentId'])) {
            $errorCode = TripJackFlightErrorCatalog::codeFromResponse($response);
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'This request could not be processed.');
            $this->logFailure($described['logLevel'], 'flight_cancel_unsuccessful', ['booking_id' => $booking->id, 'type' => $type, 'errorCode' => $errorCode, 'response' => $response]);
            if ($isFullBooking) {
                $booking->update(['cancellation_requested_at' => null]);
            } else {
                Cache::forget($partialClaimKey);
            }

            return ['success' => false, 'message' => $described['message']];
        }

        // Status stays 'confirmed' for a full-booking cancellation too — same
        // convention the hotel flow uses — cancellation_requested_at
        // (already set above) is what signals "cancellation in progress" to
        // the confirmation page; there's no separate enum value for it
        // (bookings.status is a real MySQL enum, not a free string, and this
        // state is transient/self-resolving). A partial/void amendment never
        // touches booking.status at all until finalizeCancellation() below.

        // 12s initial delay: submit-amendment needs a moment before its own
        // status is even meaningfully "REQUESTED" — mirrors the doc's
        // 10-second poll interval, offset slightly so the first poll isn't
        // wasted.
        PollFlightAmendmentJob::dispatch($booking->id, $response['amendmentId'], attempt: 1, isFullBooking: $isFullBooking, type: $type)
            ->delay(now()->addSeconds(12));

        return ['success' => true, 'message' => null];
    }

    /**
     * Full Refund (staff-triggered, Filament admin only — see
     * BookingsTable's "Process Full Refund" action) — always full-booking
     * scope, using TripJack's FULL_REFUND amendment type and one of their
     * documented remarks-checklist strings (e.g. the exact string "Refund
     * under DGCA policy" to trigger their DGCA automation).
     *
     * @return array{success: bool, message: ?string}
     */
    public function submitFullRefund(Booking $booking, string $remarks): array
    {
        return $this->submitCancellation($booking, trips: [], type: 'FULL_REFUND', remarks: $remarks);
    }

    /**
     * Called by PollFlightAmendmentJob once amendment-details resolves to a
     * terminal state. Auto-refunds only when TripJack's own refundableAmount
     * unambiguously covers the full payment — same "don't guess the split"
     * principle as the hotel flow's finalizeCancellation(). For a partial
     * (scoped) amendment, the booking's own status is never touched — only
     * flight_partial_amendments gets a new record — since other travellers/
     * legs on the same booking are still active.
     */
    public function finalizeCancellation(Booking $booking, array $amendmentDetails, bool $isFullBooking = true, string $type = 'CANCELLATION'): void
    {
        $status = $amendmentDetails['amendmentStatus'] ?? null;
        $razorpay = app(RazorpayService::class);

        if ($status === 'REJECTED') {
            if ($isFullBooking) {
                $booking->update([
                    'status' => 'confirmed', // cancellation didn't go through — booking is still live
                    'cancellation_requested_at' => null,
                    'admin_note' => 'Flight amendment was rejected by the airline/TripJack. Needs manual review if the guest still wants to proceed.',
                ]);
            } else {
                $this->appendPartialAmendment($booking, $amendmentDetails, $type, 'REJECTED', 0);
            }
            Log::channel('tripjack')->warning('flight_cancellation_rejected', ['booking_id' => $booking->id, 'isFullBooking' => $isFullBooking, 'amendmentDetails' => $amendmentDetails]);

            return;
        }

        if ($status !== 'SUCCESS') {
            return; // still REQUESTED/PENDING — job will poll again
        }

        $refundableAmount = (float) ($amendmentDetails['refundableAmount'] ?? 0);
        $payment = $this->farePayment($booking);

        $paymentHasBalance = $payment && (float) $payment->amount - (float) ($payment->refund_amount ?? 0) > 0.01;

        if ($paymentHasBalance && $refundableAmount > 0 && (float) $booking->tripjack_total_price > 0) {
            // Scale TripJack's raw-price refund against what the guest
            // actually paid us, same ratio technique as the hotel flow. For
            // a partial amendment this refundableAmount already reflects
            // only the cancelled trip/travellers (confirmed live), so the
            // same ratio math is correct for a partial refund too.
            $refundRatio = min(1, $refundableAmount / (float) $booking->tripjack_total_price);
            $alreadyRefunded = (float) ($payment->refund_amount ?? 0);
            $refundToGuest = min(round((float) $payment->amount - $alreadyRefunded, 2), round((float) $payment->amount * $refundRatio, 2));

            try {
                $razorpay->refund($payment->razorpay_payment_id, $refundToGuest);
                $newTotalRefunded = round($alreadyRefunded + $refundToGuest, 2);
                $payment->update([
                    'status' => $newTotalRefunded >= (float) $payment->amount - 0.01 ? 'refunded' : 'partially_refunded',
                    'refund_amount' => $newTotalRefunded,
                    'refund_reason' => $isFullBooking ? 'Flight cancellation refund.' : "Partial flight amendment refund ({$type}).",
                ]);

                if ($isFullBooking) {
                    $booking->update(['status' => 'cancelled', 'cancellation_reason' => "Cancelled. Refunded {$booking->currency} {$refundToGuest} automatically."]);
                } else {
                    $this->appendPartialAmendment($booking, $amendmentDetails, $type, 'SUCCESS', $refundToGuest);
                }
                Log::channel('tripjack')->info('flight_amendment_refunded', ['booking_id' => $booking->id, 'isFullBooking' => $isFullBooking, 'refund' => $refundToGuest]);

                return;
            } catch (\Throwable $e) {
                Log::channel('tripjack')->critical('flight_cancellation_refund_failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
                if ($isFullBooking) {
                    $booking->update(['status' => 'cancelled', 'cancellation_reason' => 'Cancelled — refund failed automatically and needs manual processing: '.$e->getMessage()]);
                } else {
                    $this->appendPartialAmendment($booking, $amendmentDetails, $type, 'SUCCESS_REFUND_FAILED', 0);
                }

                return;
            }
        }

        if ($isFullBooking) {
            $booking->update(['status' => 'cancelled', 'cancellation_reason' => 'Cancelled. No automatic refund applied — needs manual review.']);
        } else {
            $this->appendPartialAmendment($booking, $amendmentDetails, $type, 'SUCCESS_NO_REFUND', 0);
        }
        Log::channel('tripjack')->warning('flight_amendment_needs_manual_refund', ['booking_id' => $booking->id, 'isFullBooking' => $isFullBooking, 'amendmentDetails' => $amendmentDetails]);
    }

    /**
     * The payment that bought the ticket itself (Instant Book, or Confirm-
     * Book after a Hold) — never an extras or reschedule payment, which are
     * separate Payment rows on the same booking. Includes partially_refunded
     * so a second partial cancellation still refunds against it.
     */
    public function farePayment(Booking $booking): ?Payment
    {
        return $booking->payments()
            ->whereIn('purpose', ['booking', 'flight_confirm_book'])
            ->whereIn('status', ['captured', 'partially_refunded'])
            ->latest()
            ->first();
    }

    protected function appendPartialAmendment(Booking $booking, array $amendmentDetails, string $type, string $outcome, float $refundAmount): void
    {
        $records = $booking->flight_partial_amendments ?? [];
        $records[] = [
            'type' => $type,
            'outcome' => $outcome,
            'refundAmount' => $refundAmount,
            'trips' => $amendmentDetails['trips'] ?? null,
            'remarks' => $amendmentDetails['remarks'] ?? null,
            'resolvedAt' => now()->toISOString(),
        ];
        $booking->update(['flight_partial_amendments' => $records]);
    }

    protected function logFailure(string $logLevel, string $event, array $context): void
    {
        $logger = Log::channel('tripjack');
        match ($logLevel) {
            'critical' => $logger->critical($event, $context),
            'error' => $logger->error($event, $context),
            'info' => $logger->info($event, $context),
            default => $logger->warning($event, $context),
        };
    }
}
