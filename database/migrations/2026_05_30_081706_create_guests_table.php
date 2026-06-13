<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->enum('id_type', ['passport', 'national_id', 'drivers_license'])->default('national_id');
            $table->string('id_number');
            $table->string('nationality')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'email']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};