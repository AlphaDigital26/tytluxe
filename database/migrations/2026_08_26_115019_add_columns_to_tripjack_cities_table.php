<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tripjack_cities', function (Blueprint $table) {
            $table->unsignedBigInteger('city_region_id')->unique()->after('id');
            $table->string('city_name')->index();
            $table->string('region_name')->nullable();
            $table->string('country_name')->index();
            $table->string('region_type')->nullable();
            $table->string('full_region_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tripjack_cities', function (Blueprint $table) {
            $table->dropColumn(['city_region_id', 'city_name', 'region_name', 'country_name', 'region_type', 'full_region_name']);
        });
    }
};
