<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('current_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('requested_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('requested_bed_id')->nullable()->constrained('beds')->nullOnDelete();
            $table->string('status')->default('pending_manager_approval');
            $table->text('reason')->nullable();
            $table->text('manager_note')->nullable();
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['requested_room_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_change_requests');
    }
};

