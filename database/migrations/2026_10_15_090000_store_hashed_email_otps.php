<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Verification codes are now stored as a keyed hash (EmailOtp::hashCode),
 * 64 characters instead of the 6-digit code itself. Codes already issued
 * were stored in plain text with a one-year expiry, so they are removed:
 * anyone mid-sign-up just clicks "Resend Code".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_otps')->delete();

        Schema::table('email_otps', function (Blueprint $table) {
            $table->string('otp', 64)->change();
        });
    }

    public function down(): void
    {
        DB::table('email_otps')->delete();

        Schema::table('email_otps', function (Blueprint $table) {
            $table->string('otp', 6)->change();
        });
    }
};
