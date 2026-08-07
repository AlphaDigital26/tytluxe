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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable()->unique();
            $table->string('password');
            $table->string('role')->default('Operations');
            $table->string('status')->default('Active');
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropForeign(['assigned_agent_id']);
            $table->foreign('assigned_agent_id')->references('id')->on('admins')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropForeign(['assigned_agent_id']);
            $table->foreign('assigned_agent_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::dropIfExists('admins');
    }
};
