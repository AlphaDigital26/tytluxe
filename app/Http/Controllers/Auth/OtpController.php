<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OtpController extends Controller
{
    /**
     * Show the OTP verification form.
     */
    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        // If no pending registration in session, redirect to register
        if (! $request->session()->has('pending_registration')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp');
    }

    /**
     * Verify the submitted OTP code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ]);

        $pending = $request->session()->get('pending_registration');

        if (! $pending) {
            return redirect()->route('register')
                ->withErrors(['otp' => 'Session expired. Please register again.']);
        }

        $email = $pending['email'];

        // Find the latest OTP record for this email
        $otpRecord = EmailOtp::where('email', $email)
            ->latest()
            ->first();

        if (! $otpRecord) {
            return back()->withErrors(['otp' => 'No verification code found. Please request a new one.']);
        }

        // Check max attempts
        if ($otpRecord->isMaxAttemptsExceeded()) {
            $otpRecord->delete();
            $request->session()->forget('pending_registration');
            return redirect()->route('register')
                ->withErrors(['email' => 'Too many incorrect attempts. Please register again.']);
        }

        // Increment attempt count
        $otpRecord->increment('attempts');

        // Codes are valid for CODE_VALID_MINUTES (security: an old code seen
        // later, e.g. in a forwarded email, must not still work).
        if ($otpRecord->isExpired()) {
            $otpRecord->delete();

            return back()->withErrors(['otp' => self::CODE_EXPIRED_MESSAGE]);
        }

        // Verify OTP. An address that already has an account never gets a
        // new one (its code was never sent; this also covers a sign-up race).
        if (! $otpRecord->matches($request->otp) || ! empty($pending['existing'])
            || User::where('email', $email)->exists()) {
            $remaining = 5 - $otpRecord->fresh()->attempts;
            return back()->withErrors([
                'otp' => "Invalid verification code. {$remaining} attempt(s) remaining.",
            ]);
        }

        // OTP is valid — create the user
        // forceFill: email_verified_at isn't mass-assignable on User, so
        // passing it to create() was silently dropped and every OTP-verified
        // account was stored as unverified.
        $user = (new User)->forceFill([
            'name'     => $pending['name'],
            'email'    => $pending['email'],
            'password' => $pending['password'], // already hashed
            'email_verified_at' => now(),
        ]);
        $user->save();

        // Subscribe to newsletter if requested
        if (! empty($pending['subscribe_newsletter'])) {
            NewsletterSubscriber::firstOrCreate(
                ['email' => $user->email],
                ['subscribed_at' => now()]
            );
        }

        // Clean up
        $otpRecord->delete();
        $request->session()->forget('pending_registration');

        // Fire registered event & log in
        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('home')->with('success', 'Your email has been verified. Welcome to TYT Luxe!');
    }

    /**
     * Resend the OTP to the pending email.
     */
    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('pending_registration');

        if (! $pending) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Session expired. Please register again.']);
        }

        $email = $pending['email'];

        if ($limit = self::codeLimitReached($email)) {
            return back()->withErrors(['otp' => $limit]);
        }

        // Delete all old OTPs for this email
        EmailOtp::where('email', $email)->delete();

        // Generate and send a new OTP
        $purpose = empty($pending['existing']) ? 'register' : 'account_exists';
        if ($this->generateAndSendOtp($email, $pending['name'], $purpose) === null) {
            return back()->withErrors(['otp' => self::EMAIL_FAILED_MESSAGE]);
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    /** How long a 6-digit code works (sign-up and email change). */
    public const CODE_VALID_MINUTES = 10;

    public const CODE_EXPIRED_MESSAGE = 'This code has expired. Please click "Resend Code" to get a new one.';

    /** Most codes one email address, or one network (IP), can be sent per hour. */
    public const MAX_CODES_PER_EMAIL_PER_HOUR = 5;

    public const MAX_CODES_PER_IP_PER_HOUR = 15;

    public const TOO_MANY_CODES_MESSAGE = 'Too many verification codes have been requested. Please wait an hour and try again, or contact our support team.';

    /**
     * Counts a code about to be sent. Returns TOO_MANY_CODES_MESSAGE (and
     * counts nothing) when this email or IP has had its hourly share, so
     * nobody can flood an inbox or run up the email bill.
     */
    public static function codeLimitReached(string $email): ?string
    {
        $keys = [
            'otp-email:'.sha1(strtolower($email)) => self::MAX_CODES_PER_EMAIL_PER_HOUR,
            'otp-ip:'.request()->ip() => self::MAX_CODES_PER_IP_PER_HOUR,
        ];

        foreach ($keys as $key => $max) {
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $max)) {
                return self::TOO_MANY_CODES_MESSAGE;
            }
        }
        foreach (array_keys($keys) as $key) {
            \Illuminate\Support\Facades\RateLimiter::hit($key, 3600);
        }

        return null;
    }

    public const EMAIL_FAILED_MESSAGE = 'We couldn\'t send the verification email just now. Please try again in a few minutes, or contact our support team.';

    /**
     * Generate a 6-digit OTP, persist it and send the email. Returns null
     * (and removes the unsent OTP) when the email can't be sent — e.g. the
     * mail server is unreachable — so the guest sees a friendly message
     * instead of an error page.
     */
    public static function generateAndSendOtp(string $email, string $name, string $purpose = 'register'): ?string
    {
        // Remove any existing OTPs for this email
        EmailOtp::where('email', $email)->delete();

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtp::create([
            'email'      => $email,
            'otp'        => EmailOtp::hashCode($otp),
            'expires_at' => now()->addMinutes(self::CODE_VALID_MINUTES),
        ]);

        try {
            // 'account_exists': the code is stored (so the code page behaves the
            // same) but never sent, so it can't be used.
            Mail::to($email)->send(new OtpVerificationMail($purpose === 'account_exists' ? '' : $otp, $name, $purpose));
        } catch (\Throwable $e) {
            EmailOtp::where('email', $email)->delete();
            \Illuminate\Support\Facades\Log::error('otp_email_send_failed', ['email' => $email, 'error' => $e->getMessage()]);

            return null;
        }

        return $otp;
    }
}
