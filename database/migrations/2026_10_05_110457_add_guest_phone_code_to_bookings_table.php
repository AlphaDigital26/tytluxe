<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * guest_phone_code: the guest's own country dialling code (digits, e.g.
     * "91", "44") — Book's deliveryInfo.code was hard-coded to +91, so a
     * foreign guest's number went out with India's code.
     *
     * hotel_confirmation_number: TripJack Booking Details'
     * hotelConfirmationNumber, shown to the guest for check-in.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('guest_phone_code', 6)->nullable()->after('guest_phone');
            $table->string('hotel_confirmation_number', 100)->nullable()->after('tripjack_booking_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['guest_phone_code', 'hotel_confirmation_number']);
        });
    }
};
