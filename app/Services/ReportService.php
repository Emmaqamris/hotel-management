<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\HousekeepingLog;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // ─────────────────────────────────────────────
    // DASHBOARD STATS
    // ─────────────────────────────────────────────
    public function getDashboardStats(int $hotelId): array
    {
        $today = today();
        $now   = now();

        $totalRooms = Room::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->count();

        $occupiedCount = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->count();

        $occupancyRate = $totalRooms > 0
            ? round(($occupiedCount / $totalRooms) * 100, 1)
            : 0;

        $arrivingToday = Booking::where('hotel_id', $hotelId)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', $today)
            ->count();

        $departingToday = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->whereDate('checkout_date', $today)
            ->count();

        $overdueCheckouts = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->whereDate('checkout_date', '<', $today)
            ->count();

        $revenueThisMonth = $this->sumPayments(
            $hotelId,
            $now->copy()->startOfMonth(),
            $now->copy()->endOfMonth()
        );

        $revenueLastMonth = $this->sumPayments(
            $hotelId,
            $now->copy()->subMonth()->startOfMonth(),
            $now->copy()->subMonth()->endOfMonth()
        );

        $activeBookings = Booking::where('hotel_id', $hotelId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        $pendingHousekeeping = HousekeepingLog::where('hotel_id', $hotelId)
            ->where('status', 'scheduled')
            ->count();
// ADR = Average Daily Rate
        $adr = $occupiedCount > 0
    ? round($revenueThisMonth / $occupiedCount, 2)
    : 0;

// RevPAR = Revenue Per Available Room
        $revpar = $totalRooms > 0
    ? round($revenueThisMonth / $totalRooms, 2)
    : 0;

        $revenueChange = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0;

            $revenueToday = $this->sumPayments(
    $hotelId,
    $today->copy()->startOfDay(),
    $today->copy()->endOfDay()
);

$revenueThisYear = $this->sumPayments(
    $hotelId,
    $now->copy()->startOfYear(),
    $now->copy()->endOfYear()
);
$daily30Revenue = collect();
$daily30Bookings = collect();
$monthlyRevenue = collect();
$roomStatus = [
    'available' => 0,
    'occupied' => 0,
    'cleaning' => 0,
    'maintenance' => 0,
];


        return compact(
    'totalRooms',
    'occupiedCount',
    'occupancyRate',
    'arrivingToday',
    'departingToday',
    'overdueCheckouts',
    'activeBookings',
    'pendingHousekeeping',
    'revenueThisMonth',
    'revenueLastMonth',
    'revenueChange',
    'revenueToday',
    'revenueThisYear',
    'adr',
    'revpar',
    'daily30Revenue',
    'daily30Bookings',
    'monthlyRevenue',
    'roomStatus',
);
    }

    // ─────────────────────────────────────────────
    // BOOKINGS REPORT
    // ─────────────────────────────────────────────
    public function getBookingsReport(int $hotelId, Carbon $from, Carbon $to): array
{
    $bookings = Booking::where('bookings.hotel_id', $hotelId)
        ->whereBetween(DB::raw('DATE(bookings.created_at)'), [
            $from->format('Y-m-d'),
            $to->format('Y-m-d'),
        ])
        ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = "checked_in" THEN 1 ELSE 0 END) as checked_in,
            SUM(CASE WHEN status = "checked_out" THEN 1 ELSE 0 END) as checked_out,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = "no_show" THEN 1 ELSE 0 END) as no_show,
            SUM(total_amount) as total_value,
            AVG(DATEDIFF(checkout_date, checkin_date)) as avg_stay_length
        ')
        ->first();

    $dailyCount = Booking::where('hotel_id', $hotelId)
        ->whereBetween(DB::raw('DATE(created_at)'), [
            $from->format('Y-m-d'),
            $to->format('Y-m-d'),
        ])
        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->groupBy('date')
        ->pluck('count', 'date');

    $dailyData = [];
    $period = CarbonPeriod::create($from, $to);

    foreach ($period as $date) {
        $key = $date->format('Y-m-d');

        $dailyData[] = [
            'date'  => $key,
            'label' => $date->format('d M'),
            'count' => (int) ($dailyCount[$key] ?? 0),
        ];
    }

    $byRoomType = Booking::where('bookings.hotel_id', $hotelId)
        ->join('rooms', 'rooms.id', '=', 'bookings.room_id')
        ->whereBetween(DB::raw('DATE(bookings.created_at)'), [
            $from->format('Y-m-d'),
            $to->format('Y-m-d'),
        ])
        ->selectRaw('rooms.type, COUNT(*) as count, SUM(bookings.total_amount) as total')
        ->groupBy('rooms.type')
        ->get();

    $cancellationRate = $bookings->total > 0
        ? round(($bookings->cancelled / $bookings->total) * 100, 1)
        : 0;

    return [
        'from'              => $from->format('Y-m-d'),
        'to'                => $to->format('Y-m-d'),
        'total'             => (int) ($bookings->total ?? 0),
        'confirmed'         => (int) ($bookings->confirmed ?? 0),
        'checked_in'        => (int) ($bookings->checked_in ?? 0),
        'checked_out'       => (int) ($bookings->checked_out ?? 0),
        'cancelled'         => (int) ($bookings->cancelled ?? 0),
        'no_show'           => (int) ($bookings->no_show ?? 0),
        'cancellation_rate' => $cancellationRate,
        'total_value'       => (float) ($bookings->total_value ?? 0),
        'avg_stay_length'   => round((float) ($bookings->avg_stay_length ?? 0), 1),
        'daily_data'        => $dailyData,
        'by_room_type'      => $byRoomType,
        'by_source'         => collect(), // prevents Blade error
    ];
}

    // ─────────────────────────────────────────────
