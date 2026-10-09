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
use App\Support\RefundPolicy;
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
            $contactNumber = self::e164($booking->guest_phone);
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
     * A guest-entered dial code + number as one international number
     * ("+919876543210"), the form TripJack's deliveryInfo/contactInfo
     * contacts take. India (+91) keeps the last 10 digits, so a number typed
     * with its own "91"/"0" prefix isn't doubled up.
     */
    public static function phoneFromInput(?string $dialCode, string $number): string
    {
        $code = preg_replace('/\D/', '', (string) $dialCode) ?: '91';
        $digits = preg_replace('/\D/', '', $number);

        return '+'.$code.($code === '91' ? substr($digits, -10) : ltrim($digits, '0'));
    }

    /**
     * A stored booking phone as an international number. Newer bookings
     * already store "+<code><number>"; older ones stored 10 bare Indian
     * digits, which get +91.
     */
    public static function e164(?string $stored): string
    {
        $stored = trim((string) $stored);
        $digits = preg_replace('/\D/', '', $stored);

        return str_starts_with($stored, '+') ? '+'.$digits : '+91'.substr($digits, -10);
    }

    /**
     * TripJack's live order.status, or null if it couldn't be fetched.
     * Doc (Cancellation Amendment / Ancillaries): "booking must be in
     * SUCCESS state before raising any amendment" — our own 'confirmed'
     * is set as soon as Book is accepted, while TripJack may still be
     * PENDING, so amendments check this first.
     */
    public function liveOrderStatus(Booking $booking): ?string
    {
        try {
            return app(TripJackFlightClient::class)->bookingDetails($booking->tripjack_booking_id)['order']['status'] ?? null;
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('flight_live_status_lookup_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

            return null;
        }
    }

    public const INSUFFICIENT_FUNDS_MESSAGE = 'Flight bookings are temporarily unavailable. You haven\'t been charged — please try again a little later or contact our support team.';

    /**
     * Whether the TripJack account (User Detail API) can pay $amount — so a
     * guest is never charged for a booking TripJack would then refuse for
     * lack of balance. Fails open: if the lookup itself fails, carry on
     * (the post-payment refund path still covers that case).
     */
    public function hasTripJackFunds(float $amount): bool
    {
        try {
            $detail = app(TripJackFlightClient::class)->userDetail();
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('flight_balance_lookup_failed', ['message' => $e->getMessage()]);

            return true;
        }

        $total = self::tripJackBalance($detail);
        if ($total === null) {
            Log::channel('tripjack')->warning('flight_balance_missing_in_user_detail', ['keys' => array_keys($detail)]);

            return true;
        }

        if ($total + 0.01 < $amount) {
            Log::channel('tripjack')->critical('flight_insufficient_tripjack_balance', ['needed' => $amount, 'totalBalance' => $total]);

            return false;
        }

        return true;
    }

    /**
     * Spendable balance from a User Detail response: totalBalance, else
     * wallet + credit. Null when the response carries none of them, so an
     * odd/empty response is never read as "₹0 left".
     *
     * Confirmed live (sandbox, 2026-10-03): the balances are nested as
     * {user: {userId, bs: {totalBalance, walletBalance}}, status} — not at
     * the root as the doc's field table shows. Both layouts are accepted.
     */
    public static function tripJackBalance(array $detail): ?float
    {
        foreach ([$detail['user']['bs'] ?? null, $detail['user'] ?? null, $detail] as $balances) {
            if (! is_array($balances)) {
                continue;
            }
            if (isset($balances['totalBalance'])) {
                return (float) $balances['totalBalance'];
            }
            if (isset($balances['walletBalance']) || isset($balances['creditBalance'])) {
                return (float) ($balances['walletBalance'] ?? 0) + (float) ($balances['creditBalance'] ?? 0);
            }
        }

        return null;
    }

    public const NOT_TICKETED_YET_MESSAGE = 'The airline is still confirming this booking, so changes can\'t be made yet. Please try again in a few minutes.';

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
     * Every traveller's ticket numbers — ticketNumberDetails is per
     * traveller, keyed by sector ("DEL-BOM"), so reading only the first
     * traveller dropped everyone else's tickets. Null when none are issued
     * yet, so a caller can keep what it already has.
     *
     * @return array<int, array{name: string, tickets: array<string, string>}>|null
     */
    public static function ticketsByTraveller(array $details): ?array
    {
        $tickets = collect(self::airTravellers($details))
            ->filter(fn ($t) => ! empty($t['ticketNumberDetails']) && is_array($t['ticketNumberDetails']))
            ->map(fn ($t) => [
                'name' => trim(preg_replace('/\s+/', ' ', ($t['ti'] ?? '').' '.($t['fN'] ?? '').' '.($t['lN'] ?? ''))),
                'tickets' => $t['ticketNumberDetails'],
            ])
            ->values()
            ->all();

        return $tickets ?: null;
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
        $this->storeItinerary($booking, $details);

        $status = $details['order']['status'] ?? null;
        $expected = $resolvingUncertain ? 'failed_needs_review' : 'confirmed';

        if ($booking->status !== $expected) {
            return $this->isTerminal($status);
        }

        // A rescheduled booking now points at the reissue's bookingId — its
        // ticket payment was for the ORIGINAL ticket, so this booking's
        // outcome must never refund that.
        if ($booking->flight_reissued_at !== null && ! $resolvingUncertain) {
            return $this->applyReissueDetails($booking, $details);
        }

        if ($status === 'SUCCESS') {
            $traveller = self::airTravellers($details)[0] ?? [];
            $booking->update([
                'status' => 'confirmed',
                'tripjack_flight_pnr' => $traveller['pnrDetails'] ?? $booking->tripjack_flight_pnr,
                'tripjack_flight_ticket_numbers' => self::ticketsByTraveller($details) ?? $booking->tripjack_flight_ticket_numbers,
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
     * Booking Details for a reissue's new bookingId (Auto Reissue, like
     * Book, only confirms the request — PNR and tickets follow via Booking
     * Details after 5s). SUCCESS saves the new PNR/tickets; a failed reissue
     * is flagged for staff rather than refunded automatically, since what
     * happened to the original ticket has to be checked with TripJack.
     *
     * @return bool true once TripJack's status is final
     */
    protected function applyReissueDetails(Booking $booking, array $details): bool
    {
        $status = $details['order']['status'] ?? null;

        if ($status === 'SUCCESS') {
            $booking->update([
                'tripjack_flight_pnr' => self::airTravellers($details)[0]['pnrDetails'] ?? $booking->tripjack_flight_pnr,
                'tripjack_flight_ticket_numbers' => self::ticketsByTraveller($details) ?? $booking->tripjack_flight_ticket_numbers,
            ]);

            return true;
        }

        if ($status === 'CANCELLED') {
            $booking->update(['status' => 'cancelled']);

            return true;
        }

        if (in_array($status, ['FAILED', 'ABORTED', 'UNCONFIRMED'], true)) {
            $note = "Reschedule to TripJack booking {$booking->tripjack_booking_id} ended {$status} — check the original and new booking with TripJack, then fix the booking/refund the reschedule payment manually.";
            if (! str_contains((string) $booking->admin_note, $note)) {
                $booking->update(['admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '').$note)]);
                Log::channel('tripjack')->critical('flight_reissue_not_ticketed', ['booking_id' => $booking->id, 'tripjack_booking_id' => $booking->tripjack_booking_id, 'status' => $status]);
            }

            return true;
        }

        return false;
    }

    /**
     * Saves airline, flight number and times per segment from a Booking
     * Details response, for the admin booking page (which never calls
     * TripJack on load). Segments sit under itemInfos.AIR.tripInfos[].sI[],
     * next to travellerInfos — see airTravellers().
     */
    public function storeItinerary(Booking $booking, array $details): void
    {
        $itinerary = self::itinerarySummary($details);
        if ($itinerary && $itinerary !== $booking->flight_itinerary) {
            $booking->update(['flight_itinerary' => $itinerary]);
        }
    }

    /**
     * Segments plus every traveller's ticket numbers, keyed by name, for the
     * admin booking page. Empty when the response has no segments.
     *
     * @return array{segments?: array<int, array{airline: string, airlineCode: string, flightNo: string, from: string, fromCity: string, to: string, toCity: string, departs: ?string, arrives: ?string}>, tickets?: array<string, array>}
     */
    public static function itinerarySummary(array $details): array
    {
        $tripInfos = $details['itemInfos']['AIR']['tripInfos'] ?? $details['tripInfos'] ?? [];

        $segments = collect($tripInfos)
            ->flatMap(fn ($trip) => $trip['sI'] ?? [])
            ->map(fn (array $seg) => [
                'airline' => (string) ($seg['fD']['aI']['name'] ?? ''),
                'airlineCode' => (string) ($seg['fD']['aI']['code'] ?? ''),
                'flightNo' => (string) ($seg['fD']['fN'] ?? ''),
                'from' => (string) ($seg['da']['code'] ?? ''),
                'fromCity' => (string) ($seg['da']['city'] ?? ''),
                'to' => (string) ($seg['aa']['code'] ?? ''),
                'toCity' => (string) ($seg['aa']['city'] ?? ''),
                'departs' => $seg['dt'] ?? null,
                'arrives' => $seg['at'] ?? null,
            ])
            ->filter(fn (array $seg) => $seg['from'] !== '' && $seg['to'] !== '')
            ->values()
            ->all();

        if (! $segments) {
            return [];
        }

        $tickets = collect(self::airTravellers($details))
            ->mapWithKeys(fn (array $t) => [strtoupper(trim(($t['fN'] ?? '').' '.($t['lN'] ?? ''))) => $t['ticketNumberDetails'] ?? []])
            ->filter()
            ->all();

        return ['segments' => $segments, 'tickets' => $tickets];
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

        // Doc case (4): "Fare alert — inform customer". Confirm-Book must be
        // sent the CURRENT fare (a mismatch is error 1015 after the guest
        // has paid), so re-price the booking and have the guest confirm the
        // new amount before any payment is taken.
        $fareAlert = collect($response['alerts'] ?? [])->first(fn ($a) => ($a['type'] ?? '') === 'FAREALERT');
        $hasFareAlert = $fareAlert !== null;
        $newFare = $response['totalPriceInfo']['totalFareDetail']['fC']['TF'] ?? $fareAlert['newFare'] ?? null;

        if ($newFare !== null && abs((float) $newFare - (float) $booking->tripjack_total_price) >= 0.01) {
            $oldPrice = (float) $booking->total_amount;
            $oldFare = (float) $booking->tripjack_total_price;
            $this->repriceHold($booking, (float) $newFare);
            Log::channel('tripjack')->info('flight_hold_fare_changed', ['booking_id' => $booking->id, 'oldTF' => $oldFare, 'newTF' => $newFare]);

            return ['ok' => false, 'message' => 'The airline has changed this fare from ₹'.number_format($oldPrice, 2).' to ₹'.number_format((float) $booking->total_amount, 2).'. Press Confirm & Pay again to continue at the new price.'];
        }

        if ($hasFareAlert && $newFare === null) {
            // Changed, but we can't tell to what — can't safely charge.
            Log::channel('tripjack')->warning('flight_hold_fare_alert_without_amount', ['booking_id' => $booking->id, 'response' => $response]);

            return ['ok' => false, 'message' => 'The airline has changed this fare. Please search again to see the current price.'];
        }

        return ['ok' => true, 'message' => null];
    }

    /**
     * Applies a new TripJack TF to a held booking with the same markup
     * formula as at booking time, and voids any unpaid Razorpay order made
     * at the old amount so confirmHold() creates a fresh one.
     */
    protected function repriceHold(Booking $booking, float $newFare): void
    {
        $breakdown = FlightPricingService::price($newFare);

        $booking->update([
            'base_amount' => $breakdown['tripjack_total_price'],
            'tax_amount' => $breakdown['customer_price'] - $breakdown['tripjack_total_price'],
            'total_amount' => $breakdown['customer_price'],
            'tripjack_total_price' => $breakdown['tripjack_total_price'],
            'gst_slab' => $breakdown['gst_slab'],
            'margin_amount' => $breakdown['margin_amount'],
            'gst_on_margin' => $breakdown['gst_on_margin'],
            'razorpay_recovery' => $breakdown['razorpay_recovery'],
        ]);

        $booking->payments()
            ->where('purpose', 'flight_confirm_book')
            ->where('status', 'created')
            ->update(['status' => 'failed']);
    }

    /**
     * Releases a held (unpaid) PNR — POST /air/unhold — at the guest's
     * request, or a staff member's ($releasedBy = their name). Doesn't touch
     * payments (Hold never captured any), just the booking status and the
     * supplier-side PNR.
     */
    public function releaseHold(Booking $booking, ?string $releasedBy = null): void
    {
        $client = app(TripJackFlightClient::class);
        $pnrs = array_values(array_unique((array) ($booking->tripjack_flight_pnr ?? [])));
        $released = false;

        try {
            $client->unhold($booking->tripjack_booking_id, $pnrs);

            // Doc (Release PNR): "verify by checking Booking Details — order
            // status should be UNCONFIRMED".
            $status = $client->bookingDetails($booking->tripjack_booking_id)['order']['status'] ?? null;
            $released = $status === 'UNCONFIRMED';
            if (! $released) {
                Log::channel('tripjack')->warning('flight_unhold_not_unconfirmed', ['booking_id' => $booking->id, 'orderStatus' => $status]);
            }
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode);
            $this->logFailure($described['logLevel'], 'flight_unhold_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
        }

        // Still cancelled on our side either way — someone asked to walk
        // away, no payment was ever taken, and an unreleased hold expires
        // with the supplier at its timeLimit. Staff get a note if TripJack
        // didn't confirm the release.
        $booking->update(array_filter([
            'status' => 'cancelled',
            'cancellation_reason' => $releasedBy !== null
                ? "Hold released by the TYT Luxe team ({$releasedBy}) before payment."
                : 'Hold released by guest before payment.',
            'admin_note' => $released ? null : trim(($booking->admin_note ? $booking->admin_note.' ' : '')
                .'Release PNR was not confirmed by TripJack (status not UNCONFIRMED) — check the hold in TripJack; it will otherwise lapse at its time limit.'),
        ], fn ($v) => $v !== null));
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
            // The money was taken and is still with us — the payment stays
            // captured (not "failed") until staff refund it by hand.
            $payment->update(['refund_reason' => $reason]);
            $booking->update([
                'status' => 'failed_needs_review',
                'manual_refund_due_at' => now(),
                'admin_note' => $booking->adminNoteWith("The ticket could not be issued ({$reason}) and the automatic refund of {$booking->currency} {$payment->amount} also failed ({$e->getMessage()}). Refund the guest in Razorpay, then click \"Record manual refund\"."),
            ]);
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
     * Converted into the guest's terms with the same RefundPolicy rule
     * finalizeCancellation() refunds with, so the estimate shown matches
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
            $fare = 0.0;
            foreach ($response['trips'] as $trip) {
                foreach (($trip['amendmentInfo'] ?? []) as $paxType => $info) {
                    $count = max(1, (int) ($paxCounts[strtoupper($paxType)] ?? 1));
                    $charges += (float) ($info['amendmentCharges'] ?? 0) * $count;
                    $refund += (float) ($info['refundAmount'] ?? 0) * $count;
                    $fare += (float) ($info['totalFare'] ?? 0) * $count;
                }
            }

            return ['charges' => $charges, 'refund' => $refund, 'fare' => $fare];
        });

        // Same payments and TripJack cost finalizeCancellation() uses: the
        // fare payment plus any reschedule payment (taken at TripJack's price).
        $payments = $booking->payments()
            ->whereIn('purpose', ['booking', 'flight_confirm_book', 'flight_reissue'])
            ->whereIn('status', ['captured', 'partially_refunded'])
            ->get();
        $supplierTotal = (float) $booking->tripjack_total_price + (float) $payments->where('purpose', 'flight_reissue')->sum('amount');
        if (! $airline || $supplierTotal <= 0) {
            return null;
        }
        $paid = $payments->isNotEmpty() ? (float) $payments->sum('amount') : (float) $booking->total_amount;
        $stillRefundable = max(0, round($paid - (float) $payments->sum('refund_amount'), 2));

        // RefundPolicy, exactly as finalizeCancellation() refunds: the whole
        // booking → TripJack's refund, or everything paid less the flat fee
        // when free; part of it → TripJack's refund as is. Charges = what the
        // guest paid for that part (their payment in proportion to TripJack's
        // fare for it) minus the refund.
        $isFull = $trips === [];
        $refund = $isFull
            ? RefundPolicy::cancellationRefund($stillRefundable, $supplierTotal, $airline['refund'])
            : round(min($stillRefundable, $airline['refund']), 2);
        $supplierPart = ! $isFull && (float) ($airline['fare'] ?? 0) > 0 ? min($supplierTotal, (float) $airline['fare']) : $supplierTotal;
        $charges = round(max(0, $paid * $supplierPart / $supplierTotal - $refund), 2);

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

        // Only block on a definite non-SUCCESS answer — if the lookup itself
        // fails, TripJack's own amendment validation still applies.
        $liveStatus = $this->liveOrderStatus($booking);
        if ($liveStatus !== null && $liveStatus !== 'SUCCESS') {
            return ['success' => false, 'message' => self::NOT_TICKETED_YET_MESSAGE];
        }

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
     * TripJack's Auto Full Refund remarks checklist (doc, "Remarks
     * Checklist") — these exact strings trigger their automated refund
     * processing; "Refund under DGCA policy" drives the DGCA automation.
     */
    public const FULL_REFUND_REMARKS = [
        'Flight Cancelled by Airline',
        'Airline rescheduled flight, revised timings are not suitable',
        'Already cancelled by directly contacting airline customer support team',
        'Airline confirmed, refund is already processed',
        'Refund under DGCA policy',
        'Personal loss or bereavement',
        'Passenger is medically unfit for travel',
        'Refund under empowerment policy',
    ];

    /**
     * Full Refund (staff-triggered, Filament admin only — see
     * BookingsTable's "TripJack Full Refund" action) — always full-booking
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
                    'admin_note' => $booking->adminNoteWith('Flight amendment was rejected by the airline/TripJack. Needs manual review if the guest still wants to proceed.'),
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

        // Everything the guest paid for the flight itself, newest first: a
        // reschedule payment (taken at TripJack's own price) before the
        // original fare payment. Razorpay refunds each payment separately.
        $payments = $booking->payments()
            ->whereIn('purpose', ['booking', 'flight_confirm_book', 'flight_reissue'])
            ->whereIn('status', ['captured', 'partially_refunded'])
            ->where('amount', '>', 0)
            ->orderByDesc('id')
            ->get();
        $remaining = round((float) $payments->sum(fn (Payment $p) => (float) $p->amount - (float) ($p->refund_amount ?? 0)), 2);
        $supplierCost = (float) $booking->tripjack_total_price
            + (float) $payments->where('purpose', 'flight_reissue')->sum('amount');

        // RefundPolicy (business rule, 2026-10-08): TripJack's own refund —
        // our markup isn't refunded — or, when TripJack refunds its whole
        // price, everything paid less the flat cancellation fee. An airline-
        // caused Full Refund (flight cancelled, DGCA, …) gives back everything,
        // like a booking that failed. A partial cancellation gets TripJack's
        // refund as is: amendment-details doesn't give that part's fare.
        $refundToGuest = match (true) {
            $type === 'FULL_REFUND' && $isFullBooking => $remaining,
            $isFullBooking => RefundPolicy::cancellationRefund($remaining, $supplierCost, $refundableAmount),
            default => round(min($remaining, $refundableAmount), 2),
        };

        if ($refundToGuest > 0 && $refundableAmount > 0) {
            $refunded = 0.0;
            try {
                foreach ($payments as $payment) {
                    $take = round(min($refundToGuest - $refunded, (float) $payment->amount - (float) ($payment->refund_amount ?? 0)), 2);
                    if ($take <= 0) {
                        continue;
                    }
                    $razorpay->refund($payment->razorpay_payment_id, $take);
                    $newTotalRefunded = round((float) ($payment->refund_amount ?? 0) + $take, 2);
                    $payment->update([
                        'status' => $newTotalRefunded >= (float) $payment->amount - 0.01 ? 'refunded' : 'partially_refunded',
                        'refund_amount' => $newTotalRefunded,
                        'refund_reason' => $isFullBooking ? 'Flight cancellation refund.' : "Partial flight amendment refund ({$type}).",
                    ]);
                    $refunded = round($refunded + $take, 2);
                }

                if ($isFullBooking) {
                    $booking->update(['status' => 'cancelled', 'cancellation_reason' => "Cancelled. Refunded {$booking->currency} {$refunded} automatically."]);
                } else {
                    $this->appendPartialAmendment($booking, $amendmentDetails, $type, 'SUCCESS', $refunded);
                }
                Log::channel('tripjack')->info('flight_amendment_refunded', ['booking_id' => $booking->id, 'isFullBooking' => $isFullBooking, 'refund' => $refunded, 'tripjackRefund' => $refundableAmount]);

                return;
            } catch (\Throwable $e) {
                Log::channel('tripjack')->critical('flight_cancellation_refund_failed', ['booking_id' => $booking->id, 'refunded_before_error' => $refunded, 'error' => $e->getMessage()]);
                $owed = round($refundToGuest - $refunded, 2);
                $note = "Flight cancelled — the automatic refund of {$booking->currency} {$refundToGuest} failed after {$booking->currency} {$refunded} ({$e->getMessage()}). Refund the remaining {$booking->currency} {$owed} in Razorpay, then click \"Record manual refund\".";
                if ($isFullBooking) {
                    $booking->update([
                        'status' => 'cancelled',
                        'cancellation_reason' => 'Cancelled — refund failed automatically and needs manual processing: '.$e->getMessage(),
                        'manual_refund_due_at' => now(),
                        'admin_note' => $booking->adminNoteWith($note),
                    ]);
                } else {
                    $this->appendPartialAmendment($booking, $amendmentDetails, $type, 'SUCCESS_REFUND_FAILED', $refunded);
                    $booking->update(['manual_refund_due_at' => now(), 'admin_note' => $booking->adminNoteWith($note)]);
                }

                return;
            }
        }

        if ($isFullBooking) {
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $refundableAmount > 0
                    ? 'Cancelled. No automatic refund applied — needs manual review.'
                    : 'Cancelled. TripJack reported no refundable amount — no refund due.',
            ]);
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
