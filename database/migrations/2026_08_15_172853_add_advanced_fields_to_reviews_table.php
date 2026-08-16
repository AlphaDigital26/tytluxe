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
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('title')->nullable()->after('rating');
            $table->json('images')->nullable()->after('body');
            $table->unsignedTinyInteger('rating_guide')->nullable()->after('images');
            $table->unsignedTinyInteger('rating_accommodation')->nullable()->after('rating_guide');
            $table->unsignedTinyInteger('rating_value')->nullable()->after('rating_accommodation');
            $table->unsignedTinyInteger('rating_itinerary')->nullable()->after('rating_value');
            $table->text('admin_reply')->nullable()->after('rating_itinerary');
            $table->boolean('is_featured')->default(false)->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'images',
                'rating_guide',
                'rating_accommodation',
                'rating_value',
                'rating_itinerary',
                'admin_reply',
                'is_featured'
            ]);
        });
    }
};
