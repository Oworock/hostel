<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id');
            $table->foreign('hostel_id', 'as_h_fk')->references('id')->on('hostels')->cascadeOnDelete();
            $table->string('name');
            $table->string('service_type')->default('other');
            $table->string('provider')->nullable();
            $table->string('reference')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expires_at');
            $table->string('billing_cycle')->default('monthly');
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->boolean('auto_renew')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreign('created_by', 'as_cb_fk')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'expires_at'], 'as_se_idx');
            $table->index(['hostel_id', 'expires_at'], 'as_he_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_subscriptions');
    }
};
