<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id');
            $table->foreign('asset_id', 'ai_a_fk')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreignId('hostel_id');
            $table->foreign('hostel_id', 'ai_h_fk')->references('id')->on('hostels')->cascadeOnDelete();
            $table->foreignId('reported_by');
            $table->foreign('reported_by', 'ai_rb_fk')->references('id')->on('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->index(['hostel_id', 'status'], 'ai_hs_idx');
            $table->index(['asset_id', 'status'], 'ai_as_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_issues');
    }
};
