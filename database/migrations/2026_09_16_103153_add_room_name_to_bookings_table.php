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
            // room_type_id only ever gets set for the old manually-catalogued
            // "Request Price" rooms — a live TripJack booking has no local
            // RoomType row to link to, so $booking->roomType is null for
            // virtually every real booking. This stores the room name text
            // TripJack's review response already gave us at booking time,
            // instead of leaving room type blank everywhere it's shown.
            $table->string('room_name')->nullable()->after('room_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('room_name');
        });
    }
};
