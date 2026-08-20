<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            // ── Display / Card fields ──────────────────────────────────
            // category_key maps to the filter tabs: hotels, cruises, flights, honeymoon, family, packages
            $table->string('category_key')->default('hotels')->after('title')->index();

            // Short tagline shown below the card title
            $table->string('subtitle')->nullable()->after('category_key');

            // Display price string e.g. "₹12,500 / night" or "From ₹45,000"
            $table->string('display_price')->nullable()->after('subtitle');

            // WhatsApp or page URL for the Enquire button on the card
            $table->string('enquire_link')->nullable()->after('display_price');

            // Badge: label text + style class
            $table->string('badge_label')->nullable()->after('enquire_link');
            $table->enum('badge_type', ['badge-gold', 'badge-hot', 'badge-new'])->default('badge-gold')->after('badge_label');

            // Show a "Deal Coming Soon" ribbon instead of the card content
            $table->boolean('coming_soon')->default(false)->after('badge_type');

            // Card image: uploaded file path OR external URL
            $table->string('image_path')->nullable()->after('coming_soon');
            $table->string('image_url')->nullable()->after('image_path');

            // Slider section heading (applies to all cards in this category_key)
            // We store it per-offer but only the first one of each category_key group is used.
            $table->string('slider_label')->nullable()->after('image_url');
            $table->string('slider_title')->nullable()->after('slider_label');

            // Display ordering
            $table->unsignedInteger('sort_order')->default(0)->after('slider_title')->index();
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'category_key', 'subtitle', 'display_price', 'enquire_link',
                'badge_label', 'badge_type', 'coming_soon',
                'image_path', 'image_url',
                'slider_label', 'slider_title', 'sort_order',
            ]);
        });
    }
};
