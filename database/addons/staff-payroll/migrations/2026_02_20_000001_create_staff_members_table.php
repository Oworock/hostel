<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone', 32)->nullable();
            $table->string('employee_code')->nullable()->unique();
            $table->string('department')->nullable();
            $table->string('job_title')->nullable();
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->date('joined_on')->nullable();
            $table->string('status')->default('active');
            $table->text('address')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'department']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_members');
    }
};

