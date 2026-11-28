<?php

namespace App\Http\Middleware;

use App\Support\CabSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Honours "Guests can book cabs online" on the admin's Cab Settings page.
 * Guards searching and starting a new booking; existing bookings (payment
 * page, confirmation, cancellation) keep working.
 */
class EnsureCabBookingEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (CabSettings::bookingEnabled()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => CabSettings::disabledMessage()], 503);
        }

        return redirect()->route('cabs')->with('booking_error', CabSettings::disabledMessage());
    }
}
