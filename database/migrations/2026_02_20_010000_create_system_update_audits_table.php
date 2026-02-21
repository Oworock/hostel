<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_update_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->string('package_name')->nullable();
            $table->string('package_path')->nullable();
            $table->string('version', 40)->nullable();
            $table->unsignedInteger('files_total')->default(0);
            $table->unsignedInteger('files_applied')->default(0);
            $table->json('details')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_update_audits');
    }
};

