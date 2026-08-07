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
        Schema::create('hotel_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('hotel_id')->unique()->comment('The Tripjack Hotel ID, e.g. TBOM000000');
            $table->string('override_name')->nullable();
            $table->text('override_description')->nullable();
            $table->string('override_image')->nullable();
            $table->json('override_amenities')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_overrides');
    }
};
