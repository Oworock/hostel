<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });

        DB::table('users')->select(['id', 'name'])->orderBy('id')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $fullName = trim((string) ($user->name ?? ''));
                if ($fullName === '') {
                    continue;
                }

                $parts = preg_split('/\s+/', $fullName) ?: [];
                $firstName = (string) ($parts[0] ?? '');
                $lastName = count($parts) > 1
                    ? trim(implode(' ', array_slice($parts, 1)))
                    : $firstName;

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'first_name' => $firstName !== '' ? $firstName : null,
                        'last_name' => $lastName !== '' ? $lastName : null,
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('users', 'first_name')) {
                $table->dropColumn('first_name');
            }
        });
    }
};
