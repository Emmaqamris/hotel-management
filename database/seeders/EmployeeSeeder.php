<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Hotel;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Get first hotel (must exist before running this seeder)
        $hotel = Hotel::first();

        if (!$hotel) {
            throw new \Exception('No hotel found. Run HotelSeeder first.');
        }

        // Admin user
        Employee::updateOrCreate(
            ['email' => 'admin@hotel.com'],
            [
                'hotel_id'   => $hotel->id,
                'name'       => 'Admin User',
                'password'   => Hash::make('password'),
                'role'       => 'admin',
                'hire_date'  => now(),
            ]
        );

        // Other employees
        Employee::factory()->count(5)->create([
            'hotel_id' => $hotel->id,
        ]);
    }
}