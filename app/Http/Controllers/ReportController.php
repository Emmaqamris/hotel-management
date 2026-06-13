<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    // ─────────────────────────────────────────
    // REPORT HUB  /reports
    // ─────────────────────────────────────────

    public function index(Request $request): View
    {
        $hotelId = $request->user('employee')->hotel_id;
        $stats   = $this->reportService->getDashboardStats($hotelId);
        return view('reports.index', compact('stats'));
    }

    // ─────────────────────────────────────────
    // OCCUPANCY REPORT  /reports/occupancy
    // ─────────────────────────────────────────

    public function occupancy(Request $request): View
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->get('to',   now()->endOfMonth()));

        // Cap range to 365 days to prevent overloading
        if ($from->diffInDays($to) > 365) {
            $to = $from->copy()->addDays(365);
        }

        $hotelId = $request->user('employee')->hotel_id;
        $data    = $this->reportService->getOccupancyReport($hotelId, $from, $to);

        return view('reports.occupancy', compact('data', 'from', 'to'));
    }

    // ─────────────────────────────────────────
    // REVENUE REPORT  /reports/revenue
    // ─────────────────────────────────────────

    public function revenue(Request $request): View
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->get('to',   now()->endOfMonth()));

        if ($from->diffInDays($to) > 365) {
            $to = $from->copy()->addDays(365);
        }

        $hotelId = $request->user('employee')->hotel_id;
        $data    = $this->reportService->getRevenueReport($hotelId, $from, $to);

        return view('reports.revenue', compact('data', 'from', 'to'));
    }

    // ─────────────────────────────────────────
    // BOOKINGS REPORT  /reports/bookings
    // ─────────────────────────────────────────

    public function bookings(Request $request): View
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->get('to',   now()->endOfMonth()));

        if ($from->diffInDays($to) > 365) {
            $to = $from->copy()->addDays(365);
        }

        $hotelId = $request->user('employee')->hotel_id;
        $data    = $this->reportService->getBookingsReport($hotelId, $from, $to);

        return view('reports.bookings', compact('data', 'from', 'to'));
    }

    // ─────────────────────────────────────────
    // CSV EXPORTS
    // ─────────────────────────────────────────

    public function exportRevenue(Request $request): StreamedResponse
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->get('to',   now()->endOfMonth()));

        $hotelId = $request->user('employee')->hotel_id;
        $data    = $this->reportService->getRevenueReport($hotelId, $from, $to);

        $filename = "revenue-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Revenue', 'Transactions']);
            foreach ($data['daily_data'] as $row) {
                fputcsv($handle, [$row['date'], $row['revenue'], $row['count']]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Total', $data['total_revenue'], $data['total_payments']]);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportOccupancy(Request $request): StreamedResponse
    {
        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->get('to',   now()->endOfMonth()));

        $hotelId  = $request->user('employee')->hotel_id;
        $data     = $this->reportService->getOccupancyReport($hotelId, $from, $to);
        $filename = "occupancy-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Rooms Occupied', 'Occupancy Rate (%)']);
            foreach ($data['daily_data'] as $row) {
                fputcsv($handle, [$row['date'], $row['occupied'], $row['rate']]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Average Occupancy Rate', $data['avg_occupancy_rate'] . '%', '']);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}