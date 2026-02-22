<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('from_hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->foreignId('to_hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiving_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('request_note')->nullable();
            $table->string('status')->default('pending_receiving_manager');
            $table->timestamp('receiving_manager_decided_at')->nullable();
            $table->foreignId('receiving_manager_decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('receiving_manager_note')->nullable();
            $table->timestamp('admin_decided_at')->nullable();
            $table->foreignId('admin_decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('moved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'to_hostel_id']);
            $table->index(['status', 'from_hostel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};
