<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Auth\OtpController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('auth/google', [SocialLoginController::class, 'redirectToGoogle'])->name('social.google.redirect');
    Route::get('auth/google/callback', [SocialLoginController::class, 'handleGoogleCallback'])->name('social.google.callback');

    Route::get('verify-otp',  [OtpController::class, 'showVerifyForm'])->name('otp.verify');
    // verify() already tracks per-OTP attempts (5, then the code is invalidated) —
    // this throttle is defense in depth against a fast automated guessing script.
    Route::post('verify-otp', [OtpController::class, 'verify'])->name('otp.verify.submit')->middleware('throttle:10,1');
    // Tightest limit here on purpose: resend() deletes the old OTP and its
    // attempt counter and mints a fresh one — without a throttle, an attacker
    // could use unlimited resends to reset verify()'s 5-attempt cap back to 0
    // as many times as they like (turning a 5-guess limit into none), and it
    // sends a real email every call (mail-bombing risk) regardless.
    Route::post('verify-otp/resend', [OtpController::class, 'resend'])->name('otp.resend')->middleware('throttle:3,1');
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    // Sends a real OTP email on every successful call — unthrottled this is a
    // mail-bombing vector against any address (yours or someone else's).
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // LoginRequest::ensureIsNotRateLimited() already locks out after 5 failed
    // attempts per email+IP — but that leaves an attacker who rotates through
    // many different email addresses from one IP completely unthrottled. This
    // adds a coarser IP-wide cap on top, closing that gap.
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:20,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    // Sends a real reset-link email on every call — same mail-bombing risk as
    // registration, plus a coarse guard against email-enumeration timing.
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email')
        ->middleware('throttle:5,1');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    // Guards against brute-forcing the reset token itself.
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store')
        ->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
