<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('popup_announcements', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->enum('target', ['students', 'managers', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('popup_announcement_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('popup_announcement_id')->constrained('popup_announcements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
            $table->unique(['popup_announcement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_announcement_user');
        Schema::dropIfExists('popup_announcements');
    }
};

