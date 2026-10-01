<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Reuses tripjack_hold_id (Review's bookingId) and
            // tripjack_booking_id (final TripJack booking id) — same
            // semantic meaning as the hotel columns already on this table,
            // and tripjack_gst_type/tripjack_gst_info (identical gstInfo
            // shape) and tripjack_mf/tripjack_mft (same MF/MFT tax codes
            // per TripJack's shared reference) — no new columns needed
            // for those.
            $table->string('flight_journey_type')->nullable()->after('flight_route'); // ONEWAY / RETURN
            $table->string('flight_cabin_class')->nullable()->after('flight_journey_type');
            $table->string('flight_fare_identifier')->nullable()->after('flight_cabin_class'); // PUBLISHED / SPECIAL_RETURN / TJ_FLEX
            $table->date('flight_departure_date')->nullable()->after('flight_fare_identifier');
            $table->date('flight_return_date')->nullable()->after('flight_departure_date');
            $table->unsignedInteger('pax_infants')->nullable()->after('pax_children');
            // The reviewed fare + traveller/SSR structure Book needs —
            // mirrors tripjack_room_traveller_payload's role for hotels:
            // persisted at submission time so the post-payment Book call
            // (which may run from a webhook with no session) can
            // reconstruct it from the DB alone.
            $table->json('flight_segments_payload')->nullable()->after('tripjack_room_traveller_payload');
            // PNR and ticket numbers are both keyed by "DEP-ARR" per
            // TripJack's Booking Details response — stored as JSON rather
            // than flattened columns since a return/multi-leg booking has
            // more than one.
            $table->json('tripjack_flight_pnr')->nullable()->after('flight_segments_payload');
            $table->json('tripjack_flight_ticket_numbers')->nullable()->after('tripjack_flight_pnr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'flight_journey_type',
                'flight_cabin_class',
                'flight_fare_identifier',
                'flight_departure_date',
                'flight_return_date',
                'pax_infants',
                'flight_segments_payload',
                'tripjack_flight_pnr',
                'tripjack_flight_ticket_numbers',
            ]);
        });
    }
};
