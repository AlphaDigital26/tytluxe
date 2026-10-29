<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The TripJack reference of a full flight cancellation, so staff can check it
// from the booking page and quote it to TripJack support.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('flight_cancel_amendment_id', 40)->nullable()->after('cancellation_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn('flight_cancel_amendment_id'));
    }
};
