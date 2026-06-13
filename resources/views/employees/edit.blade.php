@extends('layouts.app')
@section('title', 'Edit Employee')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:underline">← Back to Employees</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit — {{ $employee->name }}</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div class="flex items-center gap-4 pb-4 border-b">
                <img src="{{ $employee->avatar_url }}" class="w-16 h-16 rounded-full object-cover">
                <div>
                    <p class="font-semibold text-gray-900">{{ $employee->name }}</p>
                    <p class="text-sm text-gray-500">{{ ucfirst($employee->role) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}" required
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" required
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-gray-400">(leave blank to keep)</span></label>
                    <input type="password" name="password"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select name="role" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach(['admin','manager','receptionist','housekeeper'] as $role)
                            <option value="{{ $role }}" @selected(old('role', $employee->role) === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hotel *</label>
                    <select name="hotel_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($hotels as $hotel)
                            <option value="{{ $hotel->id }}" @selected(old('hotel_id', $employee->hotel_id) == $hotel->id)>{{ $hotel->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                    <input type="number" name="salary" value="{{ old('salary', $employee->salary) }}" step="0.01" min="0"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Avatar</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>

                <div class="flex items-center gap-3 col-span-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           @checked(old('is_active', $employee->is_active))
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active Employee</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('employees.index') }}"
                   class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50 transition">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection