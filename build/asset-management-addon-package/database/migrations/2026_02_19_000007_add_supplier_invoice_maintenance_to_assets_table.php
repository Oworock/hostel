<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assets')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'supplier')) {
                $table->string('supplier')->nullable()->after('manufacturer');
            }
            if (!Schema::hasColumn('assets', 'invoice_reference')) {
                $table->string('invoice_reference')->nullable()->after('supplier');
            }
            if (!Schema::hasColumn('assets', 'maintenance_schedule')) {
                $table->string('maintenance_schedule')->nullable()->after('acquisition_cost');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('assets')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'maintenance_schedule')) {
                $table->dropColumn('maintenance_schedule');
            }
            if (Schema::hasColumn('assets', 'invoice_reference')) {
                $table->dropColumn('invoice_reference');
            }
            if (Schema::hasColumn('assets', 'supplier')) {
                $table->dropColumn('supplier');
            }
        });
    }
};
