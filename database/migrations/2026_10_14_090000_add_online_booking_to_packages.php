<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Online booking for group packages (like WanderOn): seats and a peak-date
 * extra per departure, optional sharing prices (Quad/Triple/Double) per
 * package, and pay-a-booking-amount-now-and-the-balance-later on bookings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Off for existing packages until the client checks seats and prices.
            $table->boolean('online_booking')->default(false)->after('booking_amount');
            // [{label: "Triple sharing", price: 18999}, ...] — empty = one price (price_from).
            $table->json('sharing_prices')->nullable()->after('online_booking');
        });

        Schema::table('package_departures', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_seats')->nullable()->after('end_date');
            $table->decimal('extra_price', 10, 2)->default(0)->after('total_seats');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('package_departure_id')->nullable()->after('package_id')->constrained('package_departures')->nullOnDelete();
            $table->string('package_option')->nullable()->after('package_departure_id');
            $table->decimal('package_price_per_person', 10, 2)->nullable()->after('package_option');
            $table->string('package_payment_plan', 10)->nullable()->after('package_price_per_person');
            // Package price still owed before GST (0 once fully paid).
            $table->decimal('package_balance_amount', 10, 2)->nullable()->after('package_payment_plan');
            $table->date('package_balance_due_on')->nullable()->after('package_balance_amount');
            $table->timestamp('package_balance_reminded_at')->nullable()->after('package_balance_due_on');
        });

        // Group tours ask each traveller's age (rooms, activities, insurance).
        Schema::table('booking_travelers', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('booking_travelers', fn (Blueprint $table) => $table->dropColumn('age'));
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_departure_id');
            $table->dropColumn(['package_option', 'package_price_per_person', 'package_payment_plan', 'package_balance_amount', 'package_balance_due_on', 'package_balance_reminded_at']);
        });
        Schema::table('package_departures', fn (Blueprint $table) => $table->dropColumn(['total_seats', 'extra_price']));
        Schema::table('packages', fn (Blueprint $table) => $table->dropColumn(['online_booking', 'sharing_prices']));
    }
};
