<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('referral_agents', function (Blueprint $table): void {
            if (!Schema::hasColumn('referral_agents', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete()
                    ->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('referral_agents', function (Blueprint $table): void {
            if (Schema::hasColumn('referral_agents', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};

