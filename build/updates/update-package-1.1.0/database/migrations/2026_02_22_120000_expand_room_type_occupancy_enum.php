<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE rooms MODIFY COLUMN type ENUM('single','double','triple','quad','quint','sext','sept','oct') NOT NULL DEFAULT 'double'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE rooms MODIFY COLUMN type ENUM('single','double','triple','quad') NOT NULL DEFAULT 'double'");
    }
};

