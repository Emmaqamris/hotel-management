<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function dashboard(Request $request): JsonResponse
    {
        $stats = $this->reportService->getDashboardStats($request->user()->hotel_id);
        return response()->json(['data' => $stats]);
    }

    public function occupancy(Request $request): JsonResponse
    {
        $from  = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to    = Carbon::parse($request->get('to', now()->endOfMonth()));
        $data  = $this->reportService->getOccupancyReport($request->user()->hotel_id, $from, $to);
        return response()->json(['data' => $data]);
    }

    public function revenue(Request $request): JsonResponse
    {
        $from  = Carbon::parse($request->get('from', now()->startOfMonth()));
        $to    = Carbon::parse($request->get('to', now()->endOfMonth()));
        $data  = $this->reportService->getRevenueReport($request->user()->hotel_id, $from, $to);
        return response()->json(['data' => $data]);
    }
}