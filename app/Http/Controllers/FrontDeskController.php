<?php

namespace App\Http\Controllers;

use App\Services\FrontDeskService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontDeskController extends Controller
{
    public function __construct(
        private readonly FrontDeskService $frontDeskService
    ) {}

    public function index(Request $request): View
    {
        $hotelId    = $request->user('employee')->hotel_id;
        $operations = $this->frontDeskService->getDailyOperations($hotelId);

        $stats = [
            'arrivals_today'   => $operations['arrivals']->count(),
            'departures_today' => $operations['departures']->count(),
            'in_house'         => $operations['inHouse']->count(),
            'overdue'          => $operations['overdueCheckouts']->count(),
        ];

        return view('front-desk.index', [
            'arrivalsToday'    => $operations['arrivals'],
            'departuresToday'  => $operations['departures'],
            'overdueCheckouts' => $operations['overdueCheckouts'],
            'inHouseGuests'    => $operations['inHouse'],
            'upcomingArrivals' => $operations['upcoming'],
            'stats'            => $stats,
        ]);
    }

    public function roomBoard(Request $request): View
    {
        $hotelId = $request->user('employee')->hotel_id;

        return view(
            'front-desk.room-board',
            $this->frontDeskService->getRoomBoardData($hotelId)
        );
    }
}
