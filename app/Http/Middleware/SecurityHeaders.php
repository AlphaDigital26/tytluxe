<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic browser security headers on every page (guest test, finding F05):
 * no framing by other sites (click-jacking), no content-type guessing, and
 * only the site's origin sent as referrer to other sites. HSTS only over
 * HTTPS, so local http development is unaffected.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = $response->headers;
        $headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $headers->set('X-Content-Type-Options', 'nosniff', false);
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()', false);
        $headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By'); // added by PHP itself (expose_php)
        }

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);
        }

        return $response;
    }
}
