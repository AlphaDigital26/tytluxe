<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Instead of immediately creating the user, we store the
     * form data in the session, generate an OTP, and redirect
     * to the OTP verification page.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // People type "Ananya@Gmail.com"; accounts are stored in lowercase.
        // Only for text: a list sent as email= must fail validation, not crash.
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            // No "already taken" check here: saying so would tell anyone which
            // emails have an account. Existing addresses get the same next
            // page, and their owner is emailed instead of getting a code.
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($limit = OtpController::codeLimitReached($request->email)) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => $limit]);
        }

        $existing = User::where('email', $request->email)->exists();

        // Store pending registration data in session (password already hashed)
        $request->session()->put('pending_registration', [
            'name'                 => $request->name,
            'email'                => $request->email,
            'password'             => Hash::make($request->password),
            'subscribe_newsletter' => $request->has('subscribe_newsletter'),
            'existing'             => $existing,
        ]);

        // Generate OTP and send verification email
        if (OtpController::generateAndSendOtp($request->email, $request->name, $existing ? 'account_exists' : 'register') === null) {
            $request->session()->forget('pending_registration');

            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => OtpController::EMAIL_FAILED_MESSAGE]);
        }

        return redirect()->route('otp.verify')
            ->with('info', 'A 6-digit verification code has been sent to ' . $request->email);
    }
}
