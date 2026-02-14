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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'admission_number')) {
                $table->string('admission_number')->nullable()->after('name');
            }
            if (!Schema::hasColumn('students', 'id_number')) {
                $table->string('id_number')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'admission_number')) {
                $table->dropColumn('admission_number');
            }
            if (Schema::hasColumn('students', 'id_number')) {
                $table->dropColumn('id_number');
            }
        });
    }
};
