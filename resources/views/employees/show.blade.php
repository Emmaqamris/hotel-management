@extends('layouts.app')
@section('title', $employee->name)
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:underline">← Back to Employees</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Profile Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
            <img src="{{ $employee->avatar_url }}" class="w-24 h-24 rounded-full mx-auto object-cover mb-4">
            <h2 class="text-xl font-bold text-gray-900">{{ $employee->name }}</h2>
            <p class="text-sm text-gray-500 mb-3">{{ $employee->email }}</p>

            @php $colors = ['admin'=>'purple','manager'=>'blue','receptionist'=>'green','housekeeper'=>'amber']; $c = $colors[$employee->role] ?? 'gray'; @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">
                {{ ucfirst($employee->role) }}
            </span>

            <div class="mt-4 pt-4 border-t text-left space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="{{ $employee->is_active ? 'text-green-600' : 'text-red-500' }} font-medium">
                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Phone</span>
                    <span class="text-gray-700">{{ $employee->phone ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Hotel</span>
                    <span class="text-gray-700">{{ $employee->hotel->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Hired</span>
                    <span class="text-gray-700">{{ $employee->hire_date?->format('M d, Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Salary</span>
                    <span class="text-gray-700">{{ $employee->salary ? '$'.number_format($employee->salary, 2) : '—' }}</span>
                </div>
            </div>

            @if(auth('employee')->user()->hasRole(['admin','manager']))
            <a href="{{ route('employees.edit', $employee) }}"
               class="mt-5 w-full inline-block bg-blue-600 text-white text-sm font-medium py-2 rounded-lg hover:bg-blue-700 transition text-center">
                Edit Profile
            </a>
            @endif
        </div>

        {{-- Activity --}}
        <div class="md:col-span-2 space-y-6">
            {{-- Recent Bookings --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Recent Bookings Handled</h3>
                @forelse($employee->bookings as $booking)
                <div class="flex justify-between items-center py-2 border-b last:border-0 text-sm">
                    <div>
                        <p class="font-medium text-gray-800">{{ $booking->guest->name ?? 'Guest' }}</p>
                        <p class="text-gray-400 text-xs">Room {{ $booking->room->number ?? '?' }} · {{ $booking->check_in?->format('M d') }} – {{ $booking->check_out?->format('M d, Y') }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600 capitalize">{{ $booking->status }}</span>
                </div>
                @empty
                <p class="text-gray-400 text-sm">No bookings yet.</p>
                @endforelse
            </div>

            {{-- Housekeeping Logs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Recent Housekeeping Tasks</h3>
                @forelse($employee->housekeepingLogs as $log)
                <div class="flex justify-between items-center py-2 border-b last:border-0 text-sm">
                    <div>
                        <p class="font-medium text-gray-800">Room {{ $log->room->number ?? '?' }}</p>
                        <p class="text-gray-400 text-xs">{{ $log->created_at?->format('M d, Y H:i') }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600 capitalize">{{ $log->status ?? 'assigned' }}</span>
                </div>
                @empty
                <p class="text-gray-400 text-sm">No housekeeping tasks yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection