<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * pending_confirmation: payment captured and Book accepted, but TripJack's
 * Booking Details still reads PENDING/IN_PROGRESS (or couldn't be read).
 * This used to be mapped straight to 'confirmed', telling the guest the room
 * was booked before the hotel had actually confirmed it.
 */
return new class extends Migration
{
    protected array $statuses = ['pending_payment', 'payment_failed', 'pending_confirmation', 'confirmed', 'refunded', 'cancelled', 'failed_needs_review', 'on_hold', 'hold_expired'];

    protected array $previousStatuses = ['pending_payment', 'payment_failed', 'confirmed', 'refunded', 'cancelled', 'failed_needs_review', 'on_hold', 'hold_expired'];

    public function up(): void
    {
        $this->setStatusEnum($this->statuses);
    }

    public function down(): void
    {
        DB::table('bookings')->where('status', 'pending_confirmation')->update(['status' => 'confirmed']);
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
