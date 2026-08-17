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
        Schema::create('user_travellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('self');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('relationship')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('passport_issuing_country')->nullable();
            $table->string('meal_preference')->nullable();
            $table->string('train_berth_preference')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('frequent_flyer_airline')->nullable();
            $table->string('frequent_flyer_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_travellers');
    }
};
