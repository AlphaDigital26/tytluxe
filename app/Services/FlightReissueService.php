<?php

namespace App\Services;

use App\Jobs\PollFlightBookingStatusJob;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackFlightClient;
use App\Services\TripJack\TripJackFlightErrorCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Auto Reissue — reschedule a confirmed booking to a new date on the same
 * route. Four-step TripJack flow (Search query-list → Search with
 * requestId → Review → Book), confirmed end-to-end against a live sandbox
 * booking. Doc constraints, enforced here: one trip at a time, a booking
 * can only ever be reissued once, cabin class can't be downgraded.
 *
 * Same pay-first-then-confirm pattern as the rest of the flight flow — the
 * new Book call only happens after Razorpay captures payment, via
 * FrontendController::confirmBookingAfterPayment()'s 'flight_reissue'
 * purpose branch — so flight_reissue_pending (not PHP session) carries
 * everything Book needs, since the confirming request may be Razorpay's
 * server-to-server webhook with no session at all.
 */
class FlightReissueService
{
    public function isEligible(Booking $booking): bool
    {
        return $booking->vertical === 'flight'
            && $booking->status === 'confirmed'
            && $booking->tripjack_booking_id
            && $booking->flight_reissued_at === null
            && $booking->flight_departure_date
            && $booking->flight_departure_date->isFuture();
    }

    /**
     * Step 1+2 combined — fetches fresh PNR/pax IDs from Booking Details
     * (never trusts stale local data for this), then runs the reissue
     * search. Returns a flat list of fare options for the view, plus the
     * requestId echoed back for no particular reason other than symmetry
     * with the rest of this codebase's controller/service split.
     *
     * @return array{success: bool, message: ?string, options: array}
     */
    public function search(Booking $booking, int $legIndex, string $newDate): array
    {
        $legs = $booking->flight_legs ?? [];
        $leg = $legs[$legIndex] ?? null;
        if (! $leg) {
            return ['success' => false, 'message' => 'Invalid flight selected.', 'options' => []];
        }

        $client = app(TripJackFlightClient::class);

        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            return ['success' => false, 'message' => 'We couldn\'t look up this booking right now. Please try again.', 'options' => []];
        }

        // Doc: amendments (reissue included) need the booking in SUCCESS.
        if (($details['order']['status'] ?? null) !== 'SUCCESS') {
            return ['success' => false, 'message' => FlightBookingService::NOT_TICKETED_YET_MESSAGE, 'options' => []];
        }

        $pnr = $details['itemInfos']['AIR']['travellerInfos'][0]['pnrDetails'][$leg['src'].'-'.$leg['dest']]
            ?? collect($details['itemInfos']['AIR']['travellerInfos'][0]['pnrDetails'] ?? [])->first();
        $paxIds = collect($details['itemInfos']['AIR']['travellerInfos'] ?? [])->pluck('id')->map(fn ($id) => (string) $id)->all();

        if (! $pnr || ! $paxIds) {
            return ['success' => false, 'message' => 'This booking\'s PNR could not be found — please contact support.', 'options' => []];
        }

        $paxInfo = array_filter([
            'ADULT' => $booking->pax_adults,
            'CHILD' => $booking->pax_children ?: null,
            'INFANT' => $booking->pax_infants ?: null,
        ], fn ($v) => $v !== null);

        try {
            $queryList = $client->reissueSearchQueryList(
                $paxInfo,
                [['fromCityOrAirport' => ['code' => $leg['src']], 'toCityOrAirport' => ['code' => $leg['dest']], 'travelDate' => $newDate]],
                $pnr,
                $booking->tripjack_booking_id,
                $paxIds,
            );
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'This flight can\'t be rescheduled right now. Please try again.');
            Log::channel('tripjack')->warning('flight_reissue_search_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);

            return ['success' => false, 'message' => $described['message'], 'options' => []];
        }

        // Confirmed live (2026-10-01): the response carries `requestIds`
        // (a list), not the doc's single `requestId` — accept either.
        $requestId = $queryList['requestId'] ?? ($queryList['requestIds'][0] ?? null);

        if (! ($queryList['status']['success'] ?? false) || empty($requestId)) {
            $errorCode = TripJackFlightErrorCatalog::codeFromResponse($queryList);
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'No reschedule options were found for this date.');

