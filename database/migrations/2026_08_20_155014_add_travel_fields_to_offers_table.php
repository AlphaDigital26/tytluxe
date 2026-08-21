<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            // Destination label shown on the card, e.g. "Maldives", "Dubai ↔ Mumbai"
            $table->string('destination')->nullable()->after('subtitle');

            // Duration label shown on the card, e.g. "5 Nights / 6 Days"
            $table->string('duration')->nullable()->after('destination');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['destination', 'duration']);
        });
    }
};
