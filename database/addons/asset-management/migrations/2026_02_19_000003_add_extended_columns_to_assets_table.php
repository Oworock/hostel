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
            if (!Schema::hasColumn('assets', 'asset_number')) {
                $table->string('asset_number')->nullable()->after('asset_code')->index();
            }
            if (!Schema::hasColumn('assets', 'brand')) {
                $table->string('brand')->nullable()->after('category');
            }
            if (!Schema::hasColumn('assets', 'model')) {
                $table->string('model')->nullable()->after('brand');
            }
            if (!Schema::hasColumn('assets', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('model')->index();
            }
            if (!Schema::hasColumn('assets', 'manufacturer')) {
                $table->string('manufacturer')->nullable()->after('serial_number');
            }
            if (!Schema::hasColumn('assets', 'supplier')) {
                $table->string('supplier')->nullable()->after('manufacturer');
            }
            if (!Schema::hasColumn('assets', 'invoice_reference')) {
                $table->string('invoice_reference')->nullable()->after('supplier');
            }
            if (!Schema::hasColumn('assets', 'location')) {
                $table->string('location')->nullable()->after('invoice_reference');
            }
            if (!Schema::hasColumn('assets', 'image_path')) {
                $table->string('image_path')->nullable()->after('location');
            }
            if (!Schema::hasColumn('assets', 'warranty_expiry_date')) {
                $table->date('warranty_expiry_date')->nullable()->after('purchase_date');
            }
            if (!Schema::hasColumn('assets', 'acquisition_cost')) {
                $table->decimal('acquisition_cost', 12, 2)->nullable()->after('warranty_expiry_date');
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
            if (Schema::hasColumn('assets', 'acquisition_cost')) {
                $table->dropColumn('acquisition_cost');
            }
            if (Schema::hasColumn('assets', 'maintenance_schedule')) {
                $table->dropColumn('maintenance_schedule');
            }
            if (Schema::hasColumn('assets', 'warranty_expiry_date')) {
                $table->dropColumn('warranty_expiry_date');
            }
            if (Schema::hasColumn('assets', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('assets', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('assets', 'manufacturer')) {
                $table->dropColumn('manufacturer');
            }
            if (Schema::hasColumn('assets', 'invoice_reference')) {
                $table->dropColumn('invoice_reference');
            }
            if (Schema::hasColumn('assets', 'supplier')) {
                $table->dropColumn('supplier');
            }
            if (Schema::hasColumn('assets', 'serial_number')) {
                $table->dropColumn('serial_number');
            }
            if (Schema::hasColumn('assets', 'model')) {
                $table->dropColumn('model');
            }
            if (Schema::hasColumn('assets', 'brand')) {
                $table->dropColumn('brand');
            }
            if (Schema::hasColumn('assets', 'asset_number')) {
                $table->dropColumn('asset_number');
            }
        });
    }
};
