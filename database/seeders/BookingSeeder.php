<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $hotel      = Hotel::first();
        $guests     = Guest::where('hotel_id', $hotel->id)->get();
        $rooms      = Room::where('hotel_id', $hotel->id)->where('status', 'available')->get();
        $receptionist = Employee::where('hotel_id', $hotel->id)
            ->where('role', 'receptionist')->first();

        if ($guests->isEmpty() || $rooms->isEmpty()) {
            $this->command->error('Run GuestSeeder and RoomSeeder first.');
            return;
        }

        $scenarios = [
            // Currently checked in
            ['room' => $rooms[0], 'guest' => $guests[0], 'status' => 'checked_in',
             'checkin' => today()->subDays(1), 'checkout' => today()->addDays(2)],
            // Arriving today
            ['room' => $rooms[1], 'guest' => $guests[1], 'status' => 'confirmed',
             'checkin' => today(), 'checkout' => today()->addDays(3)],
            // Future booking
            ['room' => $rooms[2], 'guest' => $guests[2], 'status' => 'confirmed',
             'checkin' => today()->addDays(3), 'checkout' => today()->addDays(5)],
            // Checked out (history)
            ['room' => $rooms[3], 'guest' => $guests[3], 'status' => 'checked_out',
             'checkin' => today()->subDays(5), 'checkout' => today()->subDays(2)],
            // Cancelled
            ['room' => $rooms[4], 'guest' => $guests[4], 'status' => 'cancelled',
             'checkin' => today()->addDays(7), 'checkout' => today()->addDays(9)],
        ];

        foreach ($scenarios as $s) {
            $nights = $s['checkin']->diffInDays($s['checkout']);
            $total  = $s['room']->price_per_night * $nights;

            $booking = Booking::create([
                'hotel_id'      => $hotel->id,
                'room_id'       => $s['room']->id,
                'guest_id'      => $s['guest']->id,
                'employee_id'   => $receptionist?->id,
                'checkin_date'  => $s['checkin']->format('Y-m-d'),
                'checkout_date' => $s['checkout']->format('Y-m-d'),
                'status'        => $s['status'],
                'adults'        => 2,
                'children'      => 0,
                'room_rate'     => $s['room']->price_per_night,
                'total_amount'  => $total,
                'source'        => 'walk_in',
                'actual_checkin' => $s['status'] === 'checked_in' ? now()->subDay() : null,
                'actual_checkout'=> $s['status'] === 'checked_out' ? now()->subDays(2) : null,
                'cancelled_at'  => $s['status'] === 'cancelled' ? now() : null,
            ]);
        }

        $this->command->info('✅ Created ' . count($scenarios) . ' sample bookings.');
    }
}