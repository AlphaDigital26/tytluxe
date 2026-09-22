<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            // Kept separate from `images` (TripJack API sync) because
            // Filament's FileUpload component re-serializes whatever
            // array field it's bound to on every save, and can't
            // recognize the API's external http:// URLs as existing
            // attachments — binding it directly to `images` silently
            // wiped API-synced photos as soon as an admin uploaded a
            // manual photo. Storing manual uploads here and merging the
            // two at display time keeps both sources coexisting.
            $table->json('manual_images')->nullable()->after('images');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('manual_images');
        });
    }
};
