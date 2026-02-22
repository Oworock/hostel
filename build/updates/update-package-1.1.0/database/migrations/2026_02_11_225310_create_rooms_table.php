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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('room_number');
            $table->enum('type', ['single', 'double', 'triple', 'quad', 'quint', 'sext', 'sept', 'oct'])->default('double');
            $table->integer('capacity')->default(2);
            $table->decimal('price_per_month', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->unique(['hostel_id', 'room_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
