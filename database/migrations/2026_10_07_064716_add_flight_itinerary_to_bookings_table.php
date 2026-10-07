<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * flight_itinerary: airline, flight number and times per segment, saved
 * from TripJack's Booking Details so the admin booking page can show them
 * without a live TripJack call.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('flight_itinerary')->nullable()->after('flight_legs');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('flight_itinerary');
        });
    }
};
