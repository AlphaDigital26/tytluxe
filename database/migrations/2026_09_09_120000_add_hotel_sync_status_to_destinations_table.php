<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('hotel_sync_status')->nullable()->after('is_active');
            $table->unsignedInteger('hotel_sync_count')->default(0)->after('hotel_sync_status');
            $table->timestamp('hotel_synced_at')->nullable()->after('hotel_sync_count');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['hotel_sync_status', 'hotel_sync_count', 'hotel_synced_at']);
        });
    }
};
