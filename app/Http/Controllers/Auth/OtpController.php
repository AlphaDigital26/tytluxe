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

        // Expiry check removed as requested

        // Verify OTP
        if ($otpRecord->otp !== $request->otp) {
            $remaining = 5 - $otpRecord->fresh()->attempts;
            return back()->withErrors([
                'otp' => "Invalid verification code. {$remaining} attempt(s) remaining.",
            ]);
        }

        // OTP is valid — create the user
        $user = User::create([
            'name'     => $pending['name'],
            'email'    => $pending['email'],
            'password' => $pending['password'], // already hashed
            'email_verified_at' => now(),
        ]);

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

        // Delete all old OTPs for this email
        EmailOtp::where('email', $email)->delete();

        // Generate and send a new OTP
        $otp = $this->generateAndSendOtp($email, $pending['name']);

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    /**
     * Generate a 6-digit OTP, persist it and send the email.
     */
    public static function generateAndSendOtp(string $email, string $name): string
    {
        // Remove any existing OTPs for this email
        EmailOtp::where('email', $email)->delete();

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtp::create([
            'email'      => $email,
            'otp'        => $otp,
            'expires_at' => now()->addYears(1),
        ]);

        Mail::to($email)->send(new OtpVerificationMail($otp, $name));

        return $otp;
    }
}
