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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('vertical', ['hotel', 'flight', 'cruise', 'staycation', 'package', 'general']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('name');
            $table->string('phone', 20)->index();
            $table->string('email')->index();
            $table->date('travel_date_from')->nullable();
            $table->date('travel_date_to')->nullable();
            $table->unsignedTinyInteger('pax_adults')->default(1);
            $table->unsignedTinyInteger('pax_children')->default(0);
            $table->string('notes', 500)->nullable();
            $table->text('admin_notes')->nullable();
            $table->enum('status', ['new', 'contacted', 'quoted', 'converted', 'closed'])->default('new')->index();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('source', ['web', 'whatsapp', 'phone'])->default('web');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['vertical', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
