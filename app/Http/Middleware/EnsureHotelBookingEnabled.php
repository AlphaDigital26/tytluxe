<?php

namespace App\Http\Middleware;

use App\Support\HotelSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Honours "Guests can book hotels online" on the admin's Hotel Settings
 * page. Guards only review → book (starting a new booking); browsing hotels
 * and everything about existing bookings keeps working.
 */
class EnsureHotelBookingEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (HotelSettings::bookingEnabled()) {
            return $next($request);
        }

        $slug = $request->route('slug');

        return ($slug ? redirect()->route('hotel.details', $slug) : redirect()->route('hotels'))
            ->with('booking_error', HotelSettings::disabledMessage());
    }
}
