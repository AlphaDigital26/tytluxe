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
        $bookings = $request->user()->bookings()->with(['hotel.destination', 'hotel.images', 'package'])->orderBy('check_in', 'desc')->get();
        
        $upcomingBookings = $bookings->filter(function ($b) {
            return !$b->check_in || \Carbon\Carbon::parse($b->check_in)->isFuture() || \Carbon\Carbon::parse($b->check_in)->isToday();
        });

        $pastBookings = $bookings->filter(function ($b) {
            return $b->check_in && \Carbon\Carbon::parse($b->check_in)->isPast() && !\Carbon\Carbon::parse($b->check_in)->isToday();
        });

        return view('profile.history', [
            'user' => $request->user(),
            'upcomingBookings' => $upcomingBookings,
            'pastBookings' => $pastBookings,
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
     * "Download My Data" — a personal-data export covering the profile
     * fields, saved co-travellers, and booking history tied to this
     * account. Deliberately excludes auth secrets (password hash, 2FA
     * secrets, remember token) even though those are already hidden on the
     * model — this is a separate, explicit allowlist so the export can
     * never accidentally widen as new fields are added to User later.
     */
    public function exportData(Request $request)
    {
        $user = $request->user();

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'profile' => [
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dob' => $user->dob?->toDateString(),
                'gender' => $user->gender,
                'nationality' => $user->nationality,
                'marital_status' => $user->marital_status,
                'anniversary' => $user->anniversary?->toDateString(),
                'passport_no' => $user->passport_no,
                'passport_expiry' => $user->passport_expiry?->toDateString(),
                'passport_issuing_country' => $user->passport_issuing_country,
                'govt_ids' => $user->govt_ids,
                'address' => $user->address,
                'preferences' => $user->preferences,
                'notifications' => $user->notifications,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'saved_travellers' => $user->savedTravellers->map(fn ($t) => [
                'first_name' => $t->first_name,
                'last_name' => $t->last_name,
                'relationship' => $t->relationship,
                'dob' => $t->dob?->toDateString(),
                'gender' => $t->gender,
                'nationality' => $t->nationality,
                'passport_number' => $t->passport_number,
                'passport_expiry' => $t->passport_expiry?->toDateString(),
                'passport_issuing_country' => $t->passport_issuing_country,
                'phone' => $t->phone,
                'email' => $t->email,
            ])->all(),
            'bookings' => $user->bookings()->with('hotel')->get()->map(fn ($b) => [
                'reference' => $b->reference,
                'hotel' => $b->hotel?->title,
                // check_in/check_out aren't cast to Carbon on Booking (plain
                // date strings from the DB already) — unlike dob/anniversary/
                // passport_expiry above, which are.
                'check_in' => $b->check_in,
                'check_out' => $b->check_out,
                'lead_guest_name' => $b->lead_guest_name,
                'total_amount' => $b->total_amount,
                'currency' => $b->currency,
                'status' => $b->status,
                'booked_on' => $b->created_at?->toIso8601String(),
            ])->all(),
        ];

        $filename = 'tytluxe-my-data-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
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
            'type' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'relationship' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'nationality' => 'nullable|string',
            'meal_preference' => 'nullable|string',
            'train_berth_preference' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'passport_expiry' => 'nullable|date',
            'passport_issuing_country' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'frequent_flyer_airline' => 'nullable|string',
            'frequent_flyer_number' => 'nullable|string',
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
            'type' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'relationship' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'nationality' => 'nullable|string',
            'meal_preference' => 'nullable|string',
            'train_berth_preference' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'passport_expiry' => 'nullable|date',
            'passport_issuing_country' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'frequent_flyer_airline' => 'nullable|string',
            'frequent_flyer_number' => 'nullable|string',
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
