<?php

namespace App\Models;

use App\Services\PackageBookingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageDeparture extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $touches = ['package'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'total_seats' => 'integer',
        'extra_price' => 'decimal:2',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'package_departure_id');
    }

    /**
     * Seats taken: paid bookings, plus ones still at the payment step (held
     * for PackageBookingService::SEAT_HOLD_MINUTES so two guests can't pay
     * for the last seat). Cancelled bookings free their seats.
     */
    public function seatsTaken(?int $exceptBookingId = null): int
    {
        return (int) $this->bookings()
            ->where('vertical', 'package')
            ->when($exceptBookingId, fn ($q) => $q->where('id', '!=', $exceptBookingId))
            ->where(fn ($q) => $q
                ->whereIn('status', ['confirmed', 'pending_confirmation', 'failed_needs_review'])
                ->orWhere(fn ($q) => $q->whereIn('status', ['pending_payment', 'payment_failed'])
                    ->where('created_at', '>', now()->subMinutes(PackageBookingService::SEAT_HOLD_MINUTES))))
            ->sum('pax_adults');
    }

    /** Null = no seat limit set. */
    public function seatsLeft(?int $exceptBookingId = null): ?int
    {
        return $this->total_seats === null ? null : max(0, $this->total_seats - $this->seatsTaken($exceptBookingId));
    }

    /** Online booking closes when the balance falls due (2 days before departure). */
    public function isOpenForBooking(): bool
    {
        return $this->start_date !== null
            && today()->lt($this->start_date->copy()->subDays(PackageBookingService::BALANCE_DUE_DAYS_BEFORE))
            && $this->seatsLeft() !== 0;
    }
}
