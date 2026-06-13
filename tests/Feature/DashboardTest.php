<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private Hotel    $hotel;
    private Employee $manager;
    private Employee $receptionist;
    private Employee $housekeeper;

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

        $this->manager = Employee::create([
            'hotel_id' => $this->hotel->id,
            'name'     => 'Alice Manager',
            'email'    => 'manager@test.com',
            'password' => bcrypt('password'),
            'role'     => 'manager',
        ]);

        $this->receptionist = Employee::create([
            'hotel_id' => $this->hotel->id,
            'name'     => 'Bob Reception',
            'email'    => 'reception@test.com',
            'password' => bcrypt('password'),
            'role'     => 'receptionist',
        ]);

        $this->housekeeper = Employee::create([
            'hotel_id' => $this->hotel->id,
            'name'     => 'Carol Housekeeper',
            'email'    => 'housekeeper@test.com',
            'password' => bcrypt('password'),
            'role'     => 'housekeeper',
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // ROLE-BASED VIEWS
    // ─────────────────────────────────────────────────────────

    public function test_manager_sees_analytics_dashboard(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.manager');
    }

    // ── UPDATED: receptionist now gets their own view ─────────
    public function test_receptionist_sees_front_desk_dashboard(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.receptionist');
    }

    // ── UPDATED: housekeeper now gets their own view ──────────
    public function test_housekeeper_sees_task_dashboard(): void
    {
        $response = $this->actingAs($this->housekeeper, 'employee')
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.housekeeper');
    }

    public function test_unauthenticated_user_redirects_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    // ─────────────────────────────────────────────────────────
    // STATS CORRECTNESS
    // ─────────────────────────────────────────────────────────

    public function test_dashboard_stats_contain_required_keys(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats');

        $stats = $response->viewData('stats');

        $requiredKeys = [
            'totalRooms', 'occupiedCount', 'occupancyRate',
            'arrivingToday', 'departingToday', 'revenueThisMonth',
            'activeBookings', 'daily30Revenue', 'monthlyRevenue',
            'roomStatus',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $stats, "Missing key: {$key}");
        }
    }

    public function test_dashboard_passes_metric_cards_to_view(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('metricCards');

        $cards = $response->viewData('metricCards');
        $this->assertCount(6, $cards);
        $this->assertEquals('Revenue MTD', $cards[0]['label']);
    }

    public function test_receptionist_dashboard_has_arrivals_data(): void
    {
        $response = $this->actingAs($this->receptionist, 'employee')
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('arrivals');
        $response->assertViewHas('departures');
        $response->assertViewHas('inHouse');
        $response->assertViewHas('upcoming');
    }

    public function test_housekeeper_dashboard_has_tasks_data(): void
    {
        $response = $this->actingAs($this->housekeeper, 'employee')
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('allTasks');
        $response->assertViewHas('pendingTasks');
        $response->assertViewHas('completedTasks');
        $response->assertViewHas('progressPercent');
    }

    public function test_occupancy_rate_calculates_correctly(): void
    {
        // Create 4 rooms
        for ($i = 1; $i <= 4; $i++) {
            Room::create([
                'hotel_id'        => $this->hotel->id,
                'number'          => "10{$i}",
                'type'            => 'standard',
                'status'          => 'available',
                'floor'           => 1,
                'capacity'        => 2,
                'price_per_night' => 100,
            ]);
        }

        $guest = Guest::create([
            'hotel_id'   => $this->hotel->id,
            'first_name' => 'Test',
            'last_name'  => 'Guest',
            'phone'      => '+255700000001',
            'id_type'    => 'national_id',
            'id_number'  => 'T001',
        ]);

        $room = Room::where('hotel_id', $this->hotel->id)->first();

        // Mark 1 of 4 rooms as checked_in → 25% occupancy
        Booking::create([
            'booking_number' => 'BK-TEST001',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'checkin_date'   => today()->subDay()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'checked_in',
            'actual_checkin' => now()->subDay(),
            'adults'         => 1,
            'room_rate'      => 100.00,
            'total_amount'   => 300.00,
        ]);

        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('dashboard'));

        $response->assertOk();
        $stats = $response->viewData('stats');

        $this->assertEquals(25.0, $stats['occupancyRate']);
        $this->assertEquals(1,    $stats['occupiedCount']);
    }
}