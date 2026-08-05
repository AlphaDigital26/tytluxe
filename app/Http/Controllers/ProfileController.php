<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's booking history / profile dashboard.
     */
    public function history(Request $request): View
    {
        return view('profile.history', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function storeTraveller(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'nationality' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'passport_expiry' => 'nullable|date',
            'passport_issuing_country' => 'nullable|string',
        ]);

        $request->user()->savedTravellers()->create($validated);

        return Redirect::route('profile.edit')->with('status', 'traveller-saved');
    }

    public function updateTraveller(Request $request, \App\Models\UserTraveller $traveller): RedirectResponse
    {
        if ($traveller->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|string',
            'name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'nationality' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'passport_expiry' => 'nullable|date',
            'passport_issuing_country' => 'nullable|string',
        ]);

        $traveller->update($validated);

        return Redirect::route('profile.edit')->with('status', 'traveller-updated');
    }

    public function deleteTraveller(Request $request, \App\Models\UserTraveller $traveller): RedirectResponse
    {
        if ($traveller->user_id !== $request->user()->id) {
            abort(403);
        }
        
        $traveller->delete();

        return Redirect::route('profile.edit')->with('status', 'traveller-deleted');
    }

    public function logoutOtherDevices(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::logoutOtherDevices($request->password);

        return Redirect::route('profile.edit')->with('status', 'logged-out-other-devices');
    }
}
