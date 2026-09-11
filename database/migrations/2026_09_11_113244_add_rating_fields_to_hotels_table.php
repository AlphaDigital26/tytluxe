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
        Schema::table('hotels', function (Blueprint $table) {
            $table->decimal('rating_score', 3, 1)->nullable()->after('star_rating');
            $table->unsignedInteger('review_count')->nullable()->after('rating_score');
            $table->string('rating_tagline')->nullable()->after('review_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['rating_score', 'review_count', 'rating_tagline']);
        });
    }
};
