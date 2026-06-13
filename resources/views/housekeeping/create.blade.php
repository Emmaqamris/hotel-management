@extends('layouts.app')
@section('title', 'Schedule Task')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('housekeeping.index') }}" class="text-sm text-gray-500 hover:underline">← Back</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Schedule Housekeeping Task</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('housekeeping.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room *</label>
                    <select name="room_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Select room...</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}"
                                @selected(old('room_id', $selectedRoom?->id) == $room->id)>
                                Room {{ $room->number }} — {{ ucfirst($room->type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                    <select name="assigned_to" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Unassigned</option>
                        @foreach($housekeepers as $hk)
                            <option value="{{ $hk->id }}" @selected(old('assigned_to') == $hk->id)>{{ $hk->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                    <select name="type" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach(['cleaning','inspection','maintenance','turndown'] as $t)
                            <option value="{{ $t }}" @selected(old('type') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
                    <select name="priority" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach(['urgent','high','normal','low'] as $p)
                            <option value="{{ $p }}" @selected(old('priority','normal') === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled At *</label>
                    <input type="datetime-local" name="scheduled_at"
                           value="{{ old('scheduled_at', now()->format('Y-m-d\TH:i')) }}"
                           required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('scheduled_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('housekeeping.index') }}"
                   class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50 transition">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Schedule Task
                </button>
            </div>
        </form>
    </div>
</div>
@endsection