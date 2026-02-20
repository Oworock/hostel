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
            if (!Schema::hasColumn('staff_members', 'category')) {
                $table->string('category')->nullable()->after('department');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('staff_members')) {
            return;
        }

        Schema::table('staff_members', function (Blueprint $table): void {
            if (Schema::hasColumn('staff_members', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};

