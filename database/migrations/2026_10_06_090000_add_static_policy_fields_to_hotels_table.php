<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * star_rating nullable: TripJack sends no/0 star_rating for unrated
     * properties, which used to be stored as a fake 3-star.
     *
     * checkin_min_age / checkout_till: Static Detail's
     * policies.checkInCheckOut.checkin_min_age and checkout_till, shown to
     * the guest on the detail and review pages.
     *
     * unica_id: see below.
     */
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->unsignedTinyInteger('star_rating')->nullable()->change();
            $table->unsignedTinyInteger('checkin_min_age')->nullable()->after('check_out_time');
            $table->string('checkout_till')->nullable()->after('checkin_min_age');
            // TripJack's content-dedup id: one property under two tjHotelIds
            // shares a unicaId, so the sync can avoid listing it twice.
            $table->string('unica_id', 32)->nullable()->index()->after('tripjack_hotel_id');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropIndex(['unica_id']);
            $table->dropColumn(['checkin_min_age', 'checkout_till', 'unica_id']);
        });
    }
};
