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
        Schema::create('cruise_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cruise_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('image_url')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('alt_text')->nullable();
            $table->timestamps();
            $table->index(['cruise_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cruise_images');
    }
};
