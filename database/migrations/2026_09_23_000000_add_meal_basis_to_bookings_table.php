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
            // TripJack's review response already gives us the meal plan
            // (Room Only / Breakfast / Half Board / ...) at booking time —
            // same source as room_name above — but it was never persisted,
            // so it couldn't be shown on the invoice or confirmation page
            // for a booking already placed.
            $table->string('meal_basis')->nullable()->after('room_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('meal_basis');
        });
    }
};
