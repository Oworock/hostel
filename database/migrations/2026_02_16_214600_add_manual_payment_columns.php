<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'is_manual')) {
                $table->boolean('is_manual')->default(false)->after('notes');
            }

            if (!Schema::hasColumn('payments', 'created_by_admin_id')) {
                $table->foreignId('created_by_admin_id')->nullable()->after('is_manual')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'created_by_admin_id')) {
                $table->dropConstrainedForeignId('created_by_admin_id');
            }

            if (Schema::hasColumn('payments', 'is_manual')) {
                $table->dropColumn('is_manual');
            }
        });
    }
};
