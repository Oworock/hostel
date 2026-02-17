<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('bed_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->nullable()->after('semester_id')->constrained('academic_sessions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeignIdFor('academic_sessions');
            $table->dropForeignIdFor('semesters');
        });
    }
};
