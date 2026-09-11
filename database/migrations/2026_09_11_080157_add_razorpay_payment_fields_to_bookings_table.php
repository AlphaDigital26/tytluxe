<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $statuses = ['pending_payment', 'payment_failed', 'confirmed', 'refunded', 'cancelled', 'failed_needs_review'];

    protected array $previousStatuses = ['pending_payment', 'confirmed', 'cancelled', 'failed_needs_review'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Exact roomTravellerInfo payload submitBooking() already builds
            // for TripJack's Book API, persisted at guest-submission time so
            // the post-payment Book call — which may run from a Razorpay
            // webhook with no PHP session — can reconstruct it from the DB
            // alone rather than from session state.
            $table->json('tripjack_room_traveller_payload')->nullable()->after('tripjack_option_id');
        });

        // pending_payment now specifically means "awaiting Razorpay payment"
        // (payment happens before Book, not after); payment_failed and
        // refunded are new terminal states for the Razorpay flow.
        $this->setStatusEnum($this->statuses);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->setStatusEnum($this->previousStatuses);

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('tripjack_room_traveller_payload');
        });
    }

    /**
     * Laravel's schema builder can't rewrite an enum's value list via
     * change() without doctrine/dbal handling it awkwardly. MySQL supports a
     * raw MODIFY in place; SQLite (used by the test suite) enforces enums via
     * a CHECK constraint that can only be changed by recreating the column —
     * safe here since this only ever runs against fresh test databases.
     */
    protected function setStatusEnum(array $statuses): void
    {
        $quoted = collect($statuses)->map(fn ($s) => "'{$s}'")->implode(', ');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY status ENUM({$quoted}) NOT NULL DEFAULT 'pending_payment'");

            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
        Schema::table('bookings', function (Blueprint $table) use ($statuses) {
            $table->enum('status', $statuses)->default('pending_payment')->index();
        });
    }
};
