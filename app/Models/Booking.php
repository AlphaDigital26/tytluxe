<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'user_id', 'guest_email', 'guest_phone', 'vertical',
        'hotel_id', 'package_id', 'room_type_id', 'room_name',
        'tripjack_booking_id', 'tripjack_hold_id', 'tripjack_hold_expires_at',
        'tripjack_option_id', 'tripjack_room_traveller_payload', 'tripjack_confirm_attempted_at',
        'check_in', 'check_out', 'flight_route', 'pax_adults', 'pax_children',
        'lead_guest_name', 'special_requests',
        'base_amount', 'tax_amount', 'discount_amount', 'total_amount',
        'tripjack_total_price', 'gst_slab', 'margin_amount', 'gst_on_margin', 'razorpay_recovery',
        'currency', 'offer_id', 'status', 'cancellation_reason', 'cancellation_requested_at', 'admin_note',
    ];

    protected $casts = [
        'tripjack_room_traveller_payload' => 'array',
        'tripjack_hold_expires_at' => 'datetime',
        'cancellation_requested_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function agent() { return $this->belongsTo(User::class, 'agent_id'); }
    public function travelers() { return $this->hasMany(BookingTraveler::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function package() { return $this->belongsTo(Package::class); }
    public function hotel() { return $this->belongsTo(Hotel::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }
}
