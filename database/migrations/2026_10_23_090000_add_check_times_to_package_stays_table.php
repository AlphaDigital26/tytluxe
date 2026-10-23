<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a stay happens: check-in / check-out times for typed-in stays (linked
 * hotels use the hotel's own), and an optional trip day the stay starts on —
 * left empty, it follows on from the stay before (see PackageStay::dayPlan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_stays', function (Blueprint $table) {
            $table->unsignedTinyInteger('check_in_day')->nullable()->after('nights');
            $table->string('check_in_time', 20)->nullable()->after('meal_plan');
            $table->string('check_out_time', 20)->nullable()->after('check_in_time');
        });
    }

    public function down(): void
    {
        Schema::table('package_stays', function (Blueprint $table) {
            $table->dropColumn(['check_in_day', 'check_in_time', 'check_out_time']);
        });
    }
};
