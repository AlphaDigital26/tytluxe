<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Accounts are stored in lowercase; people often type capitals.
        Password::sendResetLink(['email' => strtolower(trim($request->input('email')))]);

        // Same message whether or not the account exists (and when a link
        // was sent moments ago), so the form can't be used to find out who
        // has an account.
        return back()->with('status', 'If an account exists for this email, we have sent a password reset link. Please check your inbox and spam folder.');
    }
}
