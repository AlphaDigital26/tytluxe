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

        // The admin console (Filament/Livewire) manages its own scripts.
        if (! $request->is('tyt-console', 'tyt-console/*', 'livewire/*')) {
            $headers->set('Content-Security-Policy', $this->contentSecurityPolicy(), false);
        }

        return $response;
    }

    /**
     * Only the outside sites the pages actually use may load scripts, styles,
     * fonts and frames; plugins and other sites framing us are blocked.
     * Inline scripts stay allowed (the pages use many), and Alpine needs eval.
     */
    private function contentSecurityPolicy(): string
    {
        // Local `npm run dev` (Vite) serves from an address such as http://[::1]:5173,
        // which CSP can't name, so local development allows any http/ws source.
        $dev = app()->isLocal() && is_file(public_path('hot')) ? ' http: ws:' : '';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://*.razorpay.com".$dev,
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net".$dev,
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https://*.razorpay.com".$dev,
            "frame-src 'self' https://*.razorpay.com https://maps.google.com https://www.google.com",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self' https://*.razorpay.com",
        ]);
    }
}
