<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * manual_refund_due_at: set when an automatic refund failed or couldn't be
 * worked out, so the booking shows under "Needs attention" until staff
 * refund the guest themselves and record it ("Record manual refund").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('manual_refund_due_at')->nullable()->after('admin_note');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('manual_refund_due_at');
        });
    }
};
