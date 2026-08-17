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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->json('images')->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('occupancy_adults');
            $table->unsignedTinyInteger('occupancy_children')->default(0);
            $table->string('room_size')->nullable();
            $table->string('bed_type')->nullable();
            $table->json('inclusions')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->enum('cancellation_policy', ['free_cancellation', 'non_refundable', 'partial']);
            $table->string('tripjack_room_code', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
