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
            // Audit trail for the TYTLUXE markup formula (founder-defined —
            // see HotelPricingService). total_amount remains the customer
            // price actually charged; these columns record how it was
            // derived from TripJack's raw price, for support/finance queries.
            $table->decimal('tripjack_total_price', 10, 2)->nullable()->after('total_amount');
            $table->decimal('gst_slab', 5, 4)->nullable()->after('tripjack_total_price');
            $table->decimal('margin_amount', 10, 2)->nullable()->after('gst_slab');
            $table->decimal('gst_on_margin', 10, 2)->nullable()->after('margin_amount');
            $table->decimal('razorpay_recovery', 10, 2)->nullable()->after('gst_on_margin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['tripjack_total_price', 'gst_slab', 'margin_amount', 'gst_on_margin', 'razorpay_recovery']);
        });
    }
};
