<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\GuestResource;
use App\Http\Traits\ApiResponds;
use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    use ApiResponds;

    // GET /api/guests
    public function index(Request $request): JsonResponse
    {
        $hotelId = $request->user()->hotel_id;

        $guests = Guest::where('hotel_id', $hotelId)
            ->when($request->search, fn($q) => $q->search($request->search))
            ->withCount('bookings')
            ->orderBy('first_name')
            ->paginate($request->get('per_page', 20));

        return $this->ok(
            GuestResource::collection($guests)->response()->getData(true)
        );
    }

    // GET /api/guests/search?q=Alice
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);

        $guests = Guest::where('hotel_id', $request->user()->hotel_id)
            ->search($request->q)
            ->limit(15)
            ->get();

        return $this->ok(GuestResource::collection($guests));
    }

    // POST /api/guests
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'phone'         => ['required', 'string', 'max:20'],
            'email'         => ['nullable', 'email', 'max:150'],
            'id_type'       => ['required', 'in:passport,national_id,drivers_license'],
            'id_number'     => ['required', 'string', 'max:50'],
            'nationality'   => ['nullable', 'string', 'max:60'],
            'date_of_birth' => ['nullable', 'date'],
            'address'       => ['nullable', 'string', 'max:500'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $data['hotel_id'] = $request->user()->hotel_id;

        $existing = Guest::where('hotel_id', $data['hotel_id'])
            ->where('id_number', $data['id_number'])
            ->first();

        if ($existing) {
            return $this->error(
                "A guest with ID number {$data['id_number']} already exists: {$existing->full_name}."
            );
        }

        $guest = Guest::create($data);

        return $this->created(new GuestResource($guest), 'Guest registered successfully');
    }

    // GET /api/guests/{guest}
    public function show(Request $request, Guest $guest): JsonResponse
    {
        if ($guest->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        $guest->load('bookings.room');

        return $this->ok(new GuestResource($guest));
    }

    // GET /api/guests/{guest}/bookings
    public function bookings(Request $request, Guest $guest): JsonResponse
    {
        if ($guest->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        $bookings = $guest->bookings()
            ->with(['room'])
            ->latest()
            ->paginate($request->get('per_page', 10));

        return $this->ok(
            BookingResource::collection($bookings)->response()->getData(true)
        );
    }
}