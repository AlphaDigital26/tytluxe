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
            // Captured from the Review response at submission time. Per
            // TripJack's v3 docs: "When reseller/GST passthrough details are
            // received in the detail response, the same GST details must be
            // passed in the booking request under the gstInfo object." Stored
            // here so the post-payment Book call (which may run from a
            // webhook with no session) can echo it back without re-fetching.
            $table->string('tripjack_gst_type')->nullable()->after('meal_basis');
            $table->json('tripjack_gst_info')->nullable()->after('tripjack_gst_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['tripjack_gst_type', 'tripjack_gst_info']);
        });
    }
};
