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
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('check_in_time')->default('2:00 PM');
            $table->string('check_out_time')->default('11:00 AM');
            $table->text('nearby_attractions')->nullable();
            $table->text('room_categories')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['check_in_time', 'check_out_time', 'nearby_attractions', 'room_categories']);
        });
    }
};
