<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Hotel      $hotel;
    private Employee   $receptionist;
    private Room       $room;
    private Guest      $guest;
    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Test Hotel', 'address' => '1 Test St',
            'city' => 'Mbeya', 'country' => 'TZ',
            'phone' => '+255700000000', 'email' => 'test@hotel.com',
            'settings' => ['tax_rate' => 16],
        ]);

        $this->receptionist = Employee::create([
            'hotel_id' => $this->hotel->id, 'name' => 'Jane Receptionist',
            'email' => 'jane@test.com', 'password' => bcrypt('password'),
            'role' => 'receptionist',
        ]);

        $this->room = Room::create([
            'hotel_id' => $this->hotel->id, 'number' => '101',
            'type' => 'standard', 'status' => 'available',
            'floor' => 1, 'capacity' => 2, 'price_per_night' => 100.00,
        ]);

        $this->guest = Guest::create([
            'hotel_id' => $this->hotel->id, 'first_name' => 'John',
            'last_name' => 'Doe', 'phone' => '+255700000001',
            'id_type' => 'national_id', 'id_number' => 'T123456',
        ]);

        $this->service = app(BookingService::class);
    }

    public function test_receptionist_can_create_booking(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('bookings.store'), [
                'room_id'       => $this->room->id,
                'guest_id'      => $this->guest->id,
                'checkin_date'  => today()->format('Y-m-d'),
                'checkout_date' => today()->addDays(2)->format('Y-m-d'),
                'adults'        => 2,
                'source'        => 'walk_in',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bookings', [
            'room_id'  => $this->room->id,
            'guest_id' => $this->guest->id,
            'status'   => 'confirmed',
        ]);
    }

    public function test_double_booking_is_prevented(): void
    {
        $this->service->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => today()->format('Y-m-d'),
            'checkout_date'=> today()->addDays(3)->format('Y-m-d'),
        ], $this->hotel->id, $this->receptionist->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/not available/');

        $guest2 = Guest::create([
            'hotel_id' => $this->hotel->id, 'first_name' => 'Jane',
            'last_name' => 'Smith', 'phone' => '+255700000002',
            'id_type' => 'national_id', 'id_number' => 'T654321',
        ]);

        $this->service->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $guest2->id,
            'checkin_date' => today()->addDay()->format('Y-m-d'),
            'checkout_date'=> today()->addDays(4)->format('Y-m-d'),
        ], $this->hotel->id, $this->receptionist->id);
    }

    public function test_same_room_can_be_booked_after_checkout_date(): void
    {
        $this->service->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => today()->format('Y-m-d'),
            'checkout_date'=> today()->addDays(3)->format('Y-m-d'),
        ], $this->hotel->id, $this->receptionist->id);

        $guest2 = Guest::create([
            'hotel_id' => $this->hotel->id, 'first_name' => 'Jane',
            'last_name' => 'Smith', 'phone' => '+255700000002',
            'id_type' => 'national_id', 'id_number' => 'T654321',
        ]);

        $booking2 = $this->service->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $guest2->id,
            'checkin_date' => today()->addDays(3)->format('Y-m-d'),
            'checkout_date'=> today()->addDays(5)->format('Y-m-d'),
        ], $this->hotel->id, $this->receptionist->id);

        $this->assertEquals('confirmed', $booking2->status);
        $this->assertEquals(2, Booking::where('room_id', $this->room->id)->count());
    }

    public function test_cancelled_booking_does_not_block_room(): void
    {
        $booking = $this->service->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => today()->format('Y-m-d'),
            'checkout_date'=> today()->addDays(3)->format('Y-m-d'),
        ], $this->hotel->id, $this->receptionist->id);

        $this->service->cancelBooking($booking, 'Changed plans');

        $guest2 = Guest::create([
            'hotel_id' => $this->hotel->id, 'first_name' => 'Jane',
            'last_name' => 'Smith', 'phone' => '+255700000002',
            'id_type' => 'national_id', 'id_number' => 'T654321',
        ]);

        $booking2 = $this->service->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $guest2->id,
            'checkin_date' => today()->format('Y-m-d'),
            'checkout_date'=> today()->addDays(3)->format('Y-m-d'),
        ], $this->hotel->id, $this->receptionist->id);

        $this->assertEquals('confirmed', $booking2->status);
    }

    public function test_check_in_sets_status_to_checked_in(): void
    {
        $booking = $this->service->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => today()->format('Y-m-d'),
            'checkout_date'=> today()->addDays(2)->format('Y-m-d'),
        ], $this->hotel->id, $this->receptionist->id);

        $this->service->checkIn($booking, $this->receptionist->id);

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'checked_in',
        ]);
    }

    public function test_check_out_sets_status_to_checked_out(): void
    {
        $booking = Booking::create([
            'booking_number'=> 'BK-TEST0001',
            'hotel_id'      => $this->hotel->id,
            'room_id'       => $this->room->id,
            'guest_id'      => $this->guest->id,
            'checkin_date'  => today()->subDay()->format('Y-m-d'),
            'checkout_date' => today()->addDay()->format('Y-m-d'),
            'status'        => 'checked_in',
            'adults'        => 1, 'room_rate' => 100,
            'total_amount'  => 200, 'actual_checkin' => now()->subDay(),
        ]);

        $this->service->checkOut($booking);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id, 'status' => 'checked_out',
        ]);
    }

    public function test_booking_can_be_cancelled(): void
    {
        $booking = $this->service->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => today()->addDays(5)->format('Y-m-d'),
            'checkout_date'=> today()->addDays(7)->format('Y-m-d'),
        ], $this->hotel->id, $this->receptionist->id);

        $this->service->cancelBooking($booking, 'Guest requested cancellation');

        $this->assertDatabaseHas('bookings', [
            'id'                  => $booking->id,
            'status'              => 'cancelled',
            'cancellation_reason' => 'Guest requested cancellation',
        ]);
    }

    public function test_checked_in_booking_cannot_be_cancelled(): void
    {
        $booking = Booking::create([
            'booking_number'=> 'BK-TEST0002',
            'hotel_id'      => $this->hotel->id,
            'room_id'       => $this->room->id,
            'guest_id'      => $this->guest->id,
            'checkin_date'  => today()->format('Y-m-d'),
            'checkout_date' => today()->addDays(2)->format('Y-m-d'),
            'status'        => 'checked_in',
            'adults'        => 1, 'room_rate' => 100,
            'total_amount'  => 200,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/cannot be cancelled/');

        $this->service->cancelBooking($booking);
    }

    public function test_room_shows_as_unavailable_during_booking(): void
    {
        $this->service->createBooking([
            'room_id'      => $this->room->id,
            'guest_id'     => $this->guest->id,
            'checkin_date' => '2026-08-01',
            'checkout_date'=> '2026-08-05',
        ], $this->hotel->id, $this->receptionist->id);

        $this->assertFalse(
            $this->service->isRoomAvailable($this->room->id, '2026-08-02', '2026-08-04')
        );

        $this->assertTrue(
            $this->service->isRoomAvailable($this->room->id, '2026-08-05', '2026-08-08')
        );
    }
}
