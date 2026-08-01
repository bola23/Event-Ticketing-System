<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 32);
            $table->string('ticket_number', 32)->nullable()->unique();
            $table->string('status', 20)->default('pending');
            $table->string('ticket_id', 40)->nullable()->unique();
            $table->string('workshop_booking_key', 40)->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('payment_method', 50)->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
