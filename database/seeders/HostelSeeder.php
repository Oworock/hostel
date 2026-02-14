<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\Bed;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HostelSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@hostel.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Create hostel owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@hostel.com'],
            [
                'name' => 'Hostel Owner',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '1234567890',
                'is_active' => true,
            ]
        );

        // Create a hostel
        $hostel = Hostel::firstOrCreate(
            ['name' => 'Elite Hostel'],
            [
                'description' => 'A premium hostel for students',
                'address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'phone' => '555-1234',
                'email' => 'elite@hostel.com',
                'owner_id' => $owner->id,
                'price_per_month' => 500,
                'total_capacity' => 20,
                'is_active' => true,
            ]
        );

        // Create hostel manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@hostel.com'],
            [
                'name' => 'Hostel Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'phone' => '1234567891',
                'hostel_id' => $hostel->id,
                'is_active' => true,
            ]
        );

        // Create rooms
        $roomTypes = ['single', 'double', 'triple'];
        $capacities = [1, 2, 3];
        $prices = [300, 500, 700];

        foreach ($roomTypes as $index => $type) {
            $roomNum = $index + 1;
            $room = Room::firstOrCreate(
                [
                    'hostel_id' => $hostel->id,
                    'room_number' => "R{$roomNum}0{$roomNum}",
                ],
                [
                    'type' => $type,
                    'capacity' => $capacities[$index],
                    'price_per_month' => $prices[$index],
                    'description' => ucfirst($type) . ' occupancy room',
                    'is_available' => true,
                ]
            );

            // Create beds for each room
            for ($i = 1; $i <= $capacities[$index]; $i++) {
                Bed::firstOrCreate(
                    [
                        'room_id' => $room->id,
                        'bed_number' => "B{$i}",
                    ],
                    [
                        'is_occupied' => false,
                    ]
                );
            }
        }

        // Create sample students
        for ($i = 1; $i <= 5; $i++) {
            $idNum = str_pad($i, 3, '0', STR_PAD_LEFT);
            User::firstOrCreate(
                ['email' => "student{$i}@email.com"],
                [
                    'name' => "Student {$i}",
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'phone' => "555-000{$i}",
                    'id_number' => "STU{$idNum}",
                    'address' => "{$i} Student Lane",
                    'hostel_id' => $hostel->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
