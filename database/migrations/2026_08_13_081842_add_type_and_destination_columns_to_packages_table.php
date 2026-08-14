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
            if (!Schema::hasColumn('packages', 'region_type')) {
                $table->string('region_type')->nullable()->after('description');
            }
            if (!Schema::hasColumn('packages', 'tour_type')) {
                $table->string('tour_type')->nullable()->after('region_type');
            }
            if (!Schema::hasColumn('packages', 'destination_id')) {
                $table->unsignedBigInteger('destination_id')->nullable()->after('tour_type');
                $table->foreign('destination_id')->references('id')->on('destinations')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['destination_id']);
            $table->dropColumn(['region_type', 'tour_type', 'destination_id']);
        });
    }
};
