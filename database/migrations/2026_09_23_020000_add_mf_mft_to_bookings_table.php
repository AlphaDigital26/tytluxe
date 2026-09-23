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
            // TripJack's Management Fee and Management Fee Tax — already
            // included inside their totalPrice, so these are display-only
            // (they don't change base_amount/tax_amount/total_amount).
            // TripJack's docs: "Always display mf and mft as separate line
            // items in the price breakup shown to end users." Persisted here
            // (mirroring gst_slab/margin_amount) so the invoice can show them
            // for a booking placed in an earlier request/session.
            $table->decimal('tripjack_mf', 10, 2)->nullable()->after('razorpay_recovery');
            $table->decimal('tripjack_mft', 10, 2)->nullable()->after('tripjack_mf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['tripjack_mf', 'tripjack_mft']);
        });
    }
};
