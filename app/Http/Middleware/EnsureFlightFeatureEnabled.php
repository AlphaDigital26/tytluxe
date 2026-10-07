<?php

namespace App\Http\Middleware;

use App\Support\FlightSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Honours the on/off switches on the admin's Flight Settings page:
 * `flight.enabled:booking` guards search → review → book (new bookings
 * only — payments, confirmations and cancellations of existing bookings are
 * never blocked), `flight.enabled:extras` and `flight.enabled:reschedule`
 * guard the guest's post-booking self-service pages.
 */
class EnsureFlightFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature = 'booking'): Response
    {
        $enabled = match ($feature) {
            'extras' => FlightSettings::allowGuestExtras(),
            'reschedule' => FlightSettings::allowGuestReschedule(),
            default => FlightSettings::bookingEnabled(),
        };

        if ($enabled) {
            return $next($request);
        }

        $message = $feature === 'booking'
            ? FlightSettings::disabledMessage()
            : FlightSettings::contactUsMessage();

        if ($request->expectsJson() || $request->routeIs('*.ajax')) {
            return response()->json(['success' => false, 'message' => $message], 503);
        }

        $reference = $request->route('reference');
        if ($feature !== 'booking' && $reference) {
            return redirect()->route('hotel.booking.confirmation', $reference)->with('booking_error', $message);
        }

        return redirect()->route('flights')->with('flight_notice', $message);
    }
}
