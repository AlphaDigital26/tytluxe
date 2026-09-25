<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Razorpay's server-to-server webhook has no CSRF token — it's
        // authenticated instead via its own HMAC signature header,
        // verified in RazorpayService::verifyWebhookSignature().
        $middleware->validateCsrfTokens(except: [
            'payment/razorpay/webhook',
        ]);

        // Livewire's update endpoint (used by the admin panel's forms) runs
        // under the 'web' group rather than the Filament panel's own
        // middleware, so the execution-time bump has to be registered here
        // to cover admin form saves that process several uploads/repeaters
        // in one request.
        $middleware->web(append: [
            \App\Http\Middleware\IncreaseAdminExecutionLimits::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
