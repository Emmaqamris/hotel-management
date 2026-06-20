<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\HousekeepingLog;

class DashboardService
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly FrontDeskService $frontDeskService,
    ) {}

    public function managerDashboardData(Employee $user, int $hotelId): array
    {
        $stats = $this->reportService->getDashboardStats($hotelId);

        $recentBookings = Booking::where('hotel_id', $hotelId)
            ->with(['room', 'guest'])
            ->latest()
            ->limit(8)
            ->get();

        ['greeting' => $greeting, 'firstName' => $firstName] = $this->greetingFor($user);

        return [
            'stats'          => $stats,
            'recentBookings' => $recentBookings,
            'user'           => $user,
            'metricCards'    => $this->buildMetricCards($stats),
            'greeting'       => $greeting,
            'firstName'      => $firstName,
            'chartData'      => $this->encodeChartData($stats),
        ];
    }

    public function receptionistDashboardData(Employee $user, int $hotelId): array
    {
        $operations = $this->frontDeskService->getDailyOperations($hotelId, [
            'upcoming_limit'              => 5,
            'with_invoice_on_departures'  => true,
            'include_room_counts'         => true,
        ]);

        return [
            'user'             => $user,
            'arrivals'         => $operations['arrivals'],
            'departures'       => $operations['departures'],
            'inHouse'          => $operations['inHouse'],
            'upcoming'         => $operations['upcoming'],
            'overdueCheckouts' => $operations['overdueCheckouts'],
            'totalRooms'       => $operations['roomCounts']['total'],
            'occupiedRooms'    => $operations['roomCounts']['occupied'],
            'availableRooms'   => $operations['roomCounts']['available'],
        ];
    }

    public function housekeeperDashboardData(Employee $user): array
    {
        $today = today();

        $allTasks = HousekeepingLog::where('assigned_to', $user->id)
            ->whereDate('scheduled_at', $today)
            ->with(['room'])
            ->orderByRaw("
                CASE priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'normal' THEN 3
                    WHEN 'low' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('status')
            ->get();

        $pendingTasks   = $allTasks->whereIn('status', ['scheduled', 'in_progress']);
        $completedTasks = $allTasks->where('status', 'completed');
        $urgentTasks    = $allTasks
            ->where('priority', 'urgent')
            ->where('status', '!=', 'completed');

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

        return [
            'user'            => $user,
            'allTasks'        => $allTasks,
            'pendingTasks'    => $pendingTasks,
            'completedTasks'  => $completedTasks,
            'urgentTasks'     => $urgentTasks,
            'upcomingTasks'   => $upcomingTasks,
            'progressPercent' => $progressPercent,
        ];
    }

    public function greetingFor(Employee $user): array
    {
        $hour = now()->hour;

        $greeting = match (true) {
            $hour < 12 => 'Good Morning',
            $hour < 17 => 'Good Afternoon',
            default    => 'Good Evening',
        };

        return [
            'greeting'  => $greeting,
            'firstName' => explode(' ', trim($user->name))[0] ?: $user->name,
        ];
    }

    private function buildMetricCards(array $stats): array
    {
        return [
            [
                'label'     => 'Revenue MTD',
                'value'     => number_format($stats['revenueThisMonth'], 2),
                'sub'       => ($stats['revenueChange'] >= 0 ? '↑ ' : '↓ ')
                               . abs($stats['revenueChange']) . '% vs last month',
                'subColor'  => $stats['revenueChange'] >= 0 ? 'text-emerald-600' : 'text-red-500',
                'iconBg'    => '#d1fae5',
                'iconColor' => '#059669',
                'icon'      => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label'     => 'Occupancy Rate',
                'value'     => $stats['occupancyRate'] . '%',
                'sub'       => $stats['occupiedCount'] . ' / ' . $stats['totalRooms'] . ' rooms',
                'subColor'  => 'text-slate-400',
                'iconBg'    => '#dbeafe',
                'iconColor' => '#2563eb',
                'icon'      => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5',
            ],
            [
                'label'     => 'Active Bookings',
                'value'     => $stats['activeBookings'],
                'sub'       => 'confirmed + in house',
                'subColor'  => 'text-slate-400',
                'iconBg'    => '#fef3c7',
                'iconColor' => '#d97706',
                'icon'      => 'M8 7V3m8 4V3m-9 8h10',
            ],
            [
                'label'     => 'ADR',
                'value'     => number_format($stats['adr'], 2),
                'sub'       => 'avg daily rate',
                'subColor'  => 'text-slate-400',
                'iconBg'    => '#ede9fe',
                'iconColor' => '#7c3aed',
                'icon'      => 'M9 19v-6a2 2 0 00-2-2H5',
            ],
            [
                'label'     => 'Arrivals Today',
                'value'     => $stats['arrivingToday'],
                'sub'       => 'guests checking in',
                'subColor'  => 'text-slate-400',
                'iconBg'    => '#d1fae5',
                'iconColor' => '#059669',
                'icon'      => 'M11 16l-4-4m0 0l4-4',
            ],
            [
                'label'     => 'Departures Today',
                'value'     => $stats['departingToday'],
                'sub'       => 'guests checking out',
                'subColor'  => 'text-slate-400',
                'iconBg'    => '#fee2e2',
                'iconColor' => '#dc2626',
                'icon'      => 'M17 16l4-4m0 0l-4-4',
            ],
        ];
    }

    private function encodeChartData(array $stats): string
    {
        return json_encode([
            'dailyRevenue'   => $stats['daily30Revenue'] ?? [],
            'dailyBookings'  => $stats['daily30Bookings'] ?? [],
            'monthlyRevenue' => $stats['monthlyRevenue'] ?? [],
            'roomStatus'     => $stats['roomStatus'] ?? [],
        ]);
    }
}
