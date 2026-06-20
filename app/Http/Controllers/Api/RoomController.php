<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Http\Traits\ApiResponds;
use App\Models\Room;
use App\Services\BookingService;
use App\Support\BookingDateRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    use ApiResponds;

    public function __construct(
        private readonly BookingService $bookingService
    ) {}

    // GET /api/rooms
    public function index(Request $request): JsonResponse
    {
        $hotelId = $request->user()->hotel_id;

        $rooms = Room::where('hotel_id', $hotelId)
            ->when($request->type,   fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->floor,  fn($q) => $q->where('floor', $request->floor))
            ->orderBy('floor')
            ->orderBy('number')
            ->paginate($request->get('per_page', 20));

        return $this->ok(
            RoomResource::collection($rooms)->response()->getData(true)
        );
    }

    // GET /api/rooms/available?checkin_date=&checkout_date=&adults=
    public function available(Request $request): JsonResponse
    {
        $request->validate(
            array_merge(BookingDateRules::rules(), [
                'adults' => ['nullable', 'integer', 'min:1', 'max:10'],
                'type'   => ['nullable', 'string'],
            ]),
            BookingDateRules::messages()
        );

        BookingDateRules::assertValidStay(
            $request->checkin_date,
            $request->checkout_date
        );

        $rooms = $this->bookingService->searchAvailableRooms(
            $request->user()->hotel_id,
            $request->checkin_date,
            $request->checkout_date,
            $request->type,
            $request->adults
        );

        return $this->ok([
            'rooms'         => RoomResource::collection($rooms),
            'checkin_date'  => $request->checkin_date,
            'checkout_date' => $request->checkout_date,
            'count'         => $rooms->count(),
        ]);
    }

    // GET /api/rooms/{room}
    public function show(Request $request, Room $room): JsonResponse
    {
        if ($room->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        return $this->ok(new RoomResource($room));
    }

    // POST /api/rooms
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole(['admin', 'manager'])) {
            return $this->forbidden('Only managers can create rooms.');
        }

        $data = $request->validate([
            'number'          => [
                'required', 'string', 'max:10',
                Rule::unique('rooms', 'number')
                    ->where('hotel_id', $request->user()->hotel_id),
            ],
            'type'            => ['required', Rule::in(['standard','deluxe','family_suite','business_suite'])],
            'floor'           => ['required', 'integer', 'min:1'],
            'capacity'        => ['required', 'integer', 'min:1', 'max:10'],
            'price_per_night' => ['required', 'numeric', 'min:1'],
            'description'     => ['nullable', 'string'],
            'amenities'       => ['nullable', 'array'],
        ]);

        $data['hotel_id'] = $request->user()->hotel_id;
        $room = Room::create($data);

        return $this->created(new RoomResource($room), 'Room created successfully');
    }

    // PUT /api/rooms/{room}
    public function update(Request $request, Room $room): JsonResponse
    {
        if ($room->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        if (!$request->user()->hasRole(['admin', 'manager'])) {
            return $this->forbidden('Only managers can update rooms.');
        }

        $data = $request->validate([
            'number'          => [
                'sometimes', 'string', 'max:10',
                Rule::unique('rooms', 'number')
                    ->where('hotel_id', $room->hotel_id)
                    ->ignore($room->id),
            ],
            'type'            => ['sometimes', Rule::in(['standard','deluxe','family_suite','business_suite'])],
            'floor'           => ['sometimes', 'integer', 'min:1'],
            'capacity'        => ['sometimes', 'integer', 'min:1', 'max:10'],
            'price_per_night' => ['sometimes', 'numeric', 'min:1'],
            'description'     => ['nullable', 'string'],
            'amenities'       => ['nullable', 'array'],
            'status'          => ['sometimes', Rule::in(['available', 'maintenance'])],
        ]);

        $room->update($data);

        return $this->ok(new RoomResource($room), 'Room updated successfully');
    }

    // DELETE /api/rooms/{room}
    public function destroy(Request $request, Room $room): JsonResponse
    {
        if ($room->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        if (!$request->user()->hasRole(['admin', 'manager'])) {
            return $this->forbidden();
        }

        $active = $room->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        if ($active > 0) {
            return $this->error("Room {$room->number} has {$active} active booking(s) and cannot be deleted.");
        }

        $room->delete();

        return $this->ok(null, "Room {$room->number} deleted successfully");
    }
}