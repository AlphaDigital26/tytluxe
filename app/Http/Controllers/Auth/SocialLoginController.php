<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // If user exists, check if they are suspended
                if ($user->status === 'Suspended') {
                    return redirect()->route('login')->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
                }

                // If user exists, update their google_id if it's not set
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                // Create a new user
                // forceFill: email_verified_at isn't mass-assignable, so create() dropped it.
                $user = (new User)->forceFill([
                    'name' => $googleUser->getName() ?? 'Google User',
                    'email' => strtolower($googleUser->getEmail()),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(), // since Google verifies email
                ]);
                $user->save();
            }

            Auth::login($user, true); // true for "remember me"

            return redirect()->intended('/'); // Or wherever your default authenticated route is

        } catch (\Exception $e) {
            // The real error goes to the log only; guests shouldn't see server details.
            \Illuminate\Support\Facades\Log::warning('google_login_failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['email' => 'We couldn\'t sign you in with Google. Please try again, or sign in with your email and password.']);
        }
    }
}
