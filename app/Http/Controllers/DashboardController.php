<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $user    = $request->user('employee');
        $hotelId = $user->hotel_id;

        return match (true) {
            $user->isHousekeeper()  => view(
                'dashboard.housekeeper',
                $this->dashboardService->housekeeperDashboardData($user)
            ),
            $user->isReceptionist() => view(
                'dashboard.receptionist',
                $this->dashboardService->receptionistDashboardData($user, $hotelId)
            ),
            default => view(
                'dashboard.manager',
                $this->dashboardService->managerDashboardData($user, $hotelId)
            ),
        };
    }
}
