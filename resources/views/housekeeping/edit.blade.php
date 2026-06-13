@extends('layouts.app')
@section('title', 'Edit Task')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('housekeeping.index') }}" class="text-sm text-gray-500 hover:underline">← Back</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Task — Room {{ $housekeeping->room->number }}</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('housekeeping.update', $housekeeping) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room *</label>
                    <select name="room_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('room_id', $housekeeping->room_id) == $room->id)>
                                Room {{ $room->number }} — {{ ucfirst($room->type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                    <select name="assigned_to" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Unassigned</option>
                        @foreach($housekeepers as $hk)
                            <option value="{{ $hk->id }}" @selected(old('assigned_to', $housekeeping->assigned_to) == $hk->id)>{{ $hk->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                    <select name="type" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach(['cleaning','inspection','maintenance','turndown'] as $t)
                            <option value="{{ $t }}" @selected(old('type', $housekeeping->type) === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
                    <select name="priority" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach(['urgent','high','normal','low'] as $p)
                            <option value="{{ $p }}" @selected(old('priority', $housekeeping->priority) === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach(['scheduled','in_progress','completed','skipped'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $housekeeping->status) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled At *</label>
                    <input type="datetime-local" name="scheduled_at"
                           value="{{ old('scheduled_at', $housekeeping->scheduled_at->format('Y-m-d\TH:i')) }}"
                           required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes', $housekeeping->notes) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Issues Found</label>
                    <textarea name="issues_found" rows="3"
                              class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('issues_found', $housekeeping->issues_found) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('housekeeping.index') }}"
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