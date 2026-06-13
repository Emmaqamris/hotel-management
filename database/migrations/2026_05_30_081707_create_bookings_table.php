<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->date('checkin_date')->nullable();
            $table->date('checkout_date')->nullable();
            $table->timestamp('actual_checkin')->nullable();
            $table->timestamp('actual_checkout')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])
                  ->default('pending');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->decimal('room_rate', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->text('special_requests')->nullable();
            $table->enum('source', ['walk_in', 'phone', 'online', 'ota'])->default('walk_in');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'status']);
            $table->index(['room_id', 'checkin_date', 'checkout_date']);
            $table->index('booking_number');
            $table->index('guest_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};