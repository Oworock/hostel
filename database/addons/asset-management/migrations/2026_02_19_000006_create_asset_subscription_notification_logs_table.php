<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_subscription_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_subscription_id')->constrained('asset_subscriptions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('days_remaining');
            $table->timestamp('notified_at');
            $table->timestamps();

            $table->unique(['asset_subscription_id', 'user_id', 'days_remaining'], 'asset_sub_notification_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_subscription_notification_logs');
    }
};
