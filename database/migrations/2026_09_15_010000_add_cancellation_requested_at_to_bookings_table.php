<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marks the moment a guest requested cancellation — set before calling
     * TripJack's cancel-booking API (same claim-before-call pattern as
     * tripjack_confirm_attempted_at) so a double form-submit can't fire the
     * cancellation request twice, and so the confirmation page can show
     * "cancellation in progress" even while booking.status still reads
     * 'confirmed' (TripJack's CANCELLATION_PENDING can take days to resolve
     * offline, so status itself isn't flipped until it actually lands on
     * CANCELLED).
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('cancellation_requested_at');
        });
    }
};
