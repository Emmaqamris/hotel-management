<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestController extends Controller
{
    // ─────────────────────────────────────────
    // LIST  /guests
    // ─────────────────────────────────────────

    public function index(Request $request): View
    {
        $hotelId     = $request->user('employee')->hotel_id;
        $search      = $request->get('search', '');
        $nationality = $request->get('nationality', '');
        $sort        = $request->get('sort', 'name');

        $guests = Guest::where('hotel_id', $hotelId)
            ->when($search, fn ($q) => $q->search($search))
            ->when($nationality, fn ($q) => $q->where('nationality', $nationality))
            ->withCount('bookings')
            ->withSum(
                [
                    'bookings as total_spent' => fn ($q) => $q->whereIn(
                        'status',
                        ['checked_in', 'checked_out']
                    ),
                ],
                'total_amount'
            )
            ->when(
                $sort === 'name',
                fn ($q) => $q->orderBy('first_name')->orderBy('last_name')
            )
            ->when($sort === 'recent', fn ($q) => $q->latest())
            ->when($sort === 'bookings', fn ($q) => $q->orderByDesc('bookings_count'))
            ->paginate(20)
            ->withQueryString();

        $nationalities = Guest::where('hotel_id', $hotelId)
            ->whereNotNull('nationality')
            ->distinct()
            ->orderBy('nationality')
            ->pluck('nationality');

        $stats = [
            'total' => Guest::where('hotel_id', $hotelId)->count(),

            'new_this_month' => Guest::where('hotel_id', $hotelId)
                ->whereMonth('created_at', now()->month)
                ->count(),

            'with_active_booking' => Guest::where('hotel_id', $hotelId)
                ->whereHas(
                    'bookings',
                    fn ($q) => $q->whereIn('status', ['confirmed', 'checked_in'])
                )
                ->count(),
        ];

        return view('guests.index', compact(
            'guests',
            'stats',
            'nationalities',
            'search',
            'nationality',
            'sort'
        ));
    }

    // ─────────────────────────────────────────
    // CREATE FORM  /guests/create
    // ─────────────────────────────────────────

    public function create(Request $request): View
    {
        $isQuick = $request->boolean('quick');

        return view('guests.create', compact('isQuick'));
    }

    // ─────────────────────────────────────────
    // STORE  POST /guests
    // ─────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['nullable', 'email', 'max:150'],
            'phone'         => ['required', 'string', 'max:20'],
            'id_type'       => ['required', 'in:passport,national_id,drivers_license'],
            'id_number'     => ['required', 'string', 'max:50'],
            'nationality'   => ['nullable', 'string', 'max:60'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address'       => ['nullable', 'string', 'max:500'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $data['hotel_id'] = $request->user('employee')->hotel_id;

        $existing = Guest::where('hotel_id', $data['hotel_id'])
            ->where('id_number', $data['id_number'])
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    "A guest with ID number {$data['id_number']} already exists: {$existing->full_name}."
                );
        }

        $guest = Guest::create($data);

        if ($request->boolean('quick')) {
            return redirect()
                ->route('bookings.create', ['guest_id' => $guest->id])
                ->with(
                    'success',
                    "{$guest->full_name} registered. You can now book for them."
                );
        }

        return redirect()
            ->route('guests.show', $guest)
            ->with('success', "{$guest->full_name} has been registered.");
    }

    // ─────────────────────────────────────────
    // SHOW  /guests/{guest}
    // ─────────────────────────────────────────

    public function show(Request $request, Guest $guest): View
    {
        $this->authorizeGuest($request, $guest);

        $bookings = Booking::where('guest_id', $guest->id)
            ->with(['room', 'employee'])
            ->latest()
            ->paginate(10);

        $stats = [
            'total_bookings' => $guest->bookings()->count(),

            'total_nights' => $guest->bookings()
                ->whereIn('status', ['checked_in', 'checked_out'])
                ->get()
                ->sum('nights'),

            'total_spent' => $guest->bookings()
                ->whereIn('status', ['checked_in', 'checked_out'])
                ->sum('total_amount'),

            'cancelled' => $guest->bookings()
                ->where('status', 'cancelled')
                ->count(),

            'last_visit' => $guest->bookings()
                ->where('status', 'checked_out')
                ->max('actual_checkout'),

            // FIXED: bookings.status instead of ambiguous status
            'favourite_type' => $guest->bookings()
                ->whereIn('bookings.status', ['checked_in', 'checked_out'])
                ->join('rooms', 'rooms.id', '=', 'bookings.room_id')
                ->selectRaw('rooms.type, COUNT(*) as cnt')
                ->groupBy('rooms.type')
                ->orderByDesc('cnt')
                ->value('type'),
        ];

        $activeBooking = $guest->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->with('room')
            ->latest()
            ->first();

        return view(
            'guests.show',
            compact('guest', 'bookings', 'stats', 'activeBooking')
        );
    }

    // ─────────────────────────────────────────
    // EDIT FORM  /guests/{guest}/edit
    // ─────────────────────────────────────────

    public function edit(Request $request, Guest $guest): View
    {
        $this->authorizeGuest($request, $guest);

        return view('guests.edit', compact('guest'));
    }

    // ─────────────────────────────────────────
    // UPDATE  PUT /guests/{guest}
    // ─────────────────────────────────────────

    public function update(Request $request, Guest $guest): RedirectResponse
    {
        $this->authorizeGuest($request, $guest);

        $data = $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['nullable', 'email', 'max:150'],
            'phone'         => ['required', 'string', 'max:20'],
            'id_type'       => ['required', 'in:passport,national_id,drivers_license'],
            'id_number'     => ['required', 'string', 'max:50'],
            'nationality'   => ['nullable', 'string', 'max:60'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address'       => ['nullable', 'string', 'max:500'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $guest->update($data);

        return redirect()
            ->route('guests.show', $guest)
            ->with('success', "{$guest->full_name} has been updated.");
    }

    // ─────────────────────────────────────────
    // SEARCH  GET /guests/search
    // ─────────────────────────────────────────

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $hotelId = $request->user('employee')->hotel_id;
        $term = $request->get('q');

        $guests = Guest::where('hotel_id', $hotelId)
            ->search($term)
            ->limit(10)
            ->get([
                'id',
                'first_name',
                'last_name',
                'phone',
                'email',
                'id_number',
            ]);

        return response()->json(
            $guests->map(function ($guest) {
                return [
                    'id' => $guest->id,
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'name' => $guest->full_name,
                    'phone' => $guest->phone,
                    'email' => $guest->email,
                    'id_number' => $guest->id_number,
                ];
            })
        );
    }

    // ─────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────

    private function authorizeGuest(Request $request, Guest $guest): void
    {
        if ($request->user('employee')->hotel_id !== $guest->hotel_id) {
            abort(403, 'You do not have access to this guest.');
        }
    }
}