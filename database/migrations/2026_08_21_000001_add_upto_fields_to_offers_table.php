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
        Schema::table('offers', function (Blueprint $table) {
            $table->boolean('is_upto')->default(false)->after('discount_value');
            $table->decimal('min_order_value', 10, 2)->nullable()->after('is_upto');
            $table->json('upto_options')->nullable()->after('min_order_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['is_upto', 'min_order_value', 'upto_options']);
        });
    }
};
