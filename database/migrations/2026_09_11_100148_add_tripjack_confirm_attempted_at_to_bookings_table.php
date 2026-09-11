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
            // Guards against calling TripJack's /hotel/confirm-book more than
            // once for the same booking — set the instant the attempt is
            // claimed (before the HTTP call), not after it succeeds, so a
            // racing request (webhook + late poll) can never double-submit
            // confirm-book, which could double-deduct the TripJack wallet.
            $table->timestamp('tripjack_confirm_attempted_at')->nullable()->after('tripjack_room_traveller_payload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('tripjack_confirm_attempted_at');
        });
    }
};
