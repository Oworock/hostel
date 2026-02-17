<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_hostel_id')->nullable()->constrained('hostels')->nullOnDelete();
            $table->foreignId('requested_hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->string('status')->default('pending_manager_approval');
            $table->text('reason')->nullable();
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            $table->text('manager_note')->nullable();
            $table->foreignId('admin_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admin_approved_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['requested_hostel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_change_requests');
    }
};
