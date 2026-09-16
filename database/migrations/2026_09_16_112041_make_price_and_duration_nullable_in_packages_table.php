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
        Schema::table('packages', function (Blueprint $table) {
            // A custom/private tour is priced per group after enquiry, not
            // published with a fixed per-person price or fixed duration
            // up front — nullable rather than a misleading placeholder value.
            $table->decimal('price_from', 10, 2)->nullable()->change();
            $table->unsignedSmallInteger('duration_nights')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price_from', 10, 2)->nullable(false)->change();
            $table->unsignedSmallInteger('duration_nights')->nullable(false)->change();
        });
    }
};
