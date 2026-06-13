<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::first();

        if (!$hotel) {
            $this->command->error('No hotel found. Run HotelSeeder first.');
            return;
        }

        // Clear existing rooms for this hotel
        Room::where('hotel_id', $hotel->id)->delete();

        $roomConfigs = [
            // [type, floor, number, price, capacity]
            // Floor 1 — Standard rooms
            ['standard', 1, '101', 80.00,  2],
            ['standard', 1, '102', 80.00,  2],
            ['standard', 1, '103', 80.00,  2],
            ['standard', 1, '104', 80.00,  2],
            ['standard', 1, '105', 80.00,  2],
            // Floor 1 — Deluxe
            ['deluxe',   1, '106', 150.00, 2],
            ['deluxe',   1, '107', 150.00, 2],

            // Floor 2 — Standard & Deluxe
            ['standard', 2, '201', 90.00,  2],
            ['standard', 2, '202', 90.00,  2],
            ['deluxe',   2, '203', 160.00, 2],
            ['deluxe',   2, '204', 160.00, 2],
            // Floor 2 — Family Suites
            ['family_suite', 2, '205', 220.00, 4],
            ['family_suite', 2, '206', 220.00, 4],

            // Floor 3 — Business Suites
            ['business_suite', 3, '301', 200.00, 2],
            ['business_suite', 3, '302', 200.00, 2],
            ['business_suite', 3, '303', 200.00, 2],
            // Floor 3 — Family Suites
            ['family_suite', 3, '304', 240.00, 5],
            ['family_suite', 3, '305', 240.00, 5],
        ];

        $amenitySets = [
            'standard'       => ['WiFi', 'TV', 'AC', 'Hot Water'],
            'deluxe'         => ['WiFi', 'TV', 'AC', 'Hot Water', 'Mini Bar', 'Safe'],
            'family_suite'   => ['WiFi', 'TV', 'AC', 'Hot Water', 'Mini Bar', 'Safe', 'Balcony', 'Kitchenette'],
            'business_suite' => ['WiFi', 'TV', 'AC', 'Hot Water', 'Mini Bar', 'Safe', 'Work Desk', 'Lounge Area', 'Room Service'],
        ];

        foreach ($roomConfigs as [$type, $floor, $number, $price, $capacity]) {
            Room::create([
                'hotel_id'        => $hotel->id,
                'number'          => $number,
                'type'            => $type,
                'status'          => 'available',
                'floor'           => $floor,
                'capacity'        => $capacity,
                'price_per_night' => $price,
                'amenities'       => $amenitySets[$type],
                'is_active'       => true,
            ]);
        }

        $this->command->info('✅ Created ' . count($roomConfigs) . " rooms for {$hotel->name}.");
    }
}