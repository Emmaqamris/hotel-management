@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
            <p class="text-sm text-gray-500 mt-1">Manage all hotel staff</p>
        </div>
        @if(auth('employee')->user()->hasRole(['admin','manager']))
        <a href="{{ route('employees.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            + Add Employee
        </a>
        @endif
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name or email..."
               class="border rounded-lg px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-blue-500 focus:outline-none">

        <select name="role" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Roles</option>
            @foreach(['admin','manager','receptionist','housekeeper'] as $role)
                <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
            @endforeach
        </select>

        <select name="status" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>

        <button type="submit" class="bg-gray-100 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
            Filter
        </button>
        <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 px-3 py-2 hover:underline">Clear</a>
    </form>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wide">
                <tr>
                    <th class="px-6 py-3 text-left">Employee</th>
                    <th class="px-6 py-3 text-left">Role</th>
                    <th class="px-6 py-3 text-left">Hotel</th>
                    <th class="px-6 py-3 text-left">Phone</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Hired</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($employees as $emp)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $emp->avatar_url }}" alt="{{ $emp->name }}"
                                 class="w-9 h-9 rounded-full object-cover">
                            <div>
                                <p class="font-medium text-gray-900">{{ $emp->name }}</p>
                                <p class="text-gray-400 text-xs">{{ $emp->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $colors = ['admin'=>'purple','manager'=>'blue','receptionist'=>'green','housekeeper'=>'amber'];
                            $c = $colors[$emp->role] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            bg-{{ $c }}-100 text-{{ $c }}-700">
                            {{ ucfirst($emp->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $emp->hotel->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $emp->phone ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <form method="POST" action="{{ route('employees.toggle-status', $emp) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer
                                {{ $emp->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $emp->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $emp->hire_date?->format('M d, Y') ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('employees.show', $emp) }}"
                               class="text-gray-400 hover:text-gray-700 transition" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if(auth('employee')->user()->hasRole(['admin','manager']))
                            <a href="{{ route('employees.edit', $emp) }}"
                               class="text-blue-400 hover:text-blue-700 transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('employees.destroy', $emp) }}"
                                  onsubmit="return confirm('Delete {{ $emp->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-700 transition" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        No employees found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $employees->links() }}</div>
</div>
@endsection