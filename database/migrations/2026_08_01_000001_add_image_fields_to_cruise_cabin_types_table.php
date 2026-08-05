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
        Schema::table('cruise_cabin_types', function (Blueprint $table) {
            // Uploaded file path (stored in public disk)
            $table->string('image_path')->nullable()->after('price_from');
            // External URL (e.g. Unsplash) – either/both can be used
            $table->string('image_url')->nullable()->after('image_path');
            // Display tier label (e.g. "Most Luxurious", "Premium", "Value")
            $table->string('tier_label')->nullable()->after('image_url');
            // Extra info line (e.g. "Cabin: 596 Sq. Ft | Balcony: 222 Sq. Ft")
            $table->string('size_info')->nullable()->after('tier_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cruise_cabin_types', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'image_url', 'tier_label', 'size_info']);
        });
    }
};
