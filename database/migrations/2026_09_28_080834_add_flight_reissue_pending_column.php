<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Persisted (not session-only) for the same reason
            // flight_segments_payload is: Razorpay's webhook confirms
            // payment server-to-server with no PHP session available, so
            // everything auto-reissue's Book step needs must be readable
            // from the booking row alone.
            $table->json('flight_reissue_pending')->nullable()->after('flight_reissue_history');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('flight_reissue_pending');
        });
    }
};
