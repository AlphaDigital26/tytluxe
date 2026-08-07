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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->change();
            $table->string('status')->default('Active')->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('updated_at');
        });

        // We can optionally drop is_active here, but since SQLite might complain in some older versions,
        // and we might need it for a rollback, we'll just drop it.
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->dropColumn(['status', 'last_login_at']);
        });
    }
};
