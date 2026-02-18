<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_xxxxxx_create_beds_table.php
public function up(): void
{
    Schema::create('beds', function (Blueprint $table) {
        $table->id();
        $table->foreignId('room_id')->constrained()->cascadeOnDelete();
        $table->string('bed_number');
        $table->boolean('is_occupied')->default(false);
        $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
        $table->dateTime('occupied_from')->nullable();
        $table->timestamps();
        $table->unique(['room_id', 'bed_number']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
