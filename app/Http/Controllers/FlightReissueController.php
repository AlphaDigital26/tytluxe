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

        // Deliberately charged at TripJack's own reissue TF — no margin/
        // GST/Razorpay gross-up (business decision, 2026-10-01).
        $order = $razorpay->createOrder($result['amount'], $booking->reference.'-REISSUE-'.now()->timestamp);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'razorpay_order_id' => $order['id'],
            'amount' => $result['amount'],
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

        return $booking;
    }
}
