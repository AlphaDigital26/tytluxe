<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            // Lets the search bar match "Maharashtra" against Mumbai/Pune/etc,
            // not just an exact city name — see the destination search
            // suggestion feature in FrontendController.
            $table->string('state')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
