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
            $table->foreignId('destination_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->enum('region_type', ['domestic', 'international'])->default('domestic')->after('destination_id');
            $table->enum('tour_type', ['group', 'custom'])->default('group')->after('region_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['destination_id']);
            $table->dropColumn(['destination_id', 'region_type', 'tour_type']);
        });
    }
};
