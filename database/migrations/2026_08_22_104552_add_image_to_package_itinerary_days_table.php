<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_itinerary_days', function (Blueprint $table) {
            // Stores the relative path under storage/ (e.g. "packages/itinerary/day1.jpg")
            $table->string('image')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('package_itinerary_days', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
