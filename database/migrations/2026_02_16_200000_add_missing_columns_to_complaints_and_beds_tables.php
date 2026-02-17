<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('complaints')) {
            Schema::table('complaints', function (Blueprint $table) {
                if (!Schema::hasColumn('complaints', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                }
                if (!Schema::hasColumn('complaints', 'subject')) {
                    $table->string('subject')->nullable();
                }
                if (!Schema::hasColumn('complaints', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('complaints', 'status')) {
                    $table->string('status')->default('open')->index();
                }
                if (!Schema::hasColumn('complaints', 'response')) {
                    $table->text('response')->nullable();
                }
                if (!Schema::hasColumn('complaints', 'assigned_to')) {
                    $table->unsignedBigInteger('assigned_to')->nullable()->index();
                }
                if (!Schema::hasColumn('complaints', 'booking_id')) {
                    $table->unsignedBigInteger('booking_id')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('beds')) {
            Schema::table('beds', function (Blueprint $table) {
                if (!Schema::hasColumn('beds', 'is_approved')) {
                    $table->boolean('is_approved')->default(false)->index();
                }
                if (!Schema::hasColumn('beds', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->index();
                }
                if (!Schema::hasColumn('beds', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }
                if (!Schema::hasColumn('beds', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->index();
                }
            });

            DB::table('beds')
                ->whereNull('is_approved')
                ->orWhere('is_approved', 0)
                ->update(['is_approved' => 1]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('complaints')) {
            Schema::table('complaints', function (Blueprint $table) {
                foreach (['user_id', 'subject', 'description', 'status', 'response', 'assigned_to', 'booking_id'] as $column) {
                    if (Schema::hasColumn('complaints', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('beds')) {
            Schema::table('beds', function (Blueprint $table) {
                foreach (['is_approved', 'approved_by', 'approved_at', 'created_by'] as $column) {
                    if (Schema::hasColumn('beds', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
