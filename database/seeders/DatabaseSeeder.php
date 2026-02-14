<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Student User
        User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'John Student',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'email_verified_at' => now(),
            ]
        );

        // Hostel Manager
        User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Jane Manager',
                'password' => Hash::make('password123'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );

        // Hostel Owner/Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Owner',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
