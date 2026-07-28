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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('razorpay_order_id', 100)->unique();
            $table->string('razorpay_payment_id', 100)->nullable()->unique();
            $table->string('razorpay_signature')->nullable();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('INR');
            $table->enum('status', ['created', 'authorized', 'captured', 'failed', 'refunded', 'partially_refunded'])->default('created')->index();
            $table->string('method', 50)->nullable();
            $table->json('raw_response')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
