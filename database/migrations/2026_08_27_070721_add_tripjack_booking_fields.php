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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('tripjack_hold_expires_at')->nullable()->after('tripjack_hold_id');
            $table->string('tripjack_option_id', 100)->nullable()->after('tripjack_hold_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['tripjack_hold_expires_at', 'tripjack_option_id']);
        });
    }
};
