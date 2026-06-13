<?php
namespace Tests\Feature;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class GuestTest extends TestCase
{
    use RefreshDatabase;
    private Hotel    $hotel;
    private Employee $receptionist;
    private Employee $manager;
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
            'hotel_id' => $this->hotel->id, 'name' => 'Jane',
            'email' => 'jane@test.com', 'password' => bcrypt('password'),
            'role' => 'receptionist',
        ]);
        $this->manager = Employee::create([
            'hotel_id' => $this->hotel->id, 'name' => 'Bob Manager',
            'email' => 'bob@test.com', 'password' => bcrypt('password'),
            'role' => 'manager',
        ]);
    }
    private function validGuestData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Alice',
            'last_name'  => 'Johnson',
            'phone'      => '+255700000001',
            'email'      => 'alice@example.com',
            'id_type'    => 'national_id',
            'id_number'  => 'T12345678',
            'nationality'=> 'Tanzanian',
        ], $overrides);
    }
    public function test_receptionist_can_register_a_guest(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('guests.store'), $this->validGuestData());
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('guests', [
            'hotel_id'   => $this->hotel->id,
            'first_name' => 'Alice',
            'last_name'  => 'Johnson',
            'id_number'  => 'T12345678',
        ]);
    }
    public function test_duplicate_id_number_is_blocked(): void
    {
        Guest::create(array_merge($this->validGuestData(), ['hotel_id' => $this->hotel->id]));
        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('guests.store'), $this->validGuestData([
                'first_name' => 'Different',
                'last_name'  => 'Person',
            ]));
        $response->assertSessionHas('error');
        $this->assertEquals(1, Guest::where('id_number', 'T12345678')->count());
    }
    public function test_phone_is_required(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('guests.store'), $this->validGuestData(['phone' => '']));
        $response->assertSessionHasErrors('phone');
    }
    public function test_guest_can_be_updated(): void
    {
        $guest = Guest::create(array_merge(
            $this->validGuestData(), ['hotel_id' => $this->hotel->id]
        ));
        $this->actingAs($this->receptionist, 'employee')
            ->put(route('guests.update', $guest), $this->validGuestData([
                'email' => 'newemail@example.com',
            ]));
        $this->assertDatabaseHas('guests', [
            'id'    => $guest->id,
            'email' => 'newemail@example.com',
        ]);
    }
    public function test_guest_profile_shows_booking_history(): void
    {
        $guest = Guest::create(array_merge(
            $this->validGuestData(), ['hotel_id' => $this->hotel->id]
        ));
        $room = Room::create([
            'hotel_id' => $this->hotel->id, 'number' => '101',
            'type' => 'standard', 'status' => 'available',
            'floor' => 1, 'capacity' => 2, 'price_per_night' => 100,
        ]);
        Booking::create([
            'booking_number' => 'BK-TEST0001',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'confirmed',
            'adults'         => 1, 'room_rate' => 100, 'total_amount' => 200,
        ]);
        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('guests.show', $guest));
        $response->assertOk();
        $response->assertSee('BK-TEST0001');
        $response->assertSee('Alice');
    }
    public function test_cannot_view_guest_from_different_hotel(): void
    {
        $otherHotel = Hotel::create([
            'name' => 'Other Hotel', 'address' => '2 Other St',
            'city' => 'Dar', 'country' => 'TZ',
            'phone' => '+255700000002', 'email' => 'other@hotel.com',
        ]);
        $otherGuest = Guest::create(array_merge(
            $this->validGuestData(['phone' => '+255700000099']),
            ['hotel_id' => $otherHotel->id]
        ));
        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('guests.show', $otherGuest));
        $response->assertForbidden();
    }
    public function test_guest_search_returns_json(): void
    {
        Guest::create(array_merge(
            $this->validGuestData(), ['hotel_id' => $this->hotel->id]
        ));
        $response = $this->actingAs($this->receptionist, 'employee')
            ->getJson(route('guests.search', ['q' => 'Alice']));
        $response->assertOk();
        $response->assertJsonFragment(['first_name' => 'Alice']);
    }
    public function test_search_requires_minimum_2_chars(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->getJson(route('guests.search', ['q' => 'A']));
        $response->assertUnprocessable();
    }
    public function test_quick_register_redirects_to_booking_create(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->post(route('guests.store'), array_merge(
                $this->validGuestData(['phone' => '+255700000003']),
                ['quick' => '1']
            ));
        $response->assertRedirect();
        $this->assertStringContainsString('bookings', $response->headers->get('Location'));
    }
}
