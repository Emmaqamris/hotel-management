<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $hotelId = auth()->user()->hotel_id ?? 1;
        $status = $request->get('status');
        $search = $request->get('search');
        $date = $request->get('date');

        $query = Booking::where('hotel_id', $hotelId)
            ->with(['room', 'guest'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->search($search))
            ->when($date, fn($q) => $q->whereDate('checkin_date', $date));

        $bookings = $query->latest()->paginate(15);

        // Status summary for dashboard cards
        $summary = Booking::where('hotel_id', $hotelId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('bookings.index', compact('bookings', 'status', 'search', 'date', 'summary'));
    }

    public function create(Request $request)
{
    $hotelId = auth()->user()->hotel_id ?? 1;

    $checkin = $request->get(
        'checkin_date',
        today()->format('Y-m-d')
    );

    $checkout = $request->get(
        'checkout_date',
        today()->addDay()->format('Y-m-d')
    );

    $adults = $request->get('adults', 1);
    $type = $request->get('type', '');

    $availableRooms = $this->bookingService->searchAvailableRooms(
        $hotelId,
        $checkin,
        $checkout
    );

    if ($type) {
        $availableRooms = $availableRooms->where('type', $type);
    }

    $guests = Guest::where('hotel_id', $hotelId)
        ->orderBy('first_name')
        ->get();

    return view('bookings.create', compact(
        'availableRooms',
        'guests',
        'checkin',
        'checkout',
        'adults',
        'type'
    ));
}

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking(
                $request->validated(),
                auth()->user()->hotel_id ?? 1,
                auth()->id()
            );

            return redirect()->route('bookings.show', $booking)
                ->with('success', "Booking {$booking->booking_number} created successfully!");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Booking $booking)
    {
        if ($booking->hotel_id !== auth()->user()->hotel_id) abort(403);
        $booking->load(['room', 'guest']);
        return view('bookings.show', compact('booking'));
    }

    public function checkIn(Booking $booking)
    {
        try {
            $this->bookingService->checkIn($booking, auth()->id());
            return back()->with('success', 'Guest checked in successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function checkOut(Booking $booking)
    {
        try {
            $this->bookingService->checkOut($booking);
            return back()->with('success', 'Guest checked out successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, Booking $booking)
    {
        try {
            $this->bookingService->cancelBooking($booking, $request->reason ?? '');
            return back()->with('success', 'Booking cancelled.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}