            return ['success' => false, 'message' => $described['message'], 'options' => []];
        }

        try {
            $results = $client->reissueSearch((string) $requestId);
        } catch (TripJackException $e) {
            // Confirmed live: 1148 "No reissue results found for the given
            // booking" is a normal "nothing on this date" answer, not a
            // failure — the page shows its "try a different date" note.
            if ($e instanceof TripJackApiException && (string) $e->errorCode === '1148') {
                return ['success' => true, 'message' => null, 'options' => []];
            }
            Log::channel('tripjack')->warning('flight_reissue_results_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

            return ['success' => false, 'message' => 'We couldn\'t fetch reschedule options right now. Please try again.', 'options' => []];
        }

        $tripInfos = $results['searchResult']['tripInfos'] ?? [];
        $itineraries = $tripInfos['ONWARD'] ?? (is_array(reset($tripInfos)) ? reset($tripInfos) : []);
        $paxCounts = array_map('intval', $paxInfo);

        $options = [];
        foreach ($itineraries as $itinerary) {
            $segments = $itinerary['sI'] ?? [];
            if (! $segments) {
                continue;
            }
            $first = $segments[0];
            $last = end($segments);

            foreach (($itinerary['totalPriceList'] ?? []) as $priceOption) {
                $options[] = [
                    'id' => $priceOption['id'],
                    'airlineCode' => $first['fD']['aI']['code'] ?? '',
                    'airlineName' => $first['fD']['aI']['name'] ?? '',
                    'flightNos' => collect($segments)->map(fn ($s) => ($s['fD']['aI']['code'] ?? '').' '.($s['fD']['fN'] ?? ''))->implode(', '),
                    'from' => $first['da']['code'] ?? $leg['src'],
                    'to' => $last['aa']['code'] ?? $leg['dest'],
                    'depTime' => $first['dt'] ?? '',
                    'arrTime' => $last['at'] ?? '',
                    'stops' => count($segments) - 1,
                    'via' => collect($segments)->slice(0, -1)->map(fn ($s) => $s['aa']['code'] ?? '')->filter()->implode(', '),
                    'duration' => $this->journeyMinutes($segments),
                    'breakdown' => $breakdown = $this->reissueBreakdown($priceOption['fd'] ?? [], $paxCounts),
                    'amountPayable' => $breakdown['total'],
                ];
            }
        }

        usort($options, fn ($a, $b) => $a['amountPayable'] <=> $b['amountPayable']);

        return ['success' => true, 'message' => null, 'options' => $options];
    }

    /**
     * Totals TripJack's per-pax reissue fare components across every
     * traveller. Confirmed against live sandbox reissue searches: TF = BF
     * (fare difference) + TAF (tax difference) + AFS (reissue fees) − any
     * refundable ancillaries, with afC.AFS splitting AFS into ARF (airline
     * fee) and the rest (CRF etc. — TripJack's own fee).
     *
     * @param  array<string, array>  $fd  totalPriceList[].fd
     * @param  array<string, int>  $paxCounts
     * @return array{fareDifference: float, airlineFee: float, serviceFee: float, ancillaryRefund: float, total: float}
     */
    protected function reissueBreakdown(array $fd, array $paxCounts): array
    {
        $sum = ['fareDifference' => 0.0, 'airlineFee' => 0.0, 'serviceFee' => 0.0, 'ancillaryRefund' => 0.0, 'total' => 0.0];

        foreach ($fd as $paxType => $detail) {
            $count = $paxCounts[strtoupper($paxType)] ?? 0;
            if ($count <= 0) {
                continue;
            }
            $fc = $detail['fC'] ?? [];
            $afs = (float) ($fc['AFS'] ?? 0);
            $afsParts = $detail['afC']['AFS'] ?? $detail['afc']['AFS'] ?? [];
            $airlineFee = min($afs, (float) ($afsParts['ARF'] ?? 0) + (float) ($afsParts['ARFT'] ?? 0));
            $fareDifference = (float) ($fc['BF'] ?? 0) + (float) ($fc['TAF'] ?? 0);
            $tf = (float) ($fc['TF'] ?? 0);

            // RSSR's sign isn't consistent (the doc subtracts a positive
            // value; live it arrived as −2375), so derive the ancillary
            // refund from TF itself — the rows then always add up.
            $ancillaryRefund = max(0, $fareDifference + $afs - $tf);

            $sum['fareDifference'] += $fareDifference * $count;
            $sum['airlineFee'] += $airlineFee * $count;
            $sum['serviceFee'] += ($afs - $airlineFee) * $count;
            $sum['ancillaryRefund'] += $ancillaryRefund * $count;
            $sum['total'] += $tf * $count;
        }

        return array_map(fn ($v) => round($v, 2), $sum);
    }

    protected function journeyMinutes(array $segments): ?int
    {
        // dt/at are local airport times, so use TripJack's own per-segment
        // duration + connecting time (cT) — same as the results page.
        $minutes = collect($segments)->sum(fn ($s) => (int) ($s['duration'] ?? 0) + (int) ($s['cT'] ?? 0));

        return $minutes > 0 ? $minutes : null;
    }

    /**
     * Step 3 — revalidates the chosen option and stores everything Book
     * will need on the booking row (not session — see class docblock).
     *
     * @return array{success: bool, message: ?string, amount: float}
     */
    public function review(Booking $booking, string $priceId, int $legIndex): array
    {
        $client = app(TripJackFlightClient::class);

        try {
            $response = $client->reissueReview([$priceId], $booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'This fare is no longer available. Please choose another.');

            return ['success' => false, 'message' => $described['message'], 'amount' => 0];
        }

        if (! ($response['status']['success'] ?? false) || empty($response['bookingId'])) {
            $errorCode = TripJackFlightErrorCatalog::codeFromResponse($response);
            $described = TripJackFlightErrorCatalog::describe($errorCode, 'This fare is no longer available. Please choose another.');

            return ['success' => false, 'message' => $described['message'], 'amount' => 0];
        }

        $amount = (float) ($response['totalPriceInfo']['totalFareDetail']['fC']['TF'] ?? 0);
        $seg = $response['tripInfos'][0]['sI'][0] ?? null;

        $booking->update(['flight_reissue_pending' => [
            'newBookingId' => $response['bookingId'],
            'oldBookingId' => $booking->tripjack_booking_id,
            'legIndex' => $legIndex,
            'newDepartureDate' => $seg['dt'] ?? null,
            'amount' => $amount,
            'travellerInfo' => $response['travellers'] ?? [],
            'gstInfo' => ($response['conditions']['gst']['igm'] ?? false) ? $booking->tripjack_gst_info : null,
        ]]);

        return ['success' => true, 'message' => null, 'amount' => $amount];
    }

    /**
     * Called from FrontendController::confirmBookingAfterPayment() once the
     * reissue payment is captured. Books the reissue and, on success,
     * REPLACES the booking's tripjack_booking_id/PNR/departure date with
     * the new ones — TripJack's reissue creates a fresh bookingId, the old
     * one stays queryable for history but is no longer "the booking".
     */
    public function confirmAfterPayment(Payment $payment, RazorpayService $razorpay): void
    {
        $client = app(TripJackFlightClient::class);

        DB::transaction(function () use ($payment, $client, $razorpay) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->first();

            if (in_array($payment->status, ['captured', 'refunded', 'partially_refunded'], true) || $booking->flight_reissued_at !== null) {
                return; // already processed by a racing callback/webhook
            }

            $payment->update(['status' => 'captured']);

            $pending = $booking->flight_reissue_pending;
            if (! $pending) {
                Log::channel('tripjack')->critical('flight_reissue_pending_missing', ['booking_id' => $booking->id, 'payment_id' => $payment->id]);
                $this->refundAndFail($booking, $payment, $razorpay, 'Your reschedule selection could not be found after payment.');

                return;
            }

            try {
                $response = $client->autoReissueBook(
                    $pending['newBookingId'],
                    $pending['oldBookingId'],
                    (float) $pending['amount'],
                    $pending['travellerInfo'],
                    [$booking->guest_email],
                    [FlightBookingService::e164($booking->guest_phone)],
                    $pending['gstInfo'] ?? null,
                );
            } catch (TripJackException $e) {
                // Timed out / 5xx: TripJack may have reissued the ticket.
                // Keep flight_reissue_pending (it holds the new bookingId to
                // check) and flag it rather than refunding blind.
                if (FlightBookingService::isUncertainOutcome($e)) {
                    $booking->update(['admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '')
                        ."Auto Reissue request (new booking {$pending['newBookingId']}, payment #{$payment->id}) got no answer from TripJack — check its Booking Details, then complete the reschedule or refund.")]);
                    Log::channel('tripjack')->critical('flight_reissue_book_outcome_unknown', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'newBookingId' => $pending['newBookingId'], 'message' => $e->getMessage()]);

                    return;
                }

                $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
                $described = TripJackFlightErrorCatalog::describe($errorCode);
                Log::channel('tripjack')->{$described['logLevel'] === 'critical' ? 'critical' : 'warning'}('flight_reissue_book_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
                $this->refundAndFail($booking, $payment, $razorpay, $described['message']);

                return;
            }

            if (! ($response['status']['success'] ?? false)) {
                $errorCode = TripJackFlightErrorCatalog::codeFromResponse($response);
                $described = TripJackFlightErrorCatalog::describe($errorCode, 'This reschedule could not be confirmed after payment.');
                Log::channel('tripjack')->warning('flight_reissue_book_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
                $this->refundAndFail($booking, $payment, $razorpay, $described['message']);

                return;
            }

            $newBookingId = $pending['newBookingId'];

            $history = $booking->flight_reissue_history ?? [];
            $history[] = [
                'oldBookingId' => $pending['oldBookingId'],
                'oldDepartureDate' => $booking->flight_departure_date?->toDateString(),
                'newBookingId' => $newBookingId,
                'newDepartureDate' => $pending['newDepartureDate'],
                'amountPaid' => $pending['amount'],
                'resolvedAt' => now()->toISOString(),
            ];

            $legs = $booking->flight_legs ?? [];
            if (isset($legs[$pending['legIndex']]) && $pending['newDepartureDate']) {
                $legs[$pending['legIndex']]['departureDate'] = substr($pending['newDepartureDate'], 0, 10);
            }

            // The old PNR/tickets no longer apply; the new ones come from
            // Booking Details, which (as after Book) TripJack's doc says to
            // call only after 5 seconds — PollFlightBookingStatusJob fills
            // them in (FlightBookingService::applyReissueDetails()).
            $booking->update([
                'tripjack_booking_id' => $newBookingId,
                'tripjack_flight_pnr' => null,
                'tripjack_flight_ticket_numbers' => null,
                'tripjack_confirm_attempted_at' => now(),
                'flight_departure_date' => $pending['legIndex'] === 0 ? substr($pending['newDepartureDate'], 0, 10) : $booking->flight_departure_date,
                'flight_reissued_at' => now(),
                'flight_reissue_history' => $history,
                'flight_reissue_pending' => null,
                'flight_legs' => $legs,
            ]);

            Log::channel('tripjack')->info('flight_reissue_confirmed', ['booking_id' => $booking->id, 'newBookingId' => $newBookingId]);

            PollFlightBookingStatusJob::dispatch($booking->id)->delay(now()->addSeconds(5))->afterCommit();
        });
    }

    protected function refundAndFail(Booking $booking, Payment $payment, RazorpayService $razorpay, string $reason): void
    {
        // A no-charge reschedule (TF ≤ 0) never went through Razorpay.
        if ((float) $payment->amount <= 0 || ! $payment->razorpay_payment_id) {
            $payment->update(['status' => 'failed', 'refund_reason' => $reason]);
            $booking->update(['flight_reissue_pending' => null]);
            Log::channel('tripjack')->warning('flight_reissue_failed_nothing_to_refund', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $reason]);

            return;
        }

        try {
            $razorpay->refund($payment->razorpay_payment_id, (float) $payment->amount);
            $payment->update(['status' => 'refunded', 'refund_amount' => $payment->amount, 'refund_reason' => $reason]);
            $booking->update(['flight_reissue_pending' => null]);
            Log::channel('tripjack')->critical('flight_reissue_refunded_after_failure', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $reason]);
        } catch (\Throwable $e) {
            // The money was taken and is still with us — the payment stays
            // captured (not "failed") until staff refund it by hand.
            $payment->update(['refund_reason' => $reason]);
            $booking->update([
                'flight_reissue_pending' => null,
                'manual_refund_due_at' => now(),
                'admin_note' => $booking->adminNoteWith("Reschedule payment of {$booking->currency} {$payment->amount} could not be refunded automatically ({$e->getMessage()}). Refund the guest in Razorpay, then click \"Record manual refund\"."),
            ]);
            Log::channel('tripjack')->critical('flight_reissue_refund_after_failure_errored', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $reason, 'refund_error' => $e->getMessage()]);
        }
    }
}
