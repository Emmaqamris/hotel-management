<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Traits\ApiResponds;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    use ApiResponds;

    // ─────────────────────────────────────────
    // GET /api/bookings
    // ─────────────────────────────────────────

    public function index(Request $request): JsonResponse
{
    $hotelId = $request->user()->hotel_id;

    $bookings = Booking::with(['room', 'guest'])
        ->where('hotel_id', $hotelId)
        ->latest()
        ->paginate(15);

    return response()->json([
        'success' => true,
        'data' => [
            'data' => BookingResource::collection($bookings->items()),
            'current_page' => $bookings->currentPage(),
            'total' => $bookings->total(),
        ],
    ]);
}

    // ─────────────────────────────────────────
    // POST /api/bookings
    // ─────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'room_id'       => ['required', 'exists:rooms,id'],
            'guest_id'      => ['required', 'exists:guests,id'],
            'checkin_date'  => ['required', 'date'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
            'adults'        => ['required', 'integer', 'min:1'],
        ]);

        $hotelId = $request->user()->hotel_id;

        $room = Room::where('hotel_id', $hotelId)
            ->findOrFail($data['room_id']);

        $overlappingBooking = Booking::where('room_id', $room->id)
            ->whereNotIn('status', ['cancelled', 'checked_out'])
            ->where(function ($query) use ($data) {
                $query
                    ->whereBetween('checkin_date', [
                        $data['checkin_date'],
                        $data['checkout_date'],
                    ])
                    ->orWhereBetween('checkout_date', [
                        $data['checkin_date'],
                        $data['checkout_date'],
                    ])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('checkin_date', '<=', $data['checkin_date'])
                          ->where('checkout_date', '>=', $data['checkout_date']);
                    });
            })
            ->exists();

        if ($overlappingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Room is not available for the selected dates.',
            ], 422);
        }

        $nights = max(
            1,
            now()->parse($data['checkin_date'])
                ->diffInDays(now()->parse($data['checkout_date']))
        );

        $booking = Booking::create([
            'booking_number' => 'BK-' . strtoupper(Str::random(8)),
            'hotel_id'       => $hotelId,
            'room_id'        => $room->id,
            'guest_id'       => $data['guest_id'],
            'checkin_date'   => $data['checkin_date'],
            'checkout_date'  => $data['checkout_date'],
            'status'         => 'confirmed',
            'adults'         => $data['adults'],
            'room_rate'      => $room->price_per_night,
            'total_amount'   => $room->price_per_night * $nights,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully.',
            'data'    => new BookingResource($booking),
        ], 201);
    }

    // ─────────────────────────────────────────
    // GET /api/bookings/{booking}
    // ─────────────────────────────────────────

    public function show(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        $booking->load(['room', 'guest']);

        return $this->ok(new BookingResource($booking));
    }

    // ─────────────────────────────────────────
    // POST /api/bookings/{booking}/check-in
    // ─────────────────────────────────────────

    public function checkIn(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        $booking->update([
            'status' => 'checked_in',
            'actual_checkin' => now(),
        ]);

        return $this->ok(new BookingResource($booking->fresh()));
    }

    // ─────────────────────────────────────────
    // POST /api/bookings/{booking}/check-out
    // ─────────────────────────────────────────

    public function checkOut(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        $booking->update([
            'status' => 'checked_out',
            'actual_checkout' => now(),
        ]);

        return $this->ok(new BookingResource($booking->fresh()));
    }

    // ─────────────────────────────────────────
    // POST /api/bookings/{booking}/cancel
    // ─────────────────────────────────────────

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->input('reason'),
        ]);

        return $this->ok(new BookingResource($booking->fresh()));
    }
}