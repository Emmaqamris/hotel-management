<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('number', 10);
            $table->enum('type', ['standard', 'deluxe', 'family_suite', 'business_suite']);
            $table->enum('status', ['available', 'reserved', 'occupied', 'maintenance'])->default('available');
            $table->integer('floor')->default(1);
            $table->integer('capacity')->default(2);
            $table->decimal('price_per_night', 10, 2);
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hotel_id', 'number']);
            $table->index(['hotel_id', 'status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};