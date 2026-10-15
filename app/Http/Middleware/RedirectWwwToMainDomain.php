<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * www.tytluxe.in served a full second copy of the site, each copy telling
 * Google it was the original (canonical) — which splits search ranking
 * between the two. Send www visitors permanently to the APP_URL address.
 *
 * Only GET/HEAD: a redirected POST (a form, a payment webhook) would lose
 * its data, so those are left alone.
 */
class RedirectWwwToMainDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $main = parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($main && $request->getHost() === 'www.'.$main && in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return redirect()->to(rtrim((string) config('app.url'), '/').$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
