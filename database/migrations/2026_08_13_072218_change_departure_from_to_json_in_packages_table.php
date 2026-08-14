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
        // First get the existing data so we can update it BEFORE altering the column
        $packages = DB::table('packages')->get();

        // Migrate the data to be valid JSON strings so MySQL accepts the column change
        foreach ($packages as $package) {
            $value = $package->departure_from;
            // If it's empty, we can just set it to an empty array
            if (empty($value) || $value === '[]' || $value === 'null') {
                $newJson = json_encode([]);
            } else {
                // Check if it's already a json array
                json_decode($value);
                if (json_last_error() === JSON_ERROR_NONE && is_array(json_decode($value))) {
                    $newJson = $value; // already json
                } else {
                    // It's a plain string, put it in an array
                    $newJson = json_encode([$value]);
                }
            }
            
            DB::table('packages')->where('id', $package->id)->update([
                'departure_from' => $newJson
            ]);
        }

        Schema::table('packages', function (Blueprint $table) {
            $table->json('departure_from')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $packages = DB::table('packages')->get();

        foreach ($packages as $package) {
            $val = json_decode($package->departure_from, true);
            $newStr = (is_array($val) && count($val) > 0) ? $val[0] : '';
            
            DB::table('packages')->where('id', $package->id)->update([
                'departure_from' => $newStr
            ]);
        }

        Schema::table('packages', function (Blueprint $table) {
            $table->string('departure_from')->nullable()->change();
        });
    }
};
