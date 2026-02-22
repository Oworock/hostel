<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->string('name');
            $table->string('asset_code')->nullable()->index();
            $table->string('asset_number')->nullable()->index();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->string('manufacturer')->nullable();
            $table->string('supplier')->nullable();
            $table->string('invoice_reference')->nullable();
            $table->string('location')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status')->default('active');
            $table->string('condition')->default('good');
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry_date')->nullable();
            $table->decimal('acquisition_cost', 12, 2)->nullable();
            $table->string('maintenance_schedule')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['hostel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
