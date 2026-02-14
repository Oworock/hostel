<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing users
        DB::table('users')->truncate();

        // Create Admin User
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@hostel.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '+2348012345678',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Manager User
        DB::table('users')->insert([
            'name' => 'Manager User',
            'email' => 'manager@hostel.com',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'phone' => '+2348087654321',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Student User
        DB::table('users')->insert([
            'name' => 'Student User',
            'email' => 'student@hostel.com',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'phone' => '+2348090000000',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✅ Users created successfully!\n";
    }
}
