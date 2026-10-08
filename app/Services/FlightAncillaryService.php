<?php

namespace App\Services;

use App\Jobs\PollFlightSsrAmendmentJob;
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
 * Post-booking Seat/Meal/Baggage add-ons (TripJack's "Ancillaries (SSR)"
 * API family) — Fetch SSR → Fetch Seat → Add SSR → Amendment Details.
 * Confirmed against a live sandbox booking end-to-end (see the Add SSR
 * docblock on TripJackFlightClient for two corrections vs the doc table).
 *
 * Reuses the same pay-first-then-confirm pattern as the main booking flow,
 * but through a NEW Payment `purpose` ('flight_ssr') rather than touching
 * FlightBookingService — a confirmed booking must never be re-Booked, so
 * this is a fully separate confirmation path, wired in behind a purpose
 * check at the very top of FrontendController::confirmBookingAfterPayment().
 */
class FlightAncillaryService
{
    /**
     * Builds the Fetch SSR + Fetch Seat data into one per-segment structure
     * the extras view can render directly — segment info, per-traveller
     * baggage/meal options (with prices), already-added extras, and the
     * seat grid for that segment.
     */
    public function fetchOptions(Booking $booking): array
    {
        $client = app(TripJackFlightClient::class);

        $ssr = $client->fetchAncillarySsr($booking->tripjack_booking_id);
        $seat = $client->fetchAncillarySeatMap($booking->tripjack_booking_id);
        $tripSeats = $seat['tripSeatMap']['tripSeat'] ?? [];
        $ownConfirmed = $this->confirmedExtrasBySegment($booking);
        $fromDetails = $this->extrasFromBookingDetails($client, $booking);

        $tripGroups = [];
        foreach (($ssr['tripInfos'] ?? []) as $tripIdx => $trip) {
            $segments = [];
            $segmentIds = [];

            foreach (($trip['sI'] ?? []) as $seg) {
                $segmentIds[] = $seg['id'];
                $travellers = [];

                foreach (($seg['bI']['tI'] ?? []) as $t) {
                    // Doc: infants cannot add SSR.
                    if (strtoupper((string) ($t['pt'] ?? '')) === 'INFANT') {
                        continue;
                    }

                    $name = trim(($t['fN'] ?? '').' '.($t['lN'] ?? ''));
                    $nameKey = $this->travellerKey($t['fN'] ?? '', $t['lN'] ?? '');
                    $own = $ownConfirmed[(string) $seg['id']][$nameKey] ?? [];
                    $booked = $fromDetails[($seg['da']['code'] ?? '').'-'.($seg['aa']['code'] ?? '')][$nameKey] ?? [];

                    // Meals and seats can only be added once per pax per
                    // segment — merge every source of "already has one"
                    // (Fetch SSR, Booking Details, our own Add SSR history)
                    // so the guest is never charged for one TripJack will
                    // then reject.
                    $travellers[] = [
                        'id' => $t['id'],
                        'name' => $name,
                        // Confirmed live: when the airline can't price an
                        // option, ssrInfo holds a {message} entry with no
                        // code instead — keep only real, selectable options.
                        'baggageOptions' => $this->selectableOptions($t['ssrInfo']['BAGGAGE'] ?? []),
                        'mealOptions' => $this->selectableOptions($t['ssrInfo']['MEAL'] ?? []),
                        'alreadyBaggage' => $this->ssrList($t['ssrBaggageInfos'] ?? $seg['bI']['ssrBaggageInfos'] ?? null),
                        'alreadyMeal' => $this->uniqueByCode(array_merge(
                            $this->ssrList($t['ssrMealInfos'] ?? $t['ssrMeaInfos'] ?? null),
                            $booked['meal'] ?? [],
                            $own['meal'] ?? [],
                        )),
                        'alreadySeat' => $this->uniqueByCode(array_merge(
                            $this->ssrList($t['ssrSeatInfos'] ?? $t['ssrSeatInfo'] ?? null),
                            $booked['seat'] ?? [],
                            $own['seat'] ?? [],
                        )),
                    ];
                }

                $segments[] = [
                    'id' => $seg['id'],
                    'airlineCode' => $seg['fD']['aI']['code'] ?? '',
                    'airlineName' => $seg['fD']['aI']['name'] ?? '',
                    'flightNo' => $seg['fD']['fN'] ?? '',
                    'from' => $seg['da']['code'] ?? '',
                    'to' => $seg['aa']['code'] ?? '',
                    'depTime' => $seg['dt'] ?? '',
                    'arrTime' => $seg['at'] ?? '',
                    'travellers' => $travellers,
                    'seatMap' => $tripSeats[$seg['id']] ?? null,
                ];
            }

            $tripGroups[] = [
                'index' => $tripIdx,
                'firstSegmentId' => $segmentIds[0] ?? null,
                'otherSegmentIds' => array_slice($segmentIds, 1),
                'segments' => $segments,
            ];
        }

        return $tripGroups;
    }