// OCCUPANCY REPORT
// ─────────────────────────────────────────────
public function getOccupancyReport(int $hotelId, Carbon $from, Carbon $to): array
{
    $totalRooms = Room::where('hotel_id', $hotelId)
        ->where('is_active', true)
        ->count();

    $dailyData = [];
    $totalOccupiedNights = 0;

    foreach (CarbonPeriod::create($from, $to) as $date) {

        $occupied = Booking::where('hotel_id', $hotelId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereDate('checkin_date', '<=', $date->format('Y-m-d'))
            ->whereDate('checkout_date', '>', $date->format('Y-m-d'))
            ->count();

        $totalOccupiedNights += $occupied;

        $rate = $totalRooms > 0
            ? round(($occupied / $totalRooms) * 100, 1)
            : 0;

        $dailyData[] = [
            'date'     => $date->format('Y-m-d'),
            'occupied' => $occupied,
            'rate'     => $rate,
        ];
    }

    $totalDays = count($dailyData);

    $totalNights = $totalRooms * $totalDays;

    $byRoomType = Room::where('hotel_id', $hotelId)
        ->selectRaw('type, COUNT(*) as total_rooms')
        ->groupBy('type')
        ->get();

    return [
        'from' => $from->format('Y-m-d'),
        'to'   => $to->format('Y-m-d'),

        'daily_data' => $dailyData,

        'avg_occupancy_rate' => $totalDays > 0
            ? round(collect($dailyData)->avg('rate'), 1)
            : 0,

        'total_occupied_nights' => $totalOccupiedNights,

        'total_nights' => $totalNights,

        'total_rooms' => $totalRooms,

        'total_days' => $totalDays,

        'by_room_type' => $byRoomType,
    ];
}

// ─────────────────────────────────────────────
// REVENUE REPORT
// ─────────────────────────────────────────────
public function getRevenueReport(int $hotelId, Carbon $from, Carbon $to): array
{
    $payments = Payment::where('hotel_id', $hotelId)
        ->where('status', 'completed')
        ->whereBetween(DB::raw('DATE(paid_at)'), [
            $from->format('Y-m-d'),
            $to->format('Y-m-d'),
        ])
        ->get();

    $dailyData = [];

    foreach (CarbonPeriod::create($from, $to) as $date) {

        $dayPayments = $payments->filter(function ($payment) use ($date) {
            return Carbon::parse($payment->paid_at)->format('Y-m-d')
                === $date->format('Y-m-d');
        });

        $dailyData[] = [
            'date'    => $date->format('Y-m-d'),
            'revenue' => (float) $dayPayments->sum('amount'),
            'count'   => $dayPayments->count(),
        ];
    }

    $totalRevenue = (float) $payments->sum('amount');
    $totalPayments = $payments->count();

    $totalDays = max(1, count($dailyData));

    $avgDailyRevenue = round($totalRevenue / $totalDays, 2);

    $totalRooms = Room::where('hotel_id', $hotelId)
        ->where('is_active', true)
        ->count();

    $adr = $totalPayments > 0
        ? round($totalRevenue / $totalPayments, 2)
        : 0;

    $revpar = ($totalRooms > 0 && $totalDays > 0)
        ? round($totalRevenue / ($totalRooms * $totalDays), 2)
        : 0;

    $byMethod = $payments
        ->groupBy('method')
        ->map(function ($group, $method) {
            return [
                'method' => $method,
                'count'  => $group->count(),
                'total'  => (float) $group->sum('amount'),
            ];
        })
        ->values()
        ->toArray();

    return [
        'from' => $from->format('Y-m-d'),
        'to'   => $to->format('Y-m-d'),

        'daily_data' => $dailyData,

        'total_revenue' => $totalRevenue,
        'total_payments' => $totalPayments,

        'avg_daily_revenue' => $avgDailyRevenue,

        'adr' => $adr,

        'revpar' => $revpar,

        'by_method' => $byMethod,
    ];
}
    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    private function sumPayments(int $hotelId, Carbon $from, Carbon $to): float
    {
        return (float) Payment::where('hotel_id', $hotelId)
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(paid_at)'), [
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            ])
            ->sum('amount');
    }
}