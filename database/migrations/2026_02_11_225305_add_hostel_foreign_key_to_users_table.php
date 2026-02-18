<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('hostels')) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'hostel_id')) {
                    return;
                }

                $table->foreign('hostel_id')->references('id')->on('hostels')->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // Ignore if FK already exists on upgraded installations.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'hostel_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropForeign(['hostel_id']);
            } catch (\Throwable $e) {
                // Ignore if FK does not exist.
            }
        });
    }
};
