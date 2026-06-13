<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Room;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private Hotel         $hotel;
    private Employee      $manager;
    private ReportService $reportService;

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
            'name'     => 'Manager',
            'email'    => 'manager@test.com',
            'password' => bcrypt('password'),
            'role'     => 'manager',
        ]);

        $this->reportService = app(ReportService::class);
    }

    // ── Access ─────────────────────────────────────────────────

    public function test_reports_hub_is_accessible_to_manager(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewIs('reports.index');
    }

    public function test_receptionist_cannot_access_reports(): void
    {
        $receptionist = Employee::create([
            'hotel_id' => $this->hotel->id, 'name' => 'Reception',
            'email'    => 'reception@test.com', 'password' => bcrypt('password'),
            'role'     => 'receptionist',
        ]);

        $response = $this->actingAs($receptionist, 'employee')
            ->get(route('reports.index'));

        $response->assertForbidden();
    }

    // ── Revenue Report ─────────────────────────────────────────

    public function test_revenue_report_page_loads(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('reports.revenue'));

        $response->assertOk();
        $response->assertViewIs('reports.revenue');
        $response->assertViewHas('data');
    }

    public function test_revenue_report_sums_payments_correctly(): void
    {
        $room  = Room::create([
            'hotel_id' => $this->hotel->id, 'number' => '101',
            'type' => 'standard', 'status' => 'available',
            'floor' => 1, 'capacity' => 2, 'price_per_night' => 100,
        ]);

        $guest = Guest::create([
            'hotel_id' => $this->hotel->id, 'first_name' => 'Test',
            'last_name' => 'Guest', 'phone' => '+255700000001',
            'id_type' => 'national_id', 'id_number' => 'T001',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-TEST001',
            'hotel_id'       => $this->hotel->id,
            'room_id'        => $room->id,
            'guest_id'       => $guest->id,
            'checkin_date'   => today()->format('Y-m-d'),
            'checkout_date'  => today()->addDays(2)->format('Y-m-d'),
            'status'         => 'checked_out',
            'adults'         => 1,
            'room_rate'      => 100,
            'total_amount'   => 200,
        ]);

        $invoice = Invoice::create([
            'booking_id' => $booking->id,
            'hotel_id'   => $this->hotel->id,
            'guest_id'   => $guest->id,
            'subtotal'   => 200,
            'tax_rate'   => 16,
            'tax_amount' => 32,
            'total'      => 232,
            'status'     => 'paid',
            'issued_at'  => now(),
        ]);

        Payment::create([
            'invoice_id'       => $invoice->id,
            'hotel_id'         => $this->hotel->id,
            'amount'           => 232.00,
            'method'           => 'cash',
            'status'           => 'completed',
            'reference_number' => 'CASH-001',
            'paid_at'          => now(),
            'processed_by'     => $this->manager->id,
        ]);

        $from = today()->startOfMonth();
        $to   = today()->endOfMonth();
        $data = $this->reportService->getRevenueReport($this->hotel->id, $from, $to);

        $this->assertEquals(232.00, $data['total_revenue']);
        $this->assertEquals(1,      $data['total_payments']);
        $this->assertNotEmpty($data['by_method']);
        $this->assertEquals('cash', $data['by_method'][0]['method']);
    }

    // ── Occupancy Report ───────────────────────────────────────

    public function test_occupancy_report_page_loads(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('reports.occupancy'));

        $response->assertOk();
        $response->assertViewIs('reports.occupancy');
        $response->assertViewHas('data');
    }

    public function test_occupancy_report_has_daily_data_for_range(): void
    {
        $from = now()->startOfMonth();
        $to   = now()->startOfMonth()->addDays(6); // 7 days

        $data = $this->reportService->getOccupancyReport(
            $this->hotel->id, $from, $to
        );

        $this->assertArrayHasKey('daily_data',          $data);
        $this->assertArrayHasKey('avg_occupancy_rate',  $data);
        $this->assertCount(7, $data['daily_data']);
    }

    // ── Bookings Report ────────────────────────────────────────

    public function test_bookings_report_page_loads(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('reports.bookings'));

        $response->assertOk();
        $response->assertViewIs('reports.bookings');
    }

    // ── CSV Export ─────────────────────────────────────────────

    public function test_revenue_csv_export_returns_csv_file(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('reports.revenue.export', [
                'from' => now()->startOfMonth()->format('Y-m-d'),
                'to'   => now()->endOfMonth()->format('Y-m-d'),
            ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'text/csv',
            $response->headers->get('Content-Type')
        );
    }

    public function test_occupancy_csv_export_returns_csv_file(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('reports.occupancy.export', [
                'from' => now()->startOfMonth()->format('Y-m-d'),
                'to'   => now()->endOfMonth()->format('Y-m-d'),
            ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'text/csv',
            $response->headers->get('Content-Type')
        );
    }

    // ── Date filter validation ─────────────────────────────────

    public function test_invalid_date_range_is_rejected(): void
    {
        $response = $this->actingAs($this->manager, 'employee')
            ->get(route('reports.revenue', [
                'from' => '2026-12-01',
                'to'   => '2026-01-01', // to < from
            ]));

        $response->assertSessionHasErrors('to');
    }
}