<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    private Hotel    $hotel;
    private Employee $manager;
    private Employee $receptionist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Test Hotel', 'address' => '1 Test St',
            'city' => 'Testcity', 'country' => 'TZ',
            'phone' => '+255700000000', 'email' => 'test@hotel.com',
            'settings' => ['tax_rate' => 16],
        ]);

        $this->manager = Employee::create([
            'hotel_id' => $this->hotel->id, 'name' => 'Manager User',
            'email' => 'manager@test.com', 'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $this->receptionist = Employee::create([
            'hotel_id' => $this->hotel->id, 'name' => 'Receptionist User',
            'email' => 'reception@test.com', 'password' => bcrypt('password'),
            'role' => 'receptionist',
        ]);
    }

    // ── CREATE ──────────────────────────────────────

    public function test_manager_can_create_a_room(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->post(route('rooms.store'), [
                'number'          => '101',
                'type'            => 'standard',
                'floor'           => 1,
                'capacity'        => 2,
                'price_per_night' => 100.00,
                'amenities'       => ['WiFi', 'TV'],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'hotel_id' => $this->hotel->id,
            'number'   => '101',
            'type'     => 'standard',
            'status'   => 'available',
        ]);
    }

    public function test_receptionist_cannot_create_a_room(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('rooms.store'), [
                'number'          => '202',
                'type'            => 'deluxe',
                'floor'           => 2,
                'capacity'        => 2,
                'price_per_night' => 150.00,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('rooms', ['number' => '202']);
    }

    public function test_duplicate_room_number_is_rejected(): void
    {
        Room::create([
            'hotel_id' => $this->hotel->id, 'number' => '101',
            'type' => 'standard', 'floor' => 1, 'capacity' => 2,
            'price_per_night' => 100, 'status' => 'available',
        ]);

        $response = $this->actingAs($this->manager, 'employee')
            ->post(route('rooms.store'), [
                'number' => '101', 'type' => 'deluxe',
                'floor' => 2, 'capacity' => 2, 'price_per_night' => 150,
            ]);

        $response->assertSessionHasErrors('number');
        $this->assertEquals(1, Room::where('hotel_id', $this->hotel->id)->where('number', '101')->count());
    }

    // ── UPDATE ──────────────────────────────────────

    public function test_manager_can_update_room_price(): void
    {
        $room = Room::create([
            'hotel_id' => $this->hotel->id, 'number' => '101',
            'type' => 'standard', 'floor' => 1, 'capacity' => 2,
            'price_per_night' => 100, 'status' => 'available',
        ]);

        $this->actingAs($this->manager, 'employee')
            ->put(route('rooms.update', $room), [
                'number' => '101', 'type' => 'standard',
                'floor' => 1, 'capacity' => 2, 'price_per_night' => 120.00,
            ]);

        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'price_per_night' => 120.00]);
    }

    // ── STATUS ──────────────────────────────────────

    public function test_receptionist_can_change_room_status(): void
    {
        $room = Room::create([
            'hotel_id' => $this->hotel->id, 'number' => '101',
            'type' => 'standard', 'floor' => 1, 'capacity' => 2,
            'price_per_night' => 100, 'status' => 'available',
        ]);

        $this->actingAs($this->receptionist, 'employee')
            ->patch(route('rooms.status', $room), ['status' => 'maintenance']);

        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'status' => 'maintenance']);
    }

    // ── DELETE ──────────────────────────────────────

    public function test_manager_can_delete_empty_room(): void
    {
        $room = Room::create([
            'hotel_id' => $this->hotel->id, 'number' => '999',
            'type' => 'standard', 'floor' => 1, 'capacity' => 2,
            'price_per_night' => 100, 'status' => 'available',
        ]);

        $this->actingAs($this->manager, 'employee')
            ->delete(route('rooms.destroy', $room));

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    // ── IMAGE ───────────────────────────────────────

    public function test_room_image_is_stored_on_create(): void
    {
        Storage::fake('public');

        $this->actingAs($this->manager, 'employee')
            ->post(route('rooms.store'), [
                'number'          => '303',
                'type'            => 'deluxe',
                'floor'           => 3,
                'capacity'        => 2,
                'price_per_night' => 150,
                'image'           => UploadedFile::fake()->image('room.jpg', 800, 600),
            ]);

        $room = Room::where('number', '303')->first();
        $this->assertNotNull($room->image);
        Storage::disk('public')->assertExists($room->image);
    }
}