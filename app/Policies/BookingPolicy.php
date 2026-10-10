<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Booking;

/**
 * Who may do what with hotel and flight bookings (agreed 2026-10-09):
 *
 * - Super Admin: everything.
 * - Operations: run bookings — check status, notes, contact details,
 *   cancel & refund, release held fares.
 * - Support: help guests — check status, notes, contact details; no money.
 * - Finance: check status, notes, record refunds made in Razorpay.
 * - Content, Analyst: no access — bookings hold guests' personal details.
 */
class BookingPolicy
{
    public const VIEW_ROLES = ['Super Admin', 'Operations', 'Support', 'Finance'];

    public function viewAny(Admin $user): bool
    {
        return in_array($user->role, self::VIEW_ROLES, true);
    }

    public function view(Admin $user, Booking $model): bool
    {
        return $this->viewAny($user);
    }

    /** Bookings come from guests on the website; resources also refuse create. */
    public function create(Admin $user): bool
    {
        return $user->role === 'Super Admin';
    }

    /** "Edit contact details" — name, email, phone and notes only (BookingForm). */
    public function update(Admin $user, Booking $model): bool
    {
        return in_array($user->role, ['Super Admin', 'Operations', 'Support'], true);
    }

    /** Re-reads TripJack; can confirm, cancel or refund as a side effect. */
    public function checkStatus(Admin $user, Booking $model): bool
    {
        return $this->viewAny($user);
    }

    public function addNote(Admin $user, Booking $model): bool
    {
        return $this->viewAny($user);
    }

    /** Cancel & refund (hotel and flight), release a held flight fare. */
    public function cancel(Admin $user, Booking $model): bool
    {
        return in_array($user->role, ['Super Admin', 'Operations'], true);
    }

    /** "Record manual refund" after refunding in the Razorpay dashboard. */
    public function recordRefund(Admin $user, Booking $model): bool
    {
        return in_array($user->role, ['Super Admin', 'Finance'], true);
    }

    public function delete(Admin $user, Booking $model): bool
    {
        return $user->role === 'Super Admin';
    }

    public function restore(Admin $user, Booking $model): bool
    {
        return $user->role === 'Super Admin';
    }

    public function forceDelete(Admin $user, Booking $model): bool
    {
        return false; // Never permanently delete from CMS
    }
}
