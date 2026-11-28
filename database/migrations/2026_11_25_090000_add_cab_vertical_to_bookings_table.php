<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cab bookings (TripJack Cabs API): a new "cab" booking type, the pickup
 * time (for sorting and "today's pickups" in the admin), and one JSON column
 * holding the trip, the chosen quote and TripJack's latest booking details.
 */
return new class extends Migration
{
    protected array $verticals = ['hotel', 'flight', 'package', 'cab'];

    protected array $previousVerticals = ['hotel', 'flight', 'package'];

    public function up(): void
    {
        $this->setVerticalEnum($this->verticals);

        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('cab_pickup_at')->nullable()->index();
            $table->json('cab_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['cab_pickup_at']);
            $table->dropColumn(['cab_pickup_at', 'cab_details']);
        });

        DB::table('bookings')->where('vertical', 'cab')->delete();
        $this->setVerticalEnum($this->previousVerticals);
    }

    protected function setVerticalEnum(array $verticals): void
    {
        $quoted = collect($verticals)->map(fn ($v) => "'{$v}'")->implode(', ');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY vertical ENUM({$quoted}) NOT NULL");

            return;
        }

        // SQLite (tests): keep each row's value while the column is rebuilt.
        Schema::table('bookings', fn (Blueprint $table) => $table->string('vertical_tmp')->nullable());
        DB::table('bookings')->update(['vertical_tmp' => DB::raw('vertical')]);
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['vertical']);
            $table->dropColumn('vertical');
        });
        Schema::table('bookings', fn (Blueprint $table) => $table->enum('vertical', $verticals)->default('hotel')->index());
        DB::table('bookings')->update(['vertical' => DB::raw('vertical_tmp')]);
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn('vertical_tmp'));
    }
};
