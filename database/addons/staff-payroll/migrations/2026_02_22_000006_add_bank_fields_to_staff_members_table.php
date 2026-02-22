<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('staff_members')) {
            return;
        }

        Schema::table('staff_members', function (Blueprint $table): void {
            if (!Schema::hasColumn('staff_members', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('staff_members', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('staff_members', 'bank_account_number')) {
                $table->string('bank_account_number', 64)->nullable()->after('bank_account_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('staff_members')) {
            return;
        }

        Schema::table('staff_members', function (Blueprint $table): void {
            if (Schema::hasColumn('staff_members', 'bank_account_number')) {
                $table->dropColumn('bank_account_number');
            }
            if (Schema::hasColumn('staff_members', 'bank_account_name')) {
                $table->dropColumn('bank_account_name');
            }
            if (Schema::hasColumn('staff_members', 'bank_name')) {
                $table->dropColumn('bank_name');
            }
        });
    }
};

