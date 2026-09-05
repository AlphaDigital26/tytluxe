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
        Schema::table('room_types', function (Blueprint $table) {
            // Static Detail room-type content has no price — actual price only
            // exists per live search option, not per static room-type catalogue
            // entry. Nullable rather than a misleading 0/placeholder value,
            // matching the precedent already set for cancellation_policy.
            $table->decimal('price_per_night', 10, 2)->nullable()->default(null)->change();
            $table->string('view_name')->nullable()->after('bed_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->decimal('price_per_night', 10, 2)->nullable(false)->change();
            $table->dropColumn('view_name');
        });
    }
};
