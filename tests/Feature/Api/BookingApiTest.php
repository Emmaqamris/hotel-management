<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiTest extends TestCase
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
            'address'  => '1 Test St',
            'city'     => 'Mbeya',
            'country'  => 'TZ',
            'phone'    => '+255700000000',
            'email'    => 'test@hotel.com',
            'settings' => ['tax_rate' => 16],
        ]);

        $this->receptionist = Employee::create([
            'hotel_id' => $this->hotel->id,
            'name'     => 'Jane',
            'email'    => 'jane@test.com',
            'password' => bcrypt('password'),
            'role'     => 'receptionist',
            'is_active'=> true,
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
            'email'      => 'alice@example.com',
            'id_type'    => 'national_id',
            'id_number'  => 'T12345',
        ]);
    }

    // ── LIST ───────────────────────────────────────────────────

    public function test_can_list_bookings(): void
    {
        $response = $this->actingAs($this->receptionist, 'sanctum')
            ->getJson('/api/bookings');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['data', 'current_page', 'total'],
            ]);
    }

    // ── CREATE ─────────────────────────────────────────────────

    public function test_can_create_booking_via_api(): void
    {
        $response = $this->actingAs($this->receptionist, 'sanctum')
            ->postJson('/api/bookings', [
                'room_id'      => $this->room->id,
                'guest_id'     => $this->guest->id,
                'checkin_date' => today()->format('Y-m-d'),
                'checkout_date'=> today()->addDays(2)->format('Y-m-d'),
                'adults'       => 2,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['booking_number', 'status', 'total_amount'],
            ]);

        $this->assertEquals('confirmed', $response->json('data.status'));
    }

    public function test_api_prevents_double_booking(): void
    {
        // First booking
        $this->actingAs($this->receptionist, 'sanctum')
            ->postJson('/api/bookings', [
                'room_id'      => $this->room->id,
                'guest_id'     => $this->guest->id,
                'checkin_date' => today()->format('Y-m-d'),
                'checkout_date'=> today()->addDays(3)->format('Y-m-d'),
                'adults'       => 1,
            ]);

        // Second overlapping booking
        $guest2 = Guest::create([
            'hotel_id' => $this->hotel->id, 'first_name' => 'Bob',
            'last_name' => 'Two', 'phone' => '+255700000003',
            'id_type' => 'national_id', 'id_number' => 'T99999',
        ]);

        $response = $this->actingAs($this->receptionist, 'sanctum')
            ->postJson('/api/bookings', [
                'room_id'      => $this->room->id,
                'guest_id'     => $guest2->id,
                'checkin_date' => today()->addDay()->format('Y-m-d'),
                'checkout_date'=> today()->addDays(4)->format('Y-m-d'),
                'adults'       => 1,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertStringContainsString(
            'not available',
            $response->json('message')
        );
    }

    // ── SHOW ───────────────────────────────────────────────────

    public function test_can_view_booking_via_api(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST001',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $response = $this->actingAs($this->receptionist, 'sanctum')
            ->getJson("/api/bookings/{$booking->id}");

        $response->assertOk()
            ->assertJsonPath('data.booking_number', 'BK-TEST001')
            ->assertJsonPath('data.status', 'confirmed');
    }

    // ── CHECK IN / OUT ─────────────────────────────────────────

    public function test_check_in_via_api(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST002',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $response = $this->actingAs($this->receptionist, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/check-in");

        $response->assertOk()
            ->assertJsonPath('data.status', 'checked_in');
    }

    public function test_check_out_via_api(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST003',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->subDay()->format('Y-m-d'),
            'checkout_date'  => today()->addDay()->format('Y-m-d'),
            'status'         => 'checked_in',
            'actual_checkin' => now()->subDay(),
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $response = $this->actingAs($this->receptionist, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/check-out");

        $response->assertOk()
            ->assertJsonPath('data.status', 'checked_out');
    }

    // ── CANCEL ─────────────────────────────────────────────────

    public function test_cancel_via_api(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-TEST004',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $this->room->id,
            'guest_id'       => $this->guest->id,
            'checkin_date'   => today()->addDays(5)->format('Y-m-d'),
            'checkout_date'  => today()->addDays(7)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $response = $this->actingAs($this->receptionist, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/cancel", [
                'reason' => 'Guest requested via API',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    // ── ROOMS AVAILABLE ────────────────────────────────────────

    public function test_available_rooms_api(): void
    {
        $response = $this->actingAs($this->receptionist, 'sanctum')
            ->getJson('/api/rooms/available?' . http_build_query([
                'checkin_date'  => today()->format('Y-m-d'),
                'checkout_date' => today()->addDays(2)->format('Y-m-d'),
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['rooms', 'count', 'checkin_date', 'checkout_date'],
            ]);
    }

    // ── DASHBOARD ──────────────────────────────────────────────

    public function test_dashboard_stats_api(): void
    {
        $response = $this->actingAs($this->receptionist, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'totalRooms',
                    'occupancyRate',
                    'activeBookings',
                    'revenueThisMonth',
                ],
            ]);
    }
}