<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            // URLs from `images` (TripJack sync) an admin has chosen to hide
            // from visitors. Kept separate from `images` itself so the
            // choice survives every future resync, same as HotelImage's
            // is_hidden flag does for hotel-level photos.
            $table->json('hidden_images')->nullable()->after('manual_images');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('hidden_images');
        });
    }
};
