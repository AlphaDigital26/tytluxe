<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First change to string/text so it accepts non-enum values and MySQL doesn't validate JSON yet
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('for', 1000)->nullable()->change();
        });

        // Migrate the data to be valid JSON strings
        $destinations = DB::table('destinations')->get();
        foreach ($destinations as $dest) {
            $forVal = $dest->for;
            if (is_string($forVal) && !str_starts_with($forVal, '[')) {
                DB::table('destinations')
                    ->where('id', $dest->id)
                    ->update(['for' => json_encode([$forVal])]);
            }
        }

        // Now that the data is valid JSON, change the column type to JSON
        Schema::table('destinations', function (Blueprint $table) {
            $table->json('for')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            //
        });
    }
};
