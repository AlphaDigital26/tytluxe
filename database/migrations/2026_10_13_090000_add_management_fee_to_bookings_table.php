<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Our own hotel management fee and its GST (HotelPricingService), kept
 * apart from TripJack's tripjack_mf/tripjack_mft — those are supplier
 * cost, these are ours. Null on flights and on hotel bookings made before
 * the fee existed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('management_fee', 10, 2)->nullable()->after('tripjack_mft');
            $table->decimal('management_fee_gst', 10, 2)->nullable()->after('management_fee');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['management_fee', 'management_fee_gst']);
        });
    }
};
