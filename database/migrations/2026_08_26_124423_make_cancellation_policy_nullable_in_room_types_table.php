<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->enum('cancellation_policy', ['free_cancellation', 'non_refundable', 'partial'])
                  ->nullable()
                  ->default(null)
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->enum('cancellation_policy', ['free_cancellation', 'non_refundable', 'partial'])
                  ->nullable(false)
                  ->change();
        });
    }
};
