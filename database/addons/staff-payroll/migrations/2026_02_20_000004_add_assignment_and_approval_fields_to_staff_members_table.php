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
            if (!Schema::hasColumn('staff_members', 'registered_via_link')) {
                $table->boolean('registered_via_link')->default(false)->after('source_role');
            }
            if (!Schema::hasColumn('staff_members', 'is_general_staff')) {
                $table->boolean('is_general_staff')->default(true)->after('status');
            }
            if (!Schema::hasColumn('staff_members', 'assigned_hostel_id')) {
                $table->unsignedBigInteger('assigned_hostel_id')->nullable()->after('is_general_staff');
            }
            if (!Schema::hasColumn('staff_members', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('assigned_hostel_id');
            }
            if (!Schema::hasColumn('staff_members', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('staff_members')) {
            return;
        }

        Schema::table('staff_members', function (Blueprint $table): void {
            if (Schema::hasColumn('staff_members', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('staff_members', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('staff_members', 'assigned_hostel_id')) {
                $table->dropColumn('assigned_hostel_id');
            }
            if (Schema::hasColumn('staff_members', 'is_general_staff')) {
                $table->dropColumn('is_general_staff');
            }
            if (Schema::hasColumn('staff_members', 'registered_via_link')) {
                $table->dropColumn('registered_via_link');
            }
        });
    }
};

