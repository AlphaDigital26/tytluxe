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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('vertical', ['hotel', 'cruise', 'staycation', 'package', 'general'])->default('general');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('author_name');
            $table->string('author_location')->nullable();
            $table->string('avatar_path')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('title')->nullable();
            $table->text('body');
            $table->json('images')->nullable();
            $table->unsignedTinyInteger('rating_guide')->nullable();
            $table->unsignedTinyInteger('rating_accommodation')->nullable();
            $table->unsignedTinyInteger('rating_value')->nullable();
            $table->unsignedTinyInteger('rating_itinerary')->nullable();
            $table->text('admin_reply')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->index(['vertical', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
