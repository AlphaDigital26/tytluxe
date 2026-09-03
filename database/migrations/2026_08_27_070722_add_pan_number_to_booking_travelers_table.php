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
        Schema::table('booking_travelers', function (Blueprint $table) {
            $table->string('pan_number', 20)->nullable()->after('passport_expiry');
            $table->string('title', 10)->nullable()->after('full_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_travelers', function (Blueprint $table) {
            $table->dropColumn(['pan_number', 'title']);
        });
    }
};
