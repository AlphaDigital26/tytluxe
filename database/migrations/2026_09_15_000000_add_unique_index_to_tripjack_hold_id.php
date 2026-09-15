<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each TripJack Review call mints one fresh tripjack_hold_id (bookingId),
     * so it's a natural idempotency key: a guest double-submitting the
     * "Proceed to Pay" form before the session draft is cleared would
     * otherwise create two Booking + Razorpay order rows for the same room
     * hold. The unique index turns the second insert into a catchable
     * failure instead of a silent duplicate (see submitBooking()).
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unique('tripjack_hold_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['tripjack_hold_id']);
        });
    }
};
