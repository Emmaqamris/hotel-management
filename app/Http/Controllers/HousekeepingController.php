<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingLog;
use App\Models\Room;
use App\Models\Employee;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth('employee')->user();

        $query = HousekeepingLog::with(['room', 'assignedEmployee', 'booking'])
            ->where('hotel_id', $employee->hotel_id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type,   fn($q) => $q->where('type', $request->type))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->date,   fn($q) => $q->whereDate('scheduled_at', $request->date))
            ->when($employee->isHousekeeper(), fn($q) => $q->where('assigned_to', $employee->id))
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderBy('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        $housekeepers = Employee::where('hotel_id', $employee->hotel_id)
            ->where('role', 'housekeeper')
            ->where('is_active', true)
            ->get();

        $stats = HousekeepingLog::where('hotel_id', $employee->hotel_id)
            ->whereDate('scheduled_at', today())
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('housekeeping.index', compact('query', 'housekeepers', 'stats'));
    }

    public function create(Request $request)
    {
        $employee = auth('employee')->user();

        $rooms = Room::where('hotel_id', $employee->hotel_id)
            ->where('is_active', true)
            ->orderBy('number')
            ->get();

        $housekeepers = Employee::where('hotel_id', $employee->hotel_id)
            ->where('role', 'housekeeper')
            ->where('is_active', true)
            ->get();

        $selectedRoom = $request->room_id
            ? Room::find($request->room_id)
            : null;

        return view('housekeeping.create', compact('rooms', 'housekeepers', 'selectedRoom'));
    }

    public function store(Request $request)
    {
        $employee = auth('employee')->user();

        $data = $request->validate([
            'room_id'      => 'required|exists:rooms,id',
            'assigned_to'  => 'nullable|exists:employees,id',
            'booking_id'   => 'nullable|exists:bookings,id',
            'type'         => 'required|in:cleaning,inspection,maintenance,turndown',
            'priority'     => 'required|in:low,normal,high,urgent',
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $data['hotel_id'] = $employee->hotel_id;

        HousekeepingLog::create($data);

        return redirect()->route('housekeeping.index')
            ->with('success', 'Task scheduled successfully.');
    }

    public function show(HousekeepingLog $housekeeping)
    {
        $housekeeping->load(['room', 'assignedEmployee', 'booking.guest']);
        return view('housekeeping.show', compact('housekeeping'));
    }

    public function edit(HousekeepingLog $housekeeping)
    {
        $employee = auth('employee')->user();

        $housekeepers = Employee::where('hotel_id', $employee->hotel_id)
            ->where('role', 'housekeeper')
            ->where('is_active', true)
            ->get();

        $rooms = Room::where('hotel_id', $employee->hotel_id)
            ->where('is_active', true)
            ->orderBy('number')
            ->get();

        return view('housekeeping.edit', compact('housekeeping', 'housekeepers', 'rooms'));
    }

    public function update(Request $request, HousekeepingLog $housekeeping)
    {
        $data = $request->validate([
            'room_id'      => 'required|exists:rooms,id',
            'assigned_to'  => 'nullable|exists:employees,id',
            'type'         => 'required|in:cleaning,inspection,maintenance,turndown',
            'priority'     => 'required|in:low,normal,high,urgent',
            'status'       => 'required|in:scheduled,in_progress,completed,skipped',
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string|max:1000',
            'issues_found' => 'nullable|string|max:1000',
        ]);

        if ($data['status'] === 'in_progress' && !$housekeeping->started_at) {
            $data['started_at'] = now();
        }

        if ($data['status'] === 'completed' && !$housekeeping->completed_at) {
            $data['completed_at'] = now();
        }

        $housekeeping->update($data);

        return redirect()->route('housekeeping.index')
            ->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, HousekeepingLog $housekeeping)
    {
        $data = $request->validate([
            'status'       => 'required|in:scheduled,in_progress,completed,skipped',
            'issues_found' => 'nullable|string|max:1000',
        ]);

        if ($data['status'] === 'in_progress' && !$housekeeping->started_at) {
            $data['started_at'] = now();
        }

        if ($data['status'] === 'completed' && !$housekeeping->completed_at) {
            $data['completed_at'] = now();
        }

        $housekeeping->update($data);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(HousekeepingLog $housekeeping)
    {
        $housekeeping->delete();
        return redirect()->route('housekeeping.index')
            ->with('success', 'Task deleted.');
    }
}