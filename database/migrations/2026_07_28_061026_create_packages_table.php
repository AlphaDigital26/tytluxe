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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_bg_image')->nullable();
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('region_type')->nullable();
            $table->string('tour_type')->nullable();
            $table->foreignId('destination_id')->nullable()->constrained('destinations')->nullOnDelete();
            $table->string('itinerary_pdf')->nullable();
            $table->unsignedSmallInteger('duration_nights');
            $table->decimal('price_from', 10, 2);
            $table->decimal('booking_amount', 10, 2)->nullable();
            $table->json('departure_from')->nullable();
            $table->string('meals_info')->nullable();
            $table->string('transport_info')->nullable();
            $table->string('stay_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'sqlite') $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
