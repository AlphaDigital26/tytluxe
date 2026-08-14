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
            $table->string('image_path')->nullable()->after('name');
            $table->text('description')->nullable()->after('image_path');
            $table->string('room_size')->nullable()->after('occupancy_children');
            $table->string('bed_type')->nullable()->after('room_size');
            $table->json('inclusions')->nullable()->after('bed_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'description', 'room_size', 'bed_type', 'inclusions']);
        });
    }
};
