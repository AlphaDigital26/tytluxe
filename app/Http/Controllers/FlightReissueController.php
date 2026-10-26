<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\FlightReissueService;
use App\Services\Payment\RazorpayService;
use Illuminate\Http\Request;

/**
 * Auto Reissue (reschedule to a new date) — kept separate from
 * FlightController (Search→Review→Book) and FlightAncillaryController
 * (post-booking extras) since this operates on an already-confirmed
 * booking's own TripJack bookingId, with its own 4-step API flow. See
 * FlightReissueService's docblock for the payment wiring.
 */
class FlightReissueController extends Controller
{
    public function show(string $reference)
    {
        $booking = $this->guardBooking($reference);

        return view('pages.flight-reissue', [
            'booking' => $booking,
            'legs' => $booking->flight_legs ?? [],
        ]);
    }

    public function search(Request $request, string $reference, FlightReissueService $reissue)
    {
        $booking = $this->guardBooking($reference);

        $validated = $request->validate([
            'leg_index' => 'required|integer|min:0',
            'new_date' => 'required|date|after:today',
        ]);

        $result = $reissue->search($booking, (int) $validated['leg_index'], $validated['new_date']);

        return view('pages.flight-reissue', [
            'booking' => $booking,
            'legs' => $booking->flight_legs ?? [],
            'legIndex' => (int) $validated['leg_index'],
            'newDate' => $validated['new_date'],
            'options' => $result['options'],
            'searchError' => $result['success'] ? null : $result['message'],
        ]);
    }

    public function review(Request $request, string $reference, FlightReissueService $reissue, RazorpayService $razorpay)
    {
        $booking = $this->guardBooking($reference);

        $validated = $request->validate([
            'price_id' => 'required|string',
            'leg_index' => 'required|integer|min:0',
        ]);

        $result = $reissue->review($booking, $validated['price_id'], (int) $validated['leg_index']);
        if (! $result['success']) {
            return redirect()->route('flights.reissue.show', $booking->reference)->with('booking_error', $result['message']);
        }

        // TripJack's TF (no markup) + our service charge + GST + gateway fee
        // (FlightPricingService::reschedule). TripJack is always sent its own
        // TF (flight_reissue_pending.amount), never what the guest pays.
        $priced = \App\Services\FlightPricingService::reschedule($result['amount']);

        // Nothing to pay (the new flight is cheaper by more than the fees and
        // our charge): Razorpay can't take a ₹0 order, so book the reissue
        // straight away against a zero-amount payment record, then refund
        // what's left over to the guest once it's confirmed.
        if ($priced['total'] <= 0) {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'razorpay_order_id' => 'NOCHARGE-'.$booking->reference.'-'.now()->timestamp,
                'amount' => 0,
                'currency' => $booking->currency ?? 'INR',
                'status' => 'created',
                'purpose' => 'flight_reissue',
            ]);
            $reissue->confirmAfterPayment($payment, $razorpay);

            $booking->refresh();
            if ($booking->flight_reissued_at === null) {
                return redirect()->route('flights.reissue.show', $booking->reference)
                    ->with('booking_error', 'We couldn\'t confirm this reschedule with the airline. Please try again or contact our support team.');
            }

            if ($priced['total'] < 0) {
                $refunded = $reissue->refundFareDifference($booking, $priced['total'], $razorpay);
                if ($refunded > 0) {
                    return redirect()->route('hotel.booking.confirmation', $booking->reference)
                        ->with('booking_success', sprintf('Your flight has been rescheduled. The fare difference of ₹%s is being refunded to your original payment method (5–7 business days).', number_format($refunded, 2)));
                }
            }

            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }

        if ($result['amount'] > 0 && ! app(\App\Services\FlightBookingService::class)->hasTripJackFunds($result['amount'])) {
            return redirect()->route('flights.reissue.show', $booking->reference)
                ->with('booking_error', \App\Services\FlightBookingService::INSUFFICIENT_FUNDS_MESSAGE);
        }

        $order = $razorpay->createOrder($priced['total'], $booking->reference.'-REISSUE-'.now()->timestamp);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'razorpay_order_id' => $order['id'],
            'amount' => $priced['total'],
            'currency' => $booking->currency ?? 'INR',
            'status' => 'created',
            'purpose' => 'flight_reissue',
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

        if (! app(FlightReissueService::class)->isEligible($booking)) {
            abort(404);
        }

        // TripJack's airline matrix: this airline's date changes are offline.
        if (! $booking->flightSupportsOnline('reschedule')) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('hotel.booking.confirmation', $booking->reference)
                    ->with('booking_error', 'This airline doesn’t allow online date changes. '.\App\Support\FlightSettings::contactUsMessage())
            );
        }

        return $booking;
    }
}
