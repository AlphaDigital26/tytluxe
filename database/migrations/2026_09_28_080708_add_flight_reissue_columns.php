<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // TripJack: "A booking cannot be reissued more than once" — this
            // is the guard, checked both when offering the option and again
            // server-side on submit.
            $table->timestamp('flight_reissued_at')->nullable()->after('flight_partial_amendments');
            // Audit trail — Auto Reissue creates a NEW TripJack bookingId
            // (booking.tripjack_booking_id gets overwritten with it), so
            // this is the only record of what the booking looked like
            // before, and what the guest paid for the change.
            $table->json('flight_reissue_history')->nullable()->after('flight_reissued_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['flight_reissued_at', 'flight_reissue_history']);
        });
    }
};
