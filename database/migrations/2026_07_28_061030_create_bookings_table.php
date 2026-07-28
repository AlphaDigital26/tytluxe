<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_email')->nullable()->index();
            $table->string('guest_phone', 20)->nullable();
            $table->enum('vertical', ['hotel', 'flight'])->index();
            $table->foreignId('hotel_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('tripjack_booking_id', 100)->nullable()->unique();
            $table->string('tripjack_hold_id', 100)->nullable();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->string('flight_route')->nullable();
            $table->unsignedTinyInteger('pax_adults')->default(1);
            $table->unsignedTinyInteger('pax_children')->default(0);
            $table->string('lead_guest_name');
            $table->string('special_requests', 500)->nullable();
            $table->decimal('base_amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->char('currency', 3)->default('INR');
            $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending_payment', 'confirmed', 'cancelled', 'failed_needs_review'])->default('pending_payment')->index();
            $table->string('cancellation_reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
