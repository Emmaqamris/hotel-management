<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Collection;

class FrontDeskService
{
    public function getDailyOperations(int $hotelId, array $options = []): array
    {
        $today           = today();
        $upcomingLimit   = $options['upcoming_limit'] ?? null;
        $withInvoice     = (bool) ($options['with_invoice_on_departures'] ?? false);
        $includeRoomCounts = (bool) ($options['include_room_counts'] ?? false);

        $arrivals = Booking::where('hotel_id', $hotelId)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', $today)
            ->with(['guest', 'room'])
            ->orderBy('checkin_date')
            ->get();

        $departuresQuery = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->whereDate('checkout_date', $today)
            ->with(['guest', 'room']);

        if ($withInvoice) {
            $departuresQuery->with('invoice');
        }

        $departures = $departuresQuery
            ->orderBy('checkout_date')
            ->get();

        $inHouse = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->with(['guest', 'room'])
            ->orderBy('checkout_date')
            ->get();

        $upcomingQuery = Booking::where('hotel_id', $hotelId)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', '>', $today)
            ->whereDate('checkin_date', '<=', $today->copy()->addDays(7))
            ->with(['guest', 'room'])
            ->orderBy('checkin_date');

        if ($upcomingLimit !== null) {
            $upcomingQuery->limit($upcomingLimit);
        }

        $upcoming = $upcomingQuery->get();

        $overdueCheckouts = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->whereDate('checkout_date', '<', $today)
            ->with(['guest', 'room'])
            ->orderBy('checkout_date')
            ->get();

        $data = [
            'arrivals'         => $arrivals,
            'departures'       => $departures,
            'inHouse'          => $inHouse,
            'upcoming'         => $upcoming,
            'overdueCheckouts' => $overdueCheckouts,
        ];

        if ($includeRoomCounts) {
            $data['roomCounts'] = $this->getRoomCounts($hotelId);
        }

        return $data;
    }

    public function getRoomCounts(int $hotelId): array
    {
        $totalRooms = Room::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->count();

        $occupiedRooms = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->count();

        return [
            'total'     => $totalRooms,
            'occupied'  => $occupiedRooms,
            'available' => max(0, $totalRooms - $occupiedRooms),
        ];
    }

    public function getRoomBoardData(int $hotelId): array
    {
        $roomsByFloor = Room::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->groupBy('floor');

        $checkedInBookings = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->with('guest')
            ->get()
            ->keyBy('room_id');

        $arrivingTodayBookings = Booking::where('hotel_id', $hotelId)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', today())
            ->with('guest')
            ->get()
            ->keyBy('room_id');

        return [
            'roomsByFloor'          => $roomsByFloor,
            'checkedInBookings'     => $checkedInBookings,
            'arrivingTodayBookings' => $arrivingTodayBookings,
        ];
    }
}
