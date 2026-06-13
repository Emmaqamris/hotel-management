<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Guest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Hotel
        $hotel = Hotel::firstOrCreate(
            ['email' => 'info@grandhorizon.com'],
            [
                'name'      => 'Grand Horizon Hotel',
                'address'   => '123 Sunset Boulevard',
                'city'      => 'Mbeya',
                'country'   => 'TZ',
                'phone'     => '+255 700 000 000',
                'stars'     => 4,
                'settings'  => json_encode(['tax_rate' => 18]),
            ]
        );

        $this->command->info('✅ Hotel seeded: ' . $hotel->name);

        // 2. Create Employees
        Employee::updateOrCreate(
            ['email' => 'admin@hotel.com'],
            [
                'hotel_id' => $hotel->id,
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        Employee::updateOrCreate(
            ['email' => 'manager@hotel.com'],
            [
                'hotel_id' => $hotel->id,
                'name'     => 'Jane Manager',
                'password' => Hash::make('password'),
                'role'     => 'manager',
            ]
        );

        Employee::updateOrCreate(
            ['email' => 'reception@hotel.com'],
            [
                'hotel_id' => $hotel->id,
                'name'     => 'John Receptionist',
                'password' => Hash::make('password'),
                'role'     => 'receptionist',
            ]
        );

        Employee::updateOrCreate(
            ['email' => 'housekeeper@hotel.com'],
            [
                'hotel_id' => $hotel->id,
                'name'     => 'Mary Housekeeper',
                'password' => Hash::make('password'),
                'role'     => 'housekeeper',
            ]
        );

        $this->command->info('✅ Employees seeded.');

        // 3. Create Rooms
        Room::where('hotel_id', $hotel->id)->delete();

        $roomTypes = ['standard', 'deluxe', 'family_suite', 'business_suite'];
        $prices = ['standard' => 80, 'deluxe' => 150, 'family_suite' => 220, 'business_suite' => 200];

        $roomNum = 100;
        foreach ([1, 2, 3] as $floor) {
            foreach ($roomTypes as $type) {
                for ($i = 0; $i < 3; $i++) {
                    $roomNum++;
                    Room::create([
                        'hotel_id'        => $hotel->id,
                        'number'          => (string)$roomNum,
                        'type'            => $type,
                        'status'          => 'available',
                        'floor'           => $floor,
                        'capacity'        => ($type === 'family_suite') ? 4 : 2,
                        'price_per_night' => $prices[$type] + ($floor - 1) * 10,
                        'amenities'       => json_encode(['WiFi', 'TV', 'AC']),
                    ]);
                }
            }
        }

        $this->command->info('✅ ' . Room::count() . ' rooms seeded.');

        // 4. Create Sample Guests
        Guest::where('hotel_id', $hotel->id)->delete();

        $guestData = [
            ['first_name' => 'Alice', 'last_name' => 'Johnson', 'phone' => '+255 700 111 111', 'email' => 'alice@example.com', 'id_type' => 'passport', 'id_number' => 'P12345678'],
            ['first_name' => 'Bob', 'last_name' => 'Smith', 'phone' => '+255 700 222 222', 'email' => 'bob@example.com', 'id_type' => 'national_id', 'id_number' => 'N87654321'],
            ['first_name' => 'Claire', 'last_name' => 'Dupont', 'phone' => '+255 700 333 333', 'email' => 'claire@example.com', 'id_type' => 'passport', 'id_number' => 'F11223344'],
        ];

        foreach ($guestData as $data) {
            Guest::create(array_merge($data, ['hotel_id' => $hotel->id]));
        }

        $this->command->info('✅ Guests seeded.');

        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('Login → admin@hotel.com / password');
    }
}