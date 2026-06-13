<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponds;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponds;

    public function __construct(
        private readonly ReportService $reportService
    ) {}

    // GET /api/dashboard
    public function index(Request $request): JsonResponse
    {
        $stats = $this->reportService->getDashboardStats(
            $request->user()->hotel_id
        );

        return $this->ok($stats);
    }

    // GET /api/reports/revenue?from=&to=
    public function revenue(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole(['admin', 'manager'])) {
            return $this->forbidden();
        }

        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->get('to',   now()->endOfMonth()));

        $data = $this->reportService->getRevenueReport(
            $request->user()->hotel_id, $from, $to
        );

        return $this->ok($data);
    }

    // GET /api/reports/occupancy?from=&to=
    public function occupancy(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole(['admin', 'manager'])) {
            return $this->forbidden();
        }

        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->get('to',   now()->endOfMonth()));

        $data = $this->reportService->getOccupancyReport(
            $request->user()->hotel_id, $from, $to
        );

        return $this->ok($data);
    }
}