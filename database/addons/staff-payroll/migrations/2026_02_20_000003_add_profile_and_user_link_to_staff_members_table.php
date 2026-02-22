<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_members', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_members', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('staff_members', 'source_role')) {
                $table->string('source_role')->nullable()->after('job_title');
            }
            if (!Schema::hasColumn('staff_members', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('address');
            }
            if (!Schema::hasColumn('staff_members', 'id_card_path')) {
                $table->string('id_card_path')->nullable()->after('profile_image');
            }
        });

        try {
            Schema::table('staff_members', function (Blueprint $table) {
                $table->unique('user_id');
            });
        } catch (\Throwable $e) {
            // Ignore if index already exists.
        }

    }

    public function down(): void
    {
        try {
            Schema::table('staff_members', function (Blueprint $table) {
                $table->dropUnique(['user_id']);
            });
        } catch (\Throwable $e) {
            // Ignore when index does not exist.
        }

        Schema::table('staff_members', function (Blueprint $table) {
            if (Schema::hasColumn('staff_members', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('staff_members', 'source_role')) {
                $table->dropColumn('source_role');
            }
            if (Schema::hasColumn('staff_members', 'profile_image')) {
                $table->dropColumn('profile_image');
            }
            if (Schema::hasColumn('staff_members', 'id_card_path')) {
                $table->dropColumn('id_card_path');
            }
        });
    }
};
