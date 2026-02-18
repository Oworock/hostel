<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('allocations') || !Schema::hasColumn('allocations', 'student_id')) {
            return;
        }

        $this->dropAllocationStudentForeignKey();

        if (Schema::hasTable('students')) {
            $allocations = DB::table('allocations')
                ->join('students', 'students.id', '=', 'allocations.student_id')
                ->select('allocations.id', 'students.email', 'students.name')
                ->get();

            foreach ($allocations as $allocation) {
                $userId = DB::table('users')
                    ->where('role', 'student')
                    ->where('email', $allocation->email)
                    ->value('id');

                if (!$userId && !empty($allocation->name)) {
                    $userId = DB::table('users')
                        ->where('role', 'student')
                        ->where('name', $allocation->name)
                        ->value('id');
                }

                if ($userId) {
                    DB::table('allocations')
                        ->where('id', $allocation->id)
                        ->update(['student_id' => $userId]);
                }
            }
        }

        DB::table('allocations')
            ->whereNotIn('student_id', function ($query) {
                $query->select('id')->from('users')->where('role', 'student');
            })
            ->delete();

        Schema::table('allocations', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('allocations') || !Schema::hasColumn('allocations', 'student_id')) {
            return;
        }

        $this->dropAllocationStudentForeignKey();

        if (Schema::hasTable('students')) {
            Schema::table('allocations', function (Blueprint $table) {
                $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            });
        }
    }

    private function dropAllocationStudentForeignKey(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            try {
                $table->dropForeign(['student_id']);
            } catch (\Throwable $e) {
                // Ignore if FK does not exist.
            }
        });
    }
};
