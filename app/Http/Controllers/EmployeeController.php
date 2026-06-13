<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
        

    public function index(Request $request)
    {
        $query = Employee::with('hotel')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->when($request->role, fn($q) =>
                $q->where('role', $request->role)
            )
            ->when($request->status !== null, fn($q) =>
                $q->where('is_active', $request->status === 'active')
            )
            ->latest();

        $employees = $query->paginate(15)->withQueryString();

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $hotels = Hotel::all();
        return view('employees.create', compact('hotels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hotel_id'   => 'required|exists:hotels,id',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:employees,email',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => ['required', Rule::in(['admin','manager','receptionist','housekeeper'])],
            'phone'      => 'nullable|string|max:20',
            'hire_date'  => 'nullable|date',
            'salary'     => 'nullable|numeric|min:0',
            'avatar'     => 'nullable|image|max:2048',
        ]);

        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        Employee::create($data);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['hotel', 'bookings' => fn($q) => $q->latest()->limit(5), 'housekeepingLogs' => fn($q) => $q->latest()->limit(5)]);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $hotels = Hotel::all();
        return view('employees.edit', compact('employee', 'hotels'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'hotel_id'   => 'required|exists:hotels,id',
            'name'       => 'required|string|max:255',
            'email'      => ['required','email', Rule::unique('employees','email')->ignore($employee->id)],
            'password'   => 'nullable|string|min:8|confirmed',
            'role'       => ['required', Rule::in(['admin','manager','receptionist','housekeeper'])],
            'phone'      => 'nullable|string|max:20',
            'hire_date'  => 'nullable|date',
            'salary'     => 'nullable|numeric|min:0',
            'is_active'  => 'boolean',
            'avatar'     => 'nullable|image|max:2048',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($employee->avatar) Storage::disk('public')->delete($employee->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');
        $employee->update($data);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->avatar) Storage::disk('public')->delete($employee->avatar);
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted.');
    }

    public function toggleStatus(Employee $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);
        return back()->with('success', 'Employee status updated.');
    }
}