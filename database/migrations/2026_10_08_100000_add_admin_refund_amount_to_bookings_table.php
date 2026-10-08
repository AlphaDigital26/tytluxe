<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * admin_refund_amount: the refund staff chose in the hotel "Cancel &
 * Refund" action. TripJack can take hours or days to finish a cancellation,
 * and whichever path finishes it later (guest page, scheduler, admin) must
 * refund this amount instead of the automatic penalty-based one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('admin_refund_amount', 10, 2)->nullable()->after('cancellation_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('admin_refund_amount');
        });
    }
};