    protected function selectableOptions(mixed $options): array
    {
        return is_array($options)
            ? array_values(array_filter($options, fn ($o) => is_array($o) && ! empty($o['code'])))
            : [];
    }

    /**
     * TripJack's doc types the already-selected SSR fields as "Object" in
     * some places and arrays in others — normalise to a list of SSR items.
     */
    protected function ssrList(mixed $value): array
    {
        if (! is_array($value) || ! $value) {
            return [];
        }

        if (isset($value['code'])) {
            return [$value];
        }

        return array_values(array_filter($value, 'is_array'));
    }

    /**
     * Meals/seats this site has already added successfully, keyed by
     * segment ID then traveller name, from the SSRAmendmentDetails stored on
     * each resolved Add SSR amendment.
     *
     * Confirmed live (2026-10-01), differing from the doc: each entry's
     * segment ID is under `sI` (not `id`), travellers carry only fN/lN (no
     * ID), and a seat added this way is NOT reported back by Fetch SSR — so
     * this history is the only reliable "seat already added" source.
     *
     * @return array<string, array<string, array{meal: array, seat: array}>>
     */
    protected function confirmedExtrasBySegment(Booking $booking): array
    {
        $bySegment = [];

        foreach (($booking->flight_ssr_confirmed ?? []) as $record) {
            if (($record['status'] ?? null) !== 'SUCCESS') {
                continue;
            }

            foreach (($record['details'] ?? []) as $segment) {
                $segmentId = $segment['sI'] ?? $segment['id'] ?? null;
                foreach (($segment['tI'] ?? []) as $t) {
                    $nameKey = $this->travellerKey($t['fN'] ?? '', $t['lN'] ?? '');
                    if ($segmentId === null || $nameKey === '') {
                        continue;
                    }
                    $entry = &$bySegment[(string) $segmentId][$nameKey];
                    $entry['meal'] = array_merge($entry['meal'] ?? [], $this->ssrList($t['ssrMeaInfos'] ?? $t['ssrMealInfos'] ?? null));
                    $entry['seat'] = array_merge($entry['seat'] ?? [], $this->ssrList($t['ssrSeatInfos'] ?? null));
                    unset($entry);
                }
            }
        }

        return $bySegment;
    }

