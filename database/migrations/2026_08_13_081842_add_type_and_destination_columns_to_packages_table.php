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
            $table->string('region_type')->nullable()->after('description');
            $table->string('tour_type')->nullable()->after('region_type');
            $table->unsignedBigInteger('destination_id')->nullable()->after('tour_type');

            $table->foreign('destination_id')->references('id')->on('destinations')->nullOnDelete();
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
