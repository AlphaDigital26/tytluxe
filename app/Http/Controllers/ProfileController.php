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
        $bookings = $request->user()->bookings()->with(['hotel.destination', 'hotel.images', 'roomType', 'package'])->orderBy('check_in', 'desc')->get();
        
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
        $user = $request->user();
        $data = $request->validated();

        // A new email only takes effect once the 6-digit code sent to it is
        // entered (verifyEmailChange). Until then the account keeps its old,
        // verified address — never un-verified, since the hourly cleanup in
        // routes/console.php deletes unverified accounts.
        $newEmail = strtolower(trim((string) ($data['email'] ?? '')));
        unset($data['email']);

        $user->fill($data);
        $user->save();

        if ($newEmail === '' || $newEmail === strtolower($user->email)) {
            $request->session()->forget('pending_email_change');

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        }

        if ($limit = \App\Http\Controllers\Auth\OtpController::codeLimitReached($newEmail)) {
            return Redirect::route('profile.edit')->withErrors(['email' => $limit]);
        }

        if (\App\Http\Controllers\Auth\OtpController::generateAndSendOtp($newEmail, $user->name, 'email_change') === null) {
            return Redirect::route('profile.edit')
                ->withErrors(['email' => \App\Http\Controllers\Auth\OtpController::EMAIL_FAILED_MESSAGE]);
        }

        $request->session()->put('pending_email_change', ['user_id' => $user->id, 'email' => $newEmail]);

        return Redirect::route('profile.email.verify');
    }

    /** The guest's own pending email change, or null. */
    protected function pendingEmailChange(Request $request): ?array
    {
        $pending = $request->session()->get('pending_email_change');

        return $pending && (int) ($pending['user_id'] ?? 0) === $request->user()->id ? $pending : null;
    }

    public function showEmailVerify(Request $request): View|RedirectResponse
    {
        $pending = $this->pendingEmailChange($request);
        if (! $pending) {
            return Redirect::route('profile.edit');
        }

        return view('profile.verify-email-change', ['newEmail' => $pending['email']]);
    }

    public function verifyEmailChange(Request $request): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/']]);

        $pending = $this->pendingEmailChange($request);
        if (! $pending) {
            return Redirect::route('profile.edit')->withErrors(['email' => 'Your email change has expired. Please enter the new email again.']);
        }

        $otp = \App\Models\EmailOtp::where('email', $pending['email'])->latest()->first();
        if (! $otp) {
            return back()->withErrors(['otp' => 'No verification code found. Please request a new one.']);
        }

        if ($otp->isMaxAttemptsExceeded()) {
            $otp->delete();
            $request->session()->forget('pending_email_change');

            return Redirect::route('profile.edit')->withErrors(['email' => 'Too many incorrect codes. Your email was not changed — please try again.']);
        }

        $otp->increment('attempts');

        if ($otp->isExpired()) {
            $otp->delete();

            return back()->withErrors(['otp' => \App\Http\Controllers\Auth\OtpController::CODE_EXPIRED_MESSAGE]);
        }

        if (! $otp->matches((string) $request->input('otp'))) {
            return back()->withErrors(['otp' => 'Invalid verification code. '.(5 - $otp->fresh()->attempts).' attempt(s) remaining.']);
        }

        // Someone may have registered this address since the code was sent.
        if (\App\Models\User::where('email', $pending['email'])->whereKeyNot($request->user()->id)->exists()) {
            $otp->delete();
            $request->session()->forget('pending_email_change');

            return Redirect::route('profile.edit')->withErrors(['email' => 'This email is already used by another account.']);
        }

        $request->user()->forceFill(['email' => $pending['email'], 'email_verified_at' => now()])->save();
        $otp->delete();
        $request->session()->forget('pending_email_change');

        return Redirect::route('profile.edit')->with('status', 'email-updated')->with('email_notice', 'Your email has been changed to '.$pending['email'].'.');
    }

    public function resendEmailChange(Request $request): RedirectResponse
    {
        $pending = $this->pendingEmailChange($request);
        if (! $pending) {
            return Redirect::route('profile.edit');
        }

        if ($limit = \App\Http\Controllers\Auth\OtpController::codeLimitReached($pending['email'])) {
            return back()->withErrors(['otp' => $limit]);
        }

        if (\App\Http\Controllers\Auth\OtpController::generateAndSendOtp($pending['email'], $request->user()->name, 'email_change') === null) {
            return back()->withErrors(['otp' => \App\Http\Controllers\Auth\OtpController::EMAIL_FAILED_MESSAGE]);
        }

        return back()->with('success', 'A new verification code has been sent to '.$pending['email'].'.');
    }

    public function cancelEmailChange(Request $request): RedirectResponse
    {
        if ($pending = $this->pendingEmailChange($request)) {
            \App\Models\EmailOtp::where('email', $pending['email'])->delete();
        }
        $request->session()->forget('pending_email_change');

        return Redirect::route('profile.edit')->with('status', 'email-change-cancelled')->with('email_notice', 'Email change cancelled. Your email was not changed.');
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
            'bookings' => $user->bookings()->with(['hotel', 'package'])->get()->map(fn ($b) => [
                'reference' => $b->reference,
                'type' => $b->vertical,
                // What was booked, whatever the type.
                'booked' => match ($b->vertical) {
                    'flight' => trim('Flight '.str_replace('-', ' → ', (string) $b->flight_route)),
                    'package' => $b->package?->title.($b->package_option && $b->package_option !== 'Per person' ? ' ('.$b->package_option.')' : ''),
                    default => $b->hotel?->title.($b->room_name ? ' — '.$b->room_name : ''),
                },
                'hotel' => $b->hotel?->title,
                'package' => $b->package?->title,
                'flight_route' => $b->flight_route,
                'flight_pnr' => $b->tripjack_flight_pnr,
                'travellers' => (int) $b->pax_adults + (int) $b->pax_children + (int) $b->pax_infants,
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

        // Never leave a paid trip without its owner: the guest would lose the
        // booking page, invoice, balance payment and cancellation.
        $activeTrip = $user->bookings()
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->whereIn('status', ['confirmed', 'pending_confirmation', 'on_hold', 'failed_needs_review'])
                    ->where(fn ($q) => $q->whereNull('check_in')->orWhereDate('check_in', '>=', today())))
                ->orWhereNotNull('manual_refund_due_at')
                ->orWhere(fn ($q) => $q->where('status', 'confirmed')->whereNotNull('cancellation_requested_at')))
            ->orderBy('check_in')
            ->first();

        if ($activeTrip) {
            $when = $activeTrip->check_in ? ' on '.\Illuminate\Support\Carbon::parse($activeTrip->check_in)->format('j M Y') : '';

            return back()->withErrors([
                'password' => "You have a booking ({$activeTrip->reference}){$when} that is still active, so your account can't be deleted yet. Please wait until after your trip, or contact us on +91 98750 73788 and we'll help.",
            ], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    protected const TRAVELLER_MESSAGES = [
        'dob.before_or_equal' => 'Date of birth cannot be in the future.',
        'passport_expiry.after' => 'This passport has expired. Please enter a valid passport.',
        'phone.regex' => 'Please enter a valid phone number.',
    ];

    public function storeTraveller(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'relationship' => 'nullable|string',
            'dob' => 'nullable|date|before_or_equal:today|after:1900-01-01',
            'gender' => 'nullable|string',
            'nationality' => 'nullable|string',
            'meal_preference' => 'nullable|string',
            'train_berth_preference' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'passport_expiry' => 'nullable|date|after:today',
            'passport_issuing_country' => 'nullable|string',
            'phone' => ['nullable', 'regex:/^\+?[\d\s-]{7,18}$/'],
            'email' => 'nullable|email',
            'frequent_flyer_airline' => 'nullable|string',
            'frequent_flyer_number' => 'nullable|string',
        ], self::TRAVELLER_MESSAGES);

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
            'dob' => 'nullable|date|before_or_equal:today|after:1900-01-01',
            'gender' => 'nullable|string',
            'nationality' => 'nullable|string',
            'meal_preference' => 'nullable|string',
            'train_berth_preference' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'passport_expiry' => 'nullable|date|after:today',
            'passport_issuing_country' => 'nullable|string',
            'phone' => ['nullable', 'regex:/^\+?[\d\s-]{7,18}$/'],
            'email' => 'nullable|email',
            'frequent_flyer_airline' => 'nullable|string',
            'frequent_flyer_number' => 'nullable|string',
        ], self::TRAVELLER_MESSAGES);

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
