<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Flights' Hold (Without Payment) mode is a genuinely different, longer-
 * lived state than hotels' transient ON_HOLD (which resolves synchronously
 * within the same request since payment is already captured by then) — this
 * needs its own persisted status so the confirmation page can show "Hold —
 * confirm before HH:MM" and gate the Confirm-Book action.
 */
return new class extends Migration
{
    protected array $statuses = ['pending_payment', 'payment_failed', 'confirmed', 'refunded', 'cancelled', 'failed_needs_review', 'on_hold', 'hold_expired'];

    protected array $previousStatuses = ['pending_payment', 'payment_failed', 'confirmed', 'refunded', 'cancelled', 'failed_needs_review'];

    public function up(): void
    {
        $this->setStatusEnum($this->statuses);
    }

    public function down(): void
    {
        $this->setStatusEnum($this->previousStatuses);
    }

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