    /**
     * Seats/meals already on the booking per Booking Details — the most
     * complete source: confirmed live that it lists seats and meals chosen
     * at Book time and added later (travellerInfos[].ssrSeatInfos /
     * ssrMealInfos keyed "DEL-BOM"), whereas Fetch SSR omits seats. Empty
     * if the lookup fails; the other sources still apply.
     *
     * @return array<string, array<string, array{meal: array, seat: array}>>
     */
    protected function extrasFromBookingDetails(TripJackFlightClient $client, Booking $booking): array
    {
        try {
            $details = $client->bookingDetails($booking->tripjack_booking_id);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('flight_ssr_details_lookup_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

            return [];
        }

        $bySegment = [];
        foreach (($details['itemInfos']['AIR']['travellerInfos'] ?? []) as $t) {
            $nameKey = $this->travellerKey($t['fN'] ?? '', $t['lN'] ?? '');
            foreach (['meal' => 'ssrMealInfos', 'seat' => 'ssrSeatInfos'] as $kind => $field) {
                foreach (($t[$field] ?? []) as $segmentKey => $item) {
                    if (is_array($item) && ! empty($item['code'])) {
                        $bySegment[$segmentKey][$nameKey][$kind][] = $item;
                    }
                }
            }
        }

        return $bySegment;
    }

    protected function uniqueByCode(array $items): array
    {
        return collect($items)->unique(fn ($i) => $i['code'] ?? json_encode($i))->values()->all();
    }

    protected function travellerKey(string $firstName, string $lastName): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $firstName.' '.$lastName)));
    }

    /**
     * Validates the guest's submitted selections against the cached (not
     * client-submitted) option list — codes and amounts both come from
     * here, never from the request — and builds the Add SSR `sI[]` payload
     * plus the total payable amount.
     *
     * @param  array  $cachedTripGroups  Booking::flight_ssr_options_cache
     * @param  array  $selections  ['segmentId' => ['travellerId' => ['baggage' => code, 'meal' => code, 'seat' => code]]]
     * @return array{segmentInfos: array, total: float}|null null if nothing valid was selected
     */
    public function buildSsrPayload(array $cachedTripGroups, array $selections): ?array
    {
        $segmentInfos = [];
        $total = 0.0;

        foreach ($cachedTripGroups as $group) {
            // Baggage is per-journey, chosen on the journey's first segment
            // only — the doc says pass that same SBI code on every later
            // segment too, with amount 0. Tracked per traveller (by ID, and
            // by name in case IDs differ between segments).
            $baggageByTraveller = [];

            foreach ($group['segments'] as $segIdx => $segment) {
                $segSelections = (array) ($selections[$segment['id']] ?? []);

                $tiPayload = [];
                foreach ($segment['travellers'] as $traveller) {
                    $picked = (array) ($segSelections[$traveller['id']] ?? []);
                    $entry = ['id' => $traveller['id']];
                    $nameKey = 'name:'.strtolower($traveller['name']);

                    if ($segIdx === 0) {
                        if (! empty($picked['baggage'])) {
                            $option = collect($traveller['baggageOptions'])->firstWhere('code', $picked['baggage']);
                            if ($option) {
                                $amount = (float) ($option['amount'] ?? 0);
                                $entry['sbi'] = ['code' => $option['code'], 'amount' => $amount];
                                $baggageByTraveller[$traveller['id']] = $baggageByTraveller[$nameKey] = $option['code'];
                                $total += $amount;
                            }
                        }
                    } elseif ($code = $baggageByTraveller[$traveller['id']] ?? $baggageByTraveller[$nameKey] ?? null) {
                        $entry['sbi'] = ['code' => $code, 'amount' => 0];
                    }

                    if (! empty($picked['meal']) && empty($traveller['alreadyMeal'])) {
                        $option = collect($traveller['mealOptions'])->firstWhere('code', $picked['meal']);
                        if ($option) {
                            $entry['smi'] = ['code' => $option['code']];
                            $total += (float) ($option['amount'] ?? 0);
                        }
                    }

                    if (! empty($picked['seat']) && $segment['seatMap'] && empty($traveller['alreadySeat'])) {
                        $seatOption = collect($segment['seatMap']['sInfo'] ?? [])->firstWhere('code', $picked['seat']);
                        if ($seatOption && ! ($seatOption['isBooked'] ?? false)) {
                            $entry['ssi'] = ['code' => $seatOption['code']];
                            $total += (float) ($seatOption['amount'] ?? 0);
                        }
                    }

                    if (count($entry) > 1) {
                        $tiPayload[] = $entry;
                    }
                }

                if ($tiPayload) {
                    $segmentInfos[] = ['id' => $segment['id'], 'bI' => ['tI' => $tiPayload]];
                }
            }
        }

        if (! $segmentInfos || $total <= 0) {
            return null;
        }

        return ['segmentInfos' => $segmentInfos, 'total' => round($total, 2)];
    }

    /**
     * Called from FrontendController::confirmBookingAfterPayment() once the
     * extras payment is captured. Applies the cached selection via Add SSR
     * and dispatches a poll job per amendment ID — mirrors
     * FlightBookingService's locking/idempotency pattern exactly.
     */
    public function confirmAfterPayment(Payment $payment, RazorpayService $razorpay): void
    {
        $client = app(TripJackFlightClient::class);

        DB::transaction(function () use ($payment, $client, $razorpay) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->first();

            if (in_array($payment->status, ['captured', 'refunded', 'partially_refunded', 'failed'], true)) {
                return; // already processed by a racing callback/webhook
            }

            $payment->update(['status' => 'captured']);

            $selection = $booking->flight_ssr_pending_selection;
            if (! $selection || empty($selection['segmentInfos'])) {
                Log::channel('tripjack')->critical('flight_ssr_pending_selection_missing', ['booking_id' => $booking->id, 'payment_id' => $payment->id]);
                $this->refundAndMarkFailed($booking, $payment, $razorpay, 'Your extras selection could not be found after payment.');

                return;
            }

            try {
                $response = $client->addSsr($booking->tripjack_booking_id, (float) $payment->amount, $selection['segmentInfos']);
            } catch (TripJackException $e) {
                // Timed out / 5xx: the extras may have been added (and
                // charged to the wallet) without us getting the amendment
                // IDs back — refunding blind could refund extras the guest
                // keeps. Hold the payment and flag it for a manual check.
                if (FlightBookingService::isUncertainOutcome($e)) {
                    $booking->update([
                        'flight_ssr_status' => 'needs_review',
                        'admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '')
                            ."Add SSR request (payment #{$payment->id}) got no answer from TripJack — check in TripJack whether the extras were added, then confirm or refund."),
                    ]);
                    Log::channel('tripjack')->critical('flight_ssr_add_outcome_unknown', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'message' => $e->getMessage()]);

                    return;
                }

                $errorCode = $e instanceof TripJackApiException ? $e->errorCode : null;
                $described = TripJackFlightErrorCatalog::describe($errorCode);
                Log::channel('tripjack')->{$described['logLevel'] === 'critical' ? 'critical' : 'warning'}('flight_ssr_add_failed', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'message' => $e->getMessage()]);
                $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

                return;
            }

            $amendmentIds = $response['amendmentIds'] ?? [];
            if (! ($response['status']['success'] ?? false) || ! $amendmentIds) {
                $errorCode = TripJackFlightErrorCatalog::codeFromResponse($response);
                $described = TripJackFlightErrorCatalog::describe($errorCode, 'This addition could not be applied to your booking.');
                Log::channel('tripjack')->warning('flight_ssr_add_unsuccessful', ['booking_id' => $booking->id, 'errorCode' => $errorCode, 'response' => $response]);
                $this->refundAndMarkFailed($booking, $payment, $razorpay, $described['message']);

                return;
            }

            $booking->update([
                'flight_ssr_amendment_ids' => array_values(array_unique(array_merge($booking->flight_ssr_amendment_ids ?? [], $amendmentIds))),
                'flight_ssr_amount_paid' => round((float) $booking->flight_ssr_amount_paid + (float) $payment->amount, 2),
                'flight_ssr_status' => 'pending',
                'flight_ssr_pending_selection' => null,
            ]);

            foreach ($amendmentIds as $i => $amendmentId) {
                PollFlightSsrAmendmentJob::dispatch($booking->id, $amendmentId, $amendmentIds, attempt: 1, paymentId: $payment->id)
                    ->delay(now()->addSeconds(10 + $i));
            }
        });
    }

    /**
     * Called by PollFlightSsrAmendmentJob once one amendment in the batch
     * resolves. $batchAmendmentIds is every ID from that one Add SSR call —
     * once every ID in the batch has resolved, the overall status is set;
     * a booking that added extras more than once keeps each batch's outcome
     * in flight_ssr_confirmed, keyed by amendment ID, rather than
     * overwriting.
     */
    public function finalizeAmendment(Booking $booking, string $amendmentId, array $details, array $batchAmendmentIds, ?int $paymentId = null): void
    {
        $status = $details['amendmentStatus'] ?? null;
        if (! in_array($status, ['SUCCESS', 'REJECTED'], true)) {
            return; // still PENDING — job will poll again
        }

        // Locked + re-read: each amendment in a batch has its own poll job,
        // and two finishing together must not both miss "all resolved" or
        // both refund.
        DB::transaction(function () use ($booking, $amendmentId, $details, $batchAmendmentIds, $paymentId, $status) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->first();

            $confirmed = $booking->flight_ssr_confirmed ?? [];
            if (isset($confirmed[$amendmentId])) {
                return; // already recorded by a duplicate job
            }
            $confirmed[$amendmentId] = [
                'status' => $status,
                'remarks' => $details['remarks'] ?? null,
                'amount' => $details['totalAmount'] ?? null,
                'details' => $details['SSRAmendmentDetails'] ?? null,
                'resolvedAt' => now()->toISOString(),
            ];

            $batch = collect($batchAmendmentIds);
            $allResolved = $batch->every(fn ($id) => isset($confirmed[$id]));
            $rejected = $batch->filter(fn ($id) => ($confirmed[$id]['status'] ?? null) === 'REJECTED');

            $update = ['flight_ssr_confirmed' => $confirmed];

            if ($allResolved) {
                $update['flight_ssr_status'] = 'confirmed';

                if ($rejected->isNotEmpty()) {
                    $update = array_merge($update, $this->refundRejectedExtras($booking, $confirmed, $batch->all(), $rejected->all(), $paymentId));
                }
            }

            $booking->update($update);
        });
    }

    /**
     * Refunds the rejected part of one Add SSR batch. Extras are charged at
     * TripJack's own amount, so a rejected amendment's totalAmount is what
     * the guest paid for it; if the whole batch was rejected, the whole
     * payment is refunded. Anything it can't work out safely is left for
     * manual review rather than guessed.
     *
     * @return array<string, mixed> booking columns to update
     */
    protected function refundRejectedExtras(Booking $booking, array $confirmed, array $batchIds, array $rejectedIds, ?int $paymentId): array
    {
        $payment = Payment::query()
            ->where('booking_id', $booking->id)
            ->when($paymentId, fn ($q) => $q->whereKey($paymentId), fn ($q) => $q->where('purpose', 'flight_ssr')->latest())
            ->lockForUpdate()
            ->first();

        $allRejected = count($rejectedIds) === count($batchIds);
        $remaining = $payment ? round((float) $payment->amount - (float) ($payment->refund_amount ?? 0), 2) : 0.0;

        if ($allRejected) {
            $refund = $remaining;
        } else {
            $amounts = collect($rejectedIds)->map(fn ($id) => (float) ($confirmed[$id]['amount'] ?? 0));
            $refund = $amounts->contains(fn ($a) => $a <= 0) ? null : min($remaining, round($amounts->sum(), 2));
        }

        $needsReview = fn (string $why) => [
            'flight_ssr_status' => 'needs_review',
            'admin_note' => trim(($booking->admin_note ? $booking->admin_note.' ' : '').$why),
        ];

        if (! $payment || ! $payment->razorpay_payment_id || $refund === null || $refund <= 0) {
            Log::channel('tripjack')->critical('flight_ssr_amendment_rejected_needs_review', ['booking_id' => $booking->id, 'rejected' => $rejectedIds, 'payment_id' => $payment?->id]);

            return $needsReview('One or more seat/meal/baggage additions were REJECTED after payment and the refund amount could not be determined automatically — needs manual refund review.');
        }

        try {
            app(RazorpayService::class)->refund($payment->razorpay_payment_id, $refund);
        } catch (\Throwable $e) {
            Log::channel('tripjack')->critical('flight_ssr_rejected_refund_failed', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'refund' => $refund, 'error' => $e->getMessage()]);

            return $needsReview("Seat/meal/baggage additions were REJECTED after payment and the automatic refund of {$refund} failed — needs manual refund: ".$e->getMessage());
        }

        $totalRefunded = round((float) ($payment->refund_amount ?? 0) + $refund, 2);
        $payment->update([
            'status' => $totalRefunded >= (float) $payment->amount - 0.01 ? 'refunded' : 'partially_refunded',
            'refund_amount' => $totalRefunded,
            'refund_reason' => 'Airline rejected the seat/meal/baggage addition.',
        ]);
        Log::channel('tripjack')->warning('flight_ssr_rejected_refunded', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'refund' => $refund, 'rejected' => $rejectedIds]);

        return [
            'flight_ssr_status' => $allRejected ? 'refunded' : 'partially_confirmed',
            'flight_ssr_amount_paid' => max(0, round((float) $booking->flight_ssr_amount_paid - $refund, 2)),
        ];
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
            $booking->update(['flight_ssr_status' => 'failed', 'flight_ssr_pending_selection' => null]);
            Log::channel('tripjack')->critical('flight_ssr_refunded_after_failure', [
                'booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $reason,
            ]);
        } catch (\Throwable $e) {
            // The money was taken and is still with us — the payment stays
            // captured (not "failed") until staff refund it by hand.
            $payment->update(['refund_reason' => $reason]);
            $booking->update([
                'flight_ssr_status' => 'failed',
                'flight_ssr_pending_selection' => null,
                'manual_refund_due_at' => now(),
                'admin_note' => $booking->adminNoteWith("Seat/meal/baggage payment of {$booking->currency} {$payment->amount} could not be refunded automatically ({$e->getMessage()}). Refund the guest in Razorpay, then click \"Record manual refund\"."),
            ]);
            Log::channel('tripjack')->critical('flight_ssr_refund_after_failure_errored', [
                'booking_id' => $booking->id, 'payment_id' => $payment->id, 'reason' => $reason, 'refund_error' => $e->getMessage(),
            ]);
        }
    }
}
