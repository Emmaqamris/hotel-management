<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoHotelSeeder extends Seeder
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
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Hotel created: ' . $hotel->name);

        // 2. Create Rooms
        Room::where('hotel_id', $hotel->id)->delete();

        $roomData = [
            ['number' => '101', 'type' => 'standard',     'price_per_night' => 85000],
            ['number' => '102', 'type' => 'standard',     'price_per_night' => 85000],
            ['number' => '201', 'type' => 'deluxe',       'price_per_night' => 150000],
            ['number' => '202', 'type' => 'deluxe',       'price_per_night' => 150000],
            ['number' => '301', 'type' => 'family_suite', 'price_per_night' => 250000],
        ];

        foreach ($roomData as $data) {
            Room::create(array_merge($data, [
                'hotel_id'   => $hotel->id,
                'status'     => 'available',
                'floor'      => (int) substr($data['number'], 0, 1),
                'capacity'   => $data['type'] === 'family_suite' ? 5 : 2,
                'amenities'  => json_encode(['WiFi', 'TV', 'AC']),
                'is_active'  => true,
            ]));
        }

        $this->command->info('✅ ' . count($roomData) . ' rooms created.');

        // 3. Create Sample Guests
        Guest::where('hotel_id', $hotel->id)->delete();
        Guest::factory()->count(8)->create(['hotel_id' => $hotel->id]);

        $this->command->info('✅ 8 guests created.');
    }
}