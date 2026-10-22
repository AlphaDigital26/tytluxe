<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The hotels (or camps, homestays…) a package's guests stay in, in trip
 * order — shown as "Where You'll Stay" on the package page. A stay can link
 * to a hotel in the catalogue (photo, stars and page come from it) or be
 * typed in by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_stays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('stay_type', 20)->default('hotel');
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('nights')->nullable();
            $table->unsignedTinyInteger('star_rating')->nullable();
            $table->string('room_type')->nullable();
            $table->string('meal_plan')->nullable();
            $table->string('image')->nullable();
            $table->boolean('or_similar')->default(true);
            $table->string('note', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_stays');
    }
};
