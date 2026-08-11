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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('hotel_id')->constrained('packages')->nullOnDelete();
        });

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE bookings MODIFY COLUMN vertical ENUM('hotel', 'flight', 'package') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE bookings MODIFY COLUMN vertical ENUM('hotel', 'flight') NOT NULL");

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
        });
    }
};
