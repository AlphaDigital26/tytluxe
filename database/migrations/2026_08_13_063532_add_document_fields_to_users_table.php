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
        Schema::table('users', function (Blueprint $table) {
            $table->string('passport_no')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('passport_issuing_country')->nullable();
            $table->string('pan_card_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['passport_no', 'passport_expiry', 'passport_issuing_country', 'pan_card_number']);
        });
    }
};
