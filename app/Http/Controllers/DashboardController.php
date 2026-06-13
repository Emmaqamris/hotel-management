<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\HousekeepingLog;
use App\Models\Room;
use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $user    = $request->user('employee');
        $hotelId = $user->hotel_id;

        // ── Route to role-specific view ───────────────────────
        return match(true) {
            $user->isHousekeeper()  => $this->housekeeperDashboard($user),
            $user->isReceptionist() => $this->receptionistDashboard($user, $hotelId),
            default                 => $this->managerDashboard($user, $hotelId),
        };
    }

    // ─────────────────────────────────────────────────────────
    // ADMIN / MANAGER
    // ─────────────────────────────────────────────────────────

    private function managerDashboard($user, int $hotelId)
{
    $stats = $this->reportService->getDashboardStats($hotelId);

    $recentBookings = Booking::where('hotel_id', $hotelId)
        ->with(['room', 'guest'])
        ->latest()
        ->limit(8)
        ->get();

    $metricCards = [
        [
            'label'      => 'Revenue MTD',
            'value'      => number_format($stats['revenueThisMonth'], 2),
            'sub'        => ($stats['revenueChange'] >= 0 ? '↑ ' : '↓ ')
                            . abs($stats['revenueChange']) . '% vs last month',
            'subColor'   => $stats['revenueChange'] >= 0
                ? 'text-emerald-600'
                : 'text-red-500',
            'iconBg'     => '#d1fae5',
            'iconColor'  => '#059669',
            'icon'       => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        [
            'label'      => 'Occupancy Rate',
            'value'      => $stats['occupancyRate'] . '%',
            'sub'        => $stats['occupiedCount'] . ' / ' . $stats['totalRooms'] . ' rooms',
            'subColor'   => 'text-slate-400',
            'iconBg'     => '#dbeafe',
            'iconColor'  => '#2563eb',
            'icon'       => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5',
        ],
        [
            'label'      => 'Active Bookings',
            'value'      => $stats['activeBookings'],
            'sub'        => 'confirmed + in house',
            'subColor'   => 'text-slate-400',
            'iconBg'     => '#fef3c7',
            'iconColor'  => '#d97706',
            'icon'       => 'M8 7V3m8 4V3m-9 8h10',
        ],
        [
            'label'      => 'ADR',
            'value'      => number_format($stats['adr'], 2),
            'sub'        => 'avg daily rate',
            'subColor'   => 'text-slate-400',
            'iconBg'     => '#ede9fe',
            'iconColor'  => '#7c3aed',
            'icon'       => 'M9 19v-6a2 2 0 00-2-2H5',
        ],
        [
            'label'      => 'Arrivals Today',
            'value'      => $stats['arrivingToday'],
            'sub'        => 'guests checking in',
            'subColor'   => 'text-slate-400',
            'iconBg'     => '#d1fae5',
            'iconColor'  => '#059669',
            'icon'       => 'M11 16l-4-4m0 0l4-4',
        ],
        [
            'label'      => 'Departures Today',
            'value'      => $stats['departingToday'],
            'sub'        => 'guests checking out',
            'subColor'   => 'text-slate-400',
            'iconBg'     => '#fee2e2',
            'iconColor'  => '#dc2626',
            'icon'       => 'M17 16l4-4m0 0l-4-4',
        ],
    ];

    // Greeting
$hour = now()->hour;

$greeting = match (true) {
    $hour < 12 => 'Good Morning',
    $hour < 17 => 'Good Afternoon',
    default    => 'Good Evening',
};

$firstName = explode(' ', $user->name)[0];

// Chart data
$chartData = json_encode([
    'dailyRevenue'   => $stats['daily30Revenue'] ?? [],
    'dailyBookings'  => $stats['daily30Bookings'] ?? [],
    'monthlyRevenue' => $stats['monthlyRevenue'] ?? [],
    'roomStatus'     => $stats['roomStatus'] ?? [],
]);
$hour = now()->hour;
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
$firstName = explode(' ', $user->name)[0];

    return view('dashboard.manager', compact(
        'stats',
        'recentBookings',
        'user',
        'metricCards',
        'greeting',
        'firstName',
        'chartData'
    ));
}

    // ─────────────────────────────────────────────────────────
    // RECEPTIONIST
    // ─────────────────────────────────────────────────────────

    private function receptionistDashboard($user, int $hotelId)
    {
        $today = today();

        // Arrivals today (confirmed, checkin = today)
        $arrivals = Booking::where('hotel_id', $hotelId)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', $today)
            ->with(['guest', 'room'])
            ->orderBy('checkin_date')
            ->get();

        // Departures today (checked_in, checkout = today)
        $departures = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->whereDate('checkout_date', $today)
            ->with(['guest', 'room', 'invoice'])
            ->orderBy('checkout_date')
            ->get();

        // Currently in house
        $inHouse = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->with(['guest', 'room'])
            ->get();

        // Upcoming (next 7 days, excluding today)
        $upcoming = Booking::where('hotel_id', $hotelId)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', '>', $today)
            ->whereDate('checkin_date', '<=', $today->copy()->addDays(7))
            ->with(['guest', 'room'])
            ->orderBy('checkin_date')
            ->limit(5)
            ->get();

        // Room availability
        $totalRooms     = Room::where('hotel_id', $hotelId)->where('is_active', true)->count();
        $occupiedRooms  = Booking::where('hotel_id', $hotelId)->where('status', 'checked_in')->count();
        $availableRooms = max(0, $totalRooms - $occupiedRooms);

        // Overdue checkouts
        $overdueCheckouts = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->whereDate('checkout_date', '<', $today)
            ->with(['guest', 'room'])
            ->get();

        return view('dashboard.receptionist', compact(
            'user',
            'arrivals',
            'departures',
            'inHouse',
            'upcoming',
            'totalRooms',
            'occupiedRooms',
            'availableRooms',
            'overdueCheckouts'
        ));
    }

    // ─────────────────────────────────────────────────────────
    // HOUSEKEEPER
    // ─────────────────────────────────────────────────────────

    private function housekeeperDashboard($user)
    {
        $today = today();

        // All tasks assigned to this housekeeper
        $allTasks = HousekeepingLog::where('assigned_to', $user->id)
            ->whereDate('scheduled_at', $today)
            ->with(['room'])
            ->orderByRaw("FIELD(priority, 'urgent','high','normal','low')")
            ->orderBy('status')
            ->get();

        $pendingTasks   = $allTasks->whereIn('status', ['scheduled', 'in_progress']);
        $completedTasks = $allTasks->where('status', 'completed');
        $urgentTasks    = $allTasks->where('priority', 'urgent')->where('status', '!=', 'completed');

        // Upcoming tasks (next 7 days, not today)
        $upcomingTasks = HousekeepingLog::where('assigned_to', $user->id)
            ->whereDate('scheduled_at', '>', $today)
            ->whereDate('scheduled_at', '<=', $today->copy()->addDays(7))
            ->with(['room'])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $progressPercent = $allTasks->count() > 0
            ? round(($completedTasks->count() / $allTasks->count()) * 100)
            : 0;

        return view('dashboard.housekeeper', compact(
            'user',
            'allTasks',
            'pendingTasks',
            'completedTasks',
            'urgentTasks',
            'upcomingTasks',
            'progressPercent'
        ));
    }
}