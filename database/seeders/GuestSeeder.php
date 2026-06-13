<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Hotel;
use Illuminate\Database\Seeder;

class GuestSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::first();
        if (!$hotel) { $this->command->error('No hotel found.'); return; }

        $guests = [
            ['first_name'=>'Alice',   'last_name'=>'Johnson', 'phone'=>'+1-555-0101', 'email'=>'alice@example.com',   'id_type'=>'passport',    'id_number'=>'P12345678', 'nationality'=>'American'],
            ['first_name'=>'Bob',     'last_name'=>'Smith',   'phone'=>'+1-555-0102', 'email'=>'bob@example.com',     'id_type'=>'national_id', 'id_number'=>'N87654321', 'nationality'=>'American'],
            ['first_name'=>'Claire',  'last_name'=>'Dupont',  'phone'=>'+33-600000001','email'=>'claire@example.com',  'id_type'=>'passport',    'id_number'=>'F11223344', 'nationality'=>'French'],
            ['first_name'=>'David',   'last_name'=>'Osei',    'phone'=>'+233-200000001','email'=>'david@example.com',  'id_type'=>'national_id', 'id_number'=>'G55667788', 'nationality'=>'Ghanaian'],
            ['first_name'=>'Fatima',  'last_name'=>'Hassan',  'phone'=>'+255-700000001','email'=>'fatima@example.com', 'id_type'=>'passport',    'id_number'=>'TZ9988776', 'nationality'=>'Tanzanian'],
            ['first_name'=>'George',  'last_name'=>'Williams','phone'=>'+44-7911123456','email'=>'george@example.com', 'id_type'=>'passport',    'id_number'=>'UK4433221', 'nationality'=>'British'],
        ];

        foreach ($guests as $data) {
            Guest::firstOrCreate(
                ['hotel_id' => $hotel->id, 'phone' => $data['phone']],
                array_merge($data, ['hotel_id' => $hotel->id])
            );
        }

        $this->command->info('✅ Created ' . count($guests) . ' sample guests.');
    }
}