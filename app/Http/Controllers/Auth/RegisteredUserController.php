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
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Store pending registration data in session (password already hashed)
        $request->session()->put('pending_registration', [
            'name'                 => $request->name,
            'email'                => $request->email,
            'password'             => Hash::make($request->password),
            'subscribe_newsletter' => $request->has('subscribe_newsletter'),
        ]);

        // Generate OTP and send verification email
        OtpController::generateAndSendOtp($request->email, $request->name);

        return redirect()->route('otp.verify')
            ->with('info', 'A 6-digit verification code has been sent to ' . $request->email);
    }
}
