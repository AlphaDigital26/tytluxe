<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_exclusions', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('package_inclusions', function (Blueprint $table) {
            $table->text('label')->change();
        });
    }

    public function down(): void
    {
        Schema::table('package_exclusions', function (Blueprint $table) {
            $table->string('name')->change();
        });

        Schema::table('package_inclusions', function (Blueprint $table) {
            $table->string('label')->change();
        });
    }
};
