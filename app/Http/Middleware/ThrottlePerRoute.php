<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Laravel's `throttle:N,M` keeps ONE counter per user (or IP) for every route
 * that uses it, so typing in the search box (suggestions) used up the same
 * count as the Book button, and a guest could be refused "Too Many Requests"
 * at payment without ever retrying it. Each route now has its own counter.
 */
class ThrottlePerRoute extends ThrottleRequests
{
    protected function resolveRequestSignature($request)
    {
        $route = $request->route();
        $routeKey = $request->method().'|'.($route?->getName() ?? $route?->uri() ?? $request->path());

        return sha1(parent::resolveRequestSignature($request).'|'.$routeKey);
    }
}
