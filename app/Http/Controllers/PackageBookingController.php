<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use App\Services\PackageBookingService;
use App\Services\PackagePricingService;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Group package booking: choose date, sharing and travellers → pay a
 * booking amount or in full → confirmed. Payment, the confirmation page and
 * cancellation sit on the shared /booking/{reference}/* routes, which
 * FrontendController hands to this controller for package bookings.
 */
class PackageBookingController extends Controller
{
    public function __construct(protected PackageBookingService $service) {}

    public function checkout(string $slug, Request $request)
    {
        $package = Package::with('departures')->where('slug', $slug)->firstOrFail();
        if (! $package->acceptsOnlineBooking()) {
            return redirect()->route('package.details', $slug)->with('error', 'Online booking is not open for this package. Please contact us to book.');
        }

        $departures = $package->bookableDepartures();
        if ($departures->isEmpty()) {
            return redirect()->route('package.details', $slug)->with('error', 'There are no open departure dates for this package right now. Please contact us.');
        }

        $user = $request->user();

        return view('pages.package-checkout', [
            'package' => $package,
            'departures' => $departures,
            'options' => $package->priceOptions(),
            'selectedDeparture' => (int) $request->query('departure', $departures->first()->id),
            'selectedOption' => (string) $request->query('option', ''),
            'travellerCount' => max(1, min(PackageBookingService::MAX_TRAVELLERS, (int) $request->query('travellers', 1))),
            'user' => $user,
            'formula' => PackagePricingService::formula(),
            'maxTravellers' => PackageBookingService::MAX_TRAVELLERS,
            'balanceDueDays' => PackageBookingService::BALANCE_DUE_DAYS_BEFORE,
            'cancelDays' => PackageBookingService::CANCEL_DAYS_BEFORE,
        ]);
    }

    public function book(string $slug, Request $request)
    {
        $package = Package::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'departure_id' => 'required|integer',
            'option' => 'required|string|max:100',
            'plan' => 'required|in:part,full',
            'travellers' => 'required|array|min:1|max:'.PackageBookingService::MAX_TRAVELLERS,
            'travellers.*.full_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL][\pL .\'-]*$/u'],
            'travellers.*.gender' => 'required|in:male,female,other',
            'travellers.*.age' => 'required|integer|min:1|max:99',
            // Strict check: a typo'd address would lose the guest their confirmation.
            'email' => 'required|email:rfc,strict|max:150',
            'phone_code' => ['required', 'regex:/^\d{1,4}$/'],
            'phone' => ['required', 'regex:/^\d{6,15}$/'],
            'special_requests' => 'nullable|string|max:500',
            'terms' => 'accepted',
        ], [
            'travellers.*.full_name.required' => 'Please enter the name of every traveller.',
            'travellers.*.full_name.regex' => 'Traveller names can only have letters, spaces, dots, apostrophes and hyphens.',
            'travellers.*.gender.required' => 'Please choose a gender for every traveller.',
            'travellers.*.age.required' => 'Please enter the age of every traveller.',
            'travellers.*.age.*' => 'Each traveller\'s age must be between 1 and 99.',
            'phone.regex' => 'Please enter a valid mobile number (digits only).',
            'terms.accepted' => 'Please accept the booking and cancellation terms.',
        ]);

        try {
            $booking = $this->service->create(
                $request->user(),
                $package,
                (int) $data['departure_id'],
                $data['option'],
                array_values(array_map(fn ($t) => [
                    'full_name' => trim(preg_replace('/\s+/', ' ', $t['full_name'])),
                    'gender' => $t['gender'],
                    'age' => (int) $t['age'],
                ], $data['travellers'])),
                [
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'phone_code' => $data['phone_code'],
                    'special_requests' => $data['special_requests'] ?? null,
                ],
                $data['plan'],
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('booking_error', $e->getMessage());
        }

        return redirect()->route('hotel.payment.show', $booking->reference);
    }

    /** /booking/{reference}/pay for a package (first payment). */
    public function payment(Booking $booking)
    {
        if (! in_array($booking->status, ['pending_payment', 'payment_failed'], true)) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }

        // The seats were held for SEAT_HOLD_MINUTES; after that, only take
        // payment if they are still free (else nothing is charged).
        $departure = $booking->packageDeparture;
        $holdOver = $booking->created_at->lt(now()->subMinutes(PackageBookingService::SEAT_HOLD_MINUTES));
        $left = $departure?->seatsLeft($booking->id);
        $unavailable = $departure === null
            || ($holdOver && ($left !== null && $left < (int) $booking->pax_adults))
            || today()->gte($departure->start_date->copy()->subDays(PackageBookingService::BALANCE_DUE_DAYS_BEFORE));

        return $this->paymentView($booking, $unavailable ? null : $this->service->paymentFor($booking, 'booking'), 'booking');
    }

    /** "Pay balance" from the booking page or the guest's trips. */
    public function balance(string $reference, Request $request)
    {
        $booking = Booking::with('package')->where('reference', $reference)->firstOrFail();
        abort_if($booking->user_id !== $request->user()->id, 403);

        if (! $booking->packageBalanceDue()) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }

        return $this->paymentView($booking, $this->service->paymentFor($booking, PackageBookingService::PURPOSE_BALANCE), PackageBookingService::PURPOSE_BALANCE);
    }

    protected function paymentView(Booking $booking, $payment, string $purpose)
    {
        $booking->loadMissing('package', 'packageDeparture');

        return view('pages.package-payment', [
            'booking' => $booking,
            'payment' => $payment,
            'purpose' => $purpose,
            'razorpayKeyId' => config('services.razorpay.key_id'),
            'charge' => $purpose === PackageBookingService::PURPOSE_BALANCE
                ? PackagePricingService::charge((float) $booking->package_balance_amount)
                : null,
        ]);
    }

    public function confirmation(Booking $booking)
    {
        $booking->load('package.destination', 'packageDeparture', 'travelers', 'payments');

        return view('pages.package-booking-confirmation', [
            'booking' => $booking,
            'balanceCharge' => $booking->packageBalanceDue() ? PackagePricingService::charge((float) $booking->package_balance_amount) : null,
            'canCancel' => PackageBookingService::guestCanCancel($booking),
            'cancelDeadline' => PackageBookingService::cancelDeadline($booking),
        ]);
    }

    public function showCancellation(Booking $booking)
    {
        if (! PackageBookingService::guestCanCancel($booking)) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference)
                ->with('booking_error', $booking->status === 'confirmed' && $booking->cancellation_requested_at === null
                    ? 'Online cancellation closes '.PackageBookingService::CANCEL_DAYS_BEFORE.' days before departure. Please contact our team.'
                    : null);
        }

        $booking->load('package', 'packageDeparture');

        return view('pages.package-booking-cancel', [
            'booking' => $booking,
            'paid' => PackageBookingService::refundableAmount($booking),
        ]);
    }

    public function submitCancellation(Booking $booking, Request $request)
    {
        $data = $request->validate([
            'reason' => 'nullable|string|max:500',
            'confirm' => 'accepted',
        ], ['confirm.accepted' => 'Please tick the box to confirm you want to cancel.']);

        if (! $this->service->requestCancellation($booking, $data['reason'] ?? null)) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference)
                ->with('booking_error', 'This booking can no longer be cancelled online. Please contact our team.');
        }

        return redirect()->route('hotel.booking.confirmation', $booking->reference)
            ->with('booking_success', 'Your cancellation request has been sent. Our team will confirm the cancellation and any refund by email.');
    }
}
