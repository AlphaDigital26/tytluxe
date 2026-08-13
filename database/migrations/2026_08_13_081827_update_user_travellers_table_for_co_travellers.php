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
        Schema::table('user_travellers', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('first_name')->after('type');
            $table->string('last_name')->after('first_name')->nullable();
            $table->string('relationship')->nullable()->after('last_name');
            $table->string('meal_preference')->nullable();
            $table->string('train_berth_preference')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('frequent_flyer_airline')->nullable();
            $table->string('frequent_flyer_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_travellers', function (Blueprint $table) {
            $table->string('name')->after('type')->nullable();
            $table->dropColumn([
                'first_name',
                'last_name',
                'relationship',
                'meal_preference',
                'train_berth_preference',
                'phone',
                'email',
                'frequent_flyer_airline',
                'frequent_flyer_number'
            ]);
        });
    }
};
