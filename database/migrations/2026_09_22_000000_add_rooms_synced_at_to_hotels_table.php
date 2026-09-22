<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // When this hotel's room types were last fetched from TripJack
            // (via either the single-hotel Static Detail API or the live
            // Pricing API fallback) — null means never. Used to order the
            // rolling room-resync command by staleness, and to avoid
            // re-queuing a hotel that was only just checked.
            $table->timestamp('rooms_synced_at')->nullable()->after('tripjack_hotel_id');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('rooms_synced_at');
        });
    }
};
