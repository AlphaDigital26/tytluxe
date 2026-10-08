<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\FlightAncillaryService;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\Exceptions\TripJackApiException;
use App\Services\TripJack\Exceptions\TripJackException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Post-booking "Add Seats, Meals & Baggage" flow (TripJack's Ancillaries
 * (SSR) API) — kept separate from FlightController (Search→Review→Book)
 * since this operates on an already-confirmed booking, not a fresh search.
 * See FlightAncillaryService's docblock for the confirm-after-payment
 * wiring into the shared Razorpay callback.
 */
class FlightAncillaryController extends Controller
{
    public const NOT_OFFERED_MESSAGE = 'The airline doesn\'t let us add seats, meals or extra baggage online for this booking. Please contact us and we\'ll arrange it with the airline for you.';

    public function show(string $reference, FlightAncillaryService $ancillaries)
    {
        $booking = $this->guardBooking($reference);

        try {
            $tripGroups = $ancillaries->fetchOptions($booking);
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('flight_ssr_fetch_failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);

            // A definite refusal (4xx) won't change on a retry — TripJack's
            // FAQ: add-ons are LCC-only, and its airline matrix lists several
            // carriers with none. Only an outage is worth retrying.
            $refused = $e instanceof TripJackApiException && $e->status < 500;

            return redirect()->route('hotel.booking.confirmation', $booking->reference)
                ->with('booking_error', $refused
                    ? self::NOT_OFFERED_MESSAGE
                    : 'Seat, meal and baggage options aren\'t available for this booking right now. Please try again shortly.');
        }

        // Cached so submit() validates against exactly what was shown,
        // never a client-submitted amount.
        $booking->update(['flight_ssr_options_cache' => $tripGroups]);

        return view('pages.flight-extras', [
            'booking' => $booking,
            'tripGroups' => $tripGroups,
        ]);
    }

    public function submit(Request $request, string $reference, FlightAncillaryService $ancillaries, RazorpayService $razorpay)
    {
        $booking = $this->guardBooking($reference);

        // Doc (Ancillaries): booking must be in SUCCESS state — check before
        // taking payment for extras TripJack would then reject.
        $liveStatus = app(\App\Services\FlightBookingService::class)->liveOrderStatus($booking);
        if ($liveStatus !== null && $liveStatus !== 'SUCCESS') {
            return redirect()->route('hotel.booking.confirmation', $booking->reference)
                ->with('booking_error', \App\Services\FlightBookingService::NOT_TICKETED_YET_MESSAGE);
        }

        $cached = $booking->flight_ssr_options_cache;
        if (! $cached) {
            return redirect()->route('flights.extras.show', $booking->reference)
                ->with('booking_error', 'Your extras session expired. Please select again.');
        }

        $selections = (array) $request->input('selections', []);
        $built = $ancillaries->buildSsrPayload($cached, $selections);

        if (! $built) {
            return back()->withErrors(['selections' => 'Please select at least one seat, meal, or baggage option.']);
        }

        if (! app(\App\Services\FlightBookingService::class)->hasTripJackFunds($built['total'])) {
            return back()->withErrors(['selections' => \App\Services\FlightBookingService::INSUFFICIENT_FUNDS_MESSAGE]);
        }

        $booking->update(['flight_ssr_pending_selection' => $built['segmentInfos'] ? ['segmentInfos' => $built['segmentInfos']] : null]);

        // Deliberately charged at TripJack's own amount — no margin/GST/
        // Razorpay gross-up (business decision, 2026-10-01). Refunds of
        // rejected extras rely on this 1:1 match; see
        // FlightAncillaryService::refundRejectedExtras().
        $order = $razorpay->createOrder($built['total'], $booking->reference.'-SSR-'.now()->timestamp);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'razorpay_order_id' => $order['id'],
            'amount' => $built['total'],
            'currency' => $booking->currency ?? 'INR',
            'status' => 'created',
            'purpose' => 'flight_ssr',
        ]);

        return view('pages.flight-ssr-payment', [
            'booking' => $booking,
            'payment' => $payment,
            'razorpayKeyId' => config('services.razorpay.key_id'),
        ]);
    }

    protected function guardBooking(string $reference): Booking
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->vertical !== 'flight' || $booking->status !== 'confirmed' || ! $booking->tripjack_booking_id) {
            abort(404);
        }

        // Until the previous Add SSR resolves, TripJack's "already selected"
        // lists may not show it yet — a second purchase in that gap could
        // repeat a meal/seat TripJack then rejects after payment.
        if ($booking->flight_ssr_status === 'pending') {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('hotel.booking.confirmation', $booking->reference)
                    ->with('booking_error', 'Your previous seat, meal or baggage additions are still being confirmed with the airline. Please try again in a minute.')
            );
        }

        return $booking;
    }
}
