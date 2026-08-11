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
        Schema::table('packages', function (Blueprint $table) {
            $table->string('hero_eyebrow')->nullable()->after('title');
            $table->string('hero_bg_image')->nullable()->after('hero_eyebrow');
            $table->decimal('booking_amount', 10, 2)->nullable()->after('price_from');
            $table->string('departure_from')->nullable()->after('booking_amount');
            $table->string('meals_info')->nullable()->after('departure_from');
            $table->string('transport_info')->nullable()->after('meals_info');
            $table->string('stay_info')->nullable()->after('transport_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'hero_eyebrow', 'hero_bg_image', 'booking_amount',
                'departure_from', 'meals_info', 'transport_info', 'stay_info',
            ]);
        });
    }
};
