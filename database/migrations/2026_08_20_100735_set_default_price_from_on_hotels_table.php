<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // Set default to 0 since price is now handled on request — no longer entered via the form
            $table->decimal('price_from', 10, 2)->default(0)->nullable()->change();
        });

        Schema::table('room_types', function (Blueprint $table) {
            // Same for room price_per_night — no longer entered via the form
            $table->decimal('price_per_night', 10, 2)->default(0)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->decimal('price_from', 10, 2)->default(null)->nullable(false)->change();
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->decimal('price_per_night', 10, 2)->default(null)->nullable(false)->change();
        });
    }
};
