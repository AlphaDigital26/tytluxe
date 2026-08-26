<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration ensures the `image` column exists in `package_itinerary_days`.
     * The original migration (2026_08_22_104552) was accidentally left empty,
     * so the column was never created on the live server.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('package_itinerary_days', 'image')) {
            Schema::table('package_itinerary_days', function (Blueprint $table) {
                $table->string('image')->nullable()->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('package_itinerary_days', 'image')) {
            Schema::table('package_itinerary_days', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
