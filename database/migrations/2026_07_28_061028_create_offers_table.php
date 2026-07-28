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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 8, 2);
            $table->string('promo_code', 50)->nullable()->unique();
            $table->enum('applies_to_vertical', ['hotel', 'flight', 'cruise', 'staycation', 'package', 'all'])->index();
            $table->unsignedBigInteger('applies_to_reference_id')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['valid_from', 'valid_to']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
