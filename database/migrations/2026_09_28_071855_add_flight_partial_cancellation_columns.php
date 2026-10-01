<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Persisted per-leg src/dest/departureDate at booking time — the
            // Cancellation Amendment API scopes a partial cancellation to a
            // specific trip via exactly these three fields, and we have no
            // other reliable way to reconstruct them later (flight_route is
            // just a concatenated display string).
            $table->json('flight_legs')->nullable()->after('flight_segments_payload');
            // Accumulated results of partial cancellations / Auto Void /
            // Auto Full Refund on this booking — the booking's own `status`
            // only ever means "the whole booking", so a partial amendment
            // (e.g. cancelling one of three travellers) can't be represented
            // there without wrongly affecting the travellers who are still
            // flying.
            $table->json('flight_partial_amendments')->nullable()->after('flight_legs');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['flight_legs', 'flight_partial_amendments']);
        });
    }
};
