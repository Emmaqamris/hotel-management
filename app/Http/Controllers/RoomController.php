<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $hotelId = auth('employee')->user()->hotel_id;
        $search  = $request->input('search', '');
        $type    = $request->input('type', '');
        $status  = $request->input('status', '');
        $floor   = $request->input('floor', '');

        $floors = Room::where('hotel_id', $hotelId)
            ->whereNotNull('floor')
            ->distinct()
            ->orderBy('floor')
            ->pluck('floor');

        $rooms = Room::where('hotel_id', $hotelId)
            ->when($search, fn($q) => $q->where('number', 'like', "%{$search}%"))
            ->when($type,   fn($q) => $q->where('type', $type))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($floor,  fn($q) => $q->where('floor', $floor))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('rooms.index', compact('rooms', 'search', 'type', 'status', 'floor', 'floors'));
    }

    public function create()
    {
        abort_unless(
            in_array(auth('employee')->user()->role, ['manager', 'admin']),
            403
        );

        return view('rooms.create');
    }

    public function store(StoreRoomRequest $request)
    {
        abort_unless(
            in_array(auth('employee')->user()->role, ['manager', 'admin']),
            403
        );

        $data = $request->validated();

        $data['hotel_id'] = auth('employee')->user()->hotel_id;
        $data['status'] = 'available';
        $data['is_active'] = true;
        $data['amenities'] = $data['amenities'] ?? [];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('rooms', 'public');
        }

        Room::create($data);

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        abort_unless(
            in_array(auth('employee')->user()->role, ['manager', 'admin']),
            403
        );

        return view('rooms.edit', compact('room'));
    }

    public function update(StoreRoomRequest $request, Room $room)
    {
        abort_unless(
            in_array(auth('employee')->user()->role, ['manager', 'admin']),
            403
        );

        $data = $request->validated();

        $data['amenities'] = $data['amenities'] ?? [];

        if ($request->hasFile('image')) {

            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }

            $data['image'] = $request->file('image')
                ->store('rooms', 'public');
        }

        $room->update($data);

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        abort_unless(
            in_array(auth('employee')->user()->role, ['manager', 'admin']),
            403
        );

        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }

        $room->delete();

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }

    public function updateStatus(Request $request, Room $room)
    {
        $request->validate([
            'status' => 'required|in:available,maintenance',
        ]);

        $room->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Room status updated.');
    }

    public function toggleActive(Room $room)
    {
        $room->update([
            'is_active' => !$room->is_active,
        ]);

        return back()->with('success', 'Room status updated.');
    }
}