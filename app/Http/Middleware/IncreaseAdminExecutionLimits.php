<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Package/hotel edit forms can save many repeater rows (itinerary days, images, etc.)
 * plus process uploaded photos in a single Livewire request, which occasionally
 * exceeds PHP's default 60s max_execution_time. Raise the ceiling for the whole
 * admin panel request rather than only inside the image-processing service.
 */
class IncreaseAdminExecutionLimits
{
    public function handle(Request $request, Closure $next): Response
    {
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        return $next($request);
    }
}
