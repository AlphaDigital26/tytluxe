<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Default 'booking' preserves every existing payment's meaning
            // unchanged — 'flight_ssr' is the only new branch read anywhere
            // (FrontendController::confirmBookingAfterPayment), so this is
            // purely additive to the shared Razorpay callback/webhook path.
            $table->string('purpose')->default('booking')->after('status');
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Snapshot of Fetch SSR + Fetch Seat responses, cached so the
            // extras page can re-render (e.g. after a validation error)
            // without hammering TripJack on every request.
            $table->json('flight_ssr_options_cache')->nullable()->after('flight_segments_payload');
            // The guest's confirmed selection at Add-SSR payment time —
            // read back by FlightAncillaryService once Razorpay captures.
            $table->json('flight_ssr_pending_selection')->nullable()->after('flight_ssr_options_cache');
            // Amendment IDs returned by Add SSR, accumulated across every
            // successful addition (a booking can add extras more than once).
            $table->json('flight_ssr_amendment_ids')->nullable()->after('flight_ssr_pending_selection');
            // Confirmed seat/meal/baggage details once each amendment
            // resolves to SUCCESS, keyed by segment ID.
            $table->json('flight_ssr_confirmed')->nullable()->after('flight_ssr_amendment_ids');
            $table->decimal('flight_ssr_amount_paid', 10, 2)->default(0)->after('flight_ssr_confirmed');
            $table->string('flight_ssr_status')->nullable()->after('flight_ssr_amount_paid'); // pending|confirmed|failed
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'flight_ssr_options_cache',
                'flight_ssr_pending_selection',
                'flight_ssr_amendment_ids',
                'flight_ssr_confirmed',
                'flight_ssr_amount_paid',
                'flight_ssr_status',
            ]);
        });
    }
};
