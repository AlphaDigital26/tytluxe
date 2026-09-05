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
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('chain_name')->nullable()->after('mandatory_fees');
            $table->json('house_rules')->nullable()->after('chain_name');
            $table->text('special_instructions')->nullable()->after('house_rules');
            $table->text('know_before_you_go')->nullable()->after('special_instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['chain_name', 'house_rules', 'special_instructions', 'know_before_you_go']);
        });
    }
};
