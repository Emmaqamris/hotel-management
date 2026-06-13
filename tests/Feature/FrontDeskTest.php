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

class FrontDeskTest extends TestCase
{
    use RefreshDatabase;

    private Hotel    $hotel;
    private Employee $receptionist;
    private Room     $room;
    private Guest    $guest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name'     => 'Test Hotel',
            'address'  => '1 Test Street',
            'city'     => 'Mbeya',
            'country'  => 'TZ',
            'phone'    => '+255700000000',
            'email'    => 'test@hotel.com',
            'settings' => ['tax_rate' => 16],
        ]);

        $this->receptionist = Employee::create([
            'hotel_id' => $this->hotel->id,
            'name'     => 'Jane Receptionist',
            'email'    => 'jane@test.com',
            'password' => bcrypt('password'),
            'role'     => 'receptionist',
        ]);

        $this->room = Room::create([
            'hotel_id'        => $this->hotel->id,
            'number'          => '101',
            'type'            => 'standard',
            'status'          => 'available',
            'floor'           => 1,
            'capacity'        => 2,
            'price_per_night' => 100.00,
        ]);

        $this->guest = Guest::create([
            'hotel_id'   => $this->hotel->id,
            'first_name' => 'Alice',
            'last_name'  => 'Tester',
            'phone'      => '+255700000001',
            'id_type'    => 'national_id',
            'id_number'  => 'T12345',
        ]);
    }

    // ─────────────────────────────────────────
    // PAGE ACCESS
    // ─────────────────────────────────────────

    public function test_front_desk_index_loads_for_receptionist(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('front-desk.index'));

        $response->assertOk();
        $response->assertViewIs('front-desk.index');
    }

    public function test_room_board_loads_for_receptionist(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('front-desk.room-board'));

        $response->assertOk();
        $response->assertViewIs('front-desk.room-board');
    }

    public function test_front_desk_redirects_guests_to_login(): void
    {
        $response = $this->get(route('front-desk.index'));
        $response->assertRedirect(route('login'));
    }

    // ─────────────────────────────────────────
    // ARRIVALS APPEAR IN DASHBOARD
    // ─────────────────────────────────────────

    public function test_todays_arrival_appears_in_front_desk(): void
    {
        Booking::create([
            'booking_number' => 'BK-TEST0001',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 200.00,
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('front-desk.index'));

        $response->assertOk();
        $response->assertViewHas('arrivalsToday', function ($arrivals) {
            return $arrivals->count() === 1;
        });
        $response->assertSee('Alice Tester');
    }

    public function test_future_booking_does_not_appear_as_arrival_today(): void
    {
        Booking::create([
            'booking_number' => 'BK-TEST0002',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->addDays(3)->format('Y-m-d'),
            'checkout_date'  => today()->addDays(5)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 200.00,
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('front-desk.index'));

        $response->assertViewHas('arrivalsToday', function ($arrivals) {
            return $arrivals->isEmpty();
        });
    }

    // ─────────────────────────────────────────
    // CHECK-IN
    // ─────────────────────────────────────────

    public function test_receptionist_can_check_in_a_guest(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST0003',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 200.00,
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('bookings.check-in', $booking));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'checked_in',
        ]);

        // actual_checkin should be set
        $this->assertNotNull($booking->fresh()->actual_checkin);
    }

    public function test_cannot_check_in_a_booking_for_tomorrow(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST0004',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->addDay()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(3)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 200.00,
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('bookings.check-in', $booking));

        // Should redirect back with an error, not check in
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'confirmed', // unchanged
        ]);
    }

    public function test_cannot_check_in_an_already_checked_in_booking(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST0005',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'checked_in',
            'actual_checkin' => now(),
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 200.00,
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('bookings.check-in', $booking));

        $response->assertSessionHas('error');
    }

    // ─────────────────────────────────────────
    // CHECK-OUT
    // ─────────────────────────────────────────

    public function test_receptionist_can_check_out_a_guest(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST0006',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->subDay()->format('Y-m-d'),
            'checkout_date'  => today()->format('Y-m-d'),
            'status'         => 'checked_in',
            'actual_checkin' => now()->subDay(),
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 100.00,
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('bookings.check-out', $booking));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'checked_out',
        ]);

        $this->assertNotNull($booking->fresh()->actual_checkout);
    }

    public function test_cannot_check_out_a_booking_that_is_not_checked_in(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST0007',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed', // not checked in yet
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 200.00,
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('bookings.check-out', $booking));

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'confirmed', // unchanged
        ]);
    }

    // ─────────────────────────────────────────
    // ROOM BOARD
    // ─────────────────────────────────────────

    public function test_room_board_shows_occupied_room(): void
    {
        Booking::create([
            'booking_number' => 'BK-TEST0008',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->subDay()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'checked_in',
            'actual_checkin' => now()->subDay(),
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 300.00,
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('front-desk.room-board'));

        $response->assertOk();

        // The checked-in booking should be in the view data
        $response->assertViewHas('checkedInBookings', function ($bookings) {
            return $bookings->has($this->room->id);
        });
    }

    public function test_room_board_shows_arriving_room(): void
    {
        Booking::create([
            'booking_number' => 'BK-TEST0009',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 200.00,
        ]);

        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('front-desk.room-board'));

        $response->assertViewHas('arrivingTodayBookings', function ($bookings) {
            return $bookings->has($this->room->id);
        });
    }
}