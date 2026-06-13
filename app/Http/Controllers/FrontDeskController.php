<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontDeskController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {}

    // ─────────────────────────────────────────
    // MAIN FRONT DESK VIEW  /front-desk
    // ─────────────────────────────────────────

    public function index(Request $request): View
    {
        $hotelId = $request->user('employee')->hotel_id;

        // Confirmed bookings checking in today
        $arrivalsToday = Booking::where('hotel_id', $hotelId)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', today())
            ->with(['guest', 'room'])
            ->orderBy('created_at')
            ->get();

        // Checked-in guests departing today
        $departuresToday = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->whereDate('checkout_date', today())
            ->with(['guest', 'room'])
            ->orderBy('checkout_date')
            ->get();

        // Checked-in guests whose checkout_date has already passed
        $overdueCheckouts = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->whereDate('checkout_date', '<', today())
            ->with(['guest', 'room'])
            ->orderBy('checkout_date')
            ->get();

        // All guests currently in house
        $inHouseGuests = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->with(['guest', 'room'])
            ->orderBy('checkout_date')
            ->get();

        // Confirmed arrivals in the next 7 days (excluding today)
        $upcomingArrivals = Booking::where('hotel_id', $hotelId)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', '>', today())
            ->whereDate('checkin_date', '<=', today()->addDays(7))
            ->with(['guest', 'room'])
            ->orderBy('checkin_date')
            ->get();

        $stats = [
            'arrivals_today'   => $arrivalsToday->count(),
            'departures_today' => $departuresToday->count(),
            'in_house'         => $inHouseGuests->count(),
            'overdue'          => $overdueCheckouts->count(),
        ];

        return view('front-desk.index', compact(
            'arrivalsToday',
            'departuresToday',
            'overdueCheckouts',
            'inHouseGuests',
            'upcomingArrivals',
            'stats'
        ));
    }

    // ─────────────────────────────────────────
    // ROOM STATUS BOARD  /front-desk/room-board
    // ─────────────────────────────────────────

    public function roomBoard(Request $request): View
    {
        $hotelId = $request->user('employee')->hotel_id;

        // All active rooms for this hotel, grouped by floor
        $roomsByFloor = Room::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->groupBy('floor');

        // Currently checked-in bookings, keyed by room_id
        $checkedInBookings = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->with('guest')
            ->get()
            ->keyBy('room_id');

        // Confirmed bookings arriving today, keyed by room_id
        $arrivingTodayBookings = Booking::where('hotel_id', $hotelId)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', today())
            ->with('guest')
            ->get()
            ->keyBy('room_id');

        return view('front-desk.room-board', compact(
            'roomsByFloor',
            'checkedInBookings',
            'arrivingTodayBookings'
        ));
    }
}