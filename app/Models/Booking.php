<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'user_id', 'guest_email', 'guest_phone', 'guest_phone_code', 'vertical',
        'hotel_id', 'package_id', 'room_type_id', 'room_name', 'meal_basis',
        'tripjack_gst_type', 'tripjack_gst_info',
        'tripjack_booking_id', 'hotel_confirmation_number', 'tripjack_hold_id', 'tripjack_hold_expires_at',
        'tripjack_option_id', 'tripjack_room_traveller_payload', 'tripjack_confirm_attempted_at',
        'check_in', 'check_out', 'flight_route', 'pax_adults', 'pax_children', 'pax_infants',
        'flight_journey_type', 'flight_cabin_class', 'flight_fare_identifier',
        'flight_departure_date', 'flight_return_date', 'flight_segments_payload',
        'tripjack_flight_pnr', 'tripjack_flight_ticket_numbers',
        'flight_ssr_options_cache', 'flight_ssr_pending_selection', 'flight_ssr_amendment_ids',
        'flight_ssr_confirmed', 'flight_ssr_amount_paid', 'flight_ssr_status',
        'flight_legs', 'flight_itinerary', 'flight_partial_amendments', 'flight_reissued_at', 'flight_reissue_history',
        'flight_reissue_pending',
        'lead_guest_name', 'special_requests',
        'base_amount', 'tax_amount', 'discount_amount', 'total_amount',
        'tripjack_total_price', 'gst_slab', 'margin_amount', 'gst_on_margin', 'razorpay_recovery',
        'tripjack_mf', 'tripjack_mft',
        'currency', 'offer_id', 'status', 'cancellation_reason', 'cancellation_requested_at', 'admin_note',
    ];

    protected $casts = [
        'tripjack_room_traveller_payload' => 'array',
        'tripjack_gst_info' => 'array',
        'flight_segments_payload' => 'array',
        'tripjack_flight_pnr' => 'array',
        'tripjack_flight_ticket_numbers' => 'array',
        'flight_ssr_options_cache' => 'array',
        'flight_ssr_pending_selection' => 'array',
        'flight_ssr_amendment_ids' => 'array',
        'flight_ssr_confirmed' => 'array',
        'flight_legs' => 'array',
        'flight_itinerary' => 'array',
        'flight_partial_amendments' => 'array',
        'flight_reissue_history' => 'array',
        'flight_reissue_pending' => 'array',
        'flight_reissued_at' => 'datetime',
        'tripjack_hold_expires_at' => 'datetime',
        'cancellation_requested_at' => 'datetime',
        'flight_departure_date' => 'date',
        'flight_return_date' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function agent() { return $this->belongsTo(User::class, 'agent_id'); }
    public function travelers() { return $this->hasMany(BookingTraveler::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function package() { return $this->belongsTo(Package::class); }
    public function hotel() { return $this->belongsTo(Hotel::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }
}
