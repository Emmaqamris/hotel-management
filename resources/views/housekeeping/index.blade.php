@extends('layouts.app')
@section('title', 'Housekeeping')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Housekeeping</h1>
            <p class="text-sm text-gray-500 mt-1">Manage cleaning and maintenance tasks</p>
        </div>
        @if(auth('employee')->user()->hasRole(['admin','manager','receptionist']))
        <a href="{{ route('housekeeping.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            + Schedule Task
        </a>
        @endif
    </div>

    {{-- Today's Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach(['scheduled' => ['blue','📋'], 'in_progress' => ['amber','🔄'], 'completed' => ['green','✅'], 'skipped' => ['gray','⏭️']] as $s => [$color, $icon])
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ ucfirst(str_replace('_',' ',$s)) }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats[$s] ?? 0 }}</p>
                </div>
                <span class="text-2xl">{{ $icon }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="status" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Status</option>
            @foreach(['scheduled','in_progress','completed','skipped'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="type" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Types</option>
            @foreach(['cleaning','inspection','maintenance','turndown'] as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <select name="priority" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Priorities</option>
            @foreach(['urgent','high','normal','low'] as $p)
                <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}"
               class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <button type="submit" class="bg-gray-100 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Filter</button>
        <a href="{{ route('housekeeping.index') }}" class="text-sm text-gray-500 px-3 py-2 hover:underline">Clear</a>
    </form>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Task List --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Room</th>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Priority</th>
                    <th class="px-5 py-3 text-left">Assigned To</th>
                    <th class="px-5 py-3 text-left">Scheduled</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($query as $task)
                @php
                    $priorityColors = ['urgent'=>'red','high'=>'orange','normal'=>'blue','low'=>'gray'];
                    $statusColors   = ['scheduled'=>'blue','in_progress'=>'amber','completed'=>'green','skipped'=>'gray'];
                    $pc = $priorityColors[$task->priority] ?? 'gray';
                    $sc = $statusColors[$task->status] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-900">Room {{ $task->room->number ?? '?' }}</p>
                        <p class="text-xs text-gray-400">{{ ucfirst($task->room->type ?? '') }}</p>
                    </td>
                    <td class="px-5 py-4 capitalize text-gray-600">{{ $task->type }}</td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $pc }}-100 text-{{ $pc }}-700">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-gray-600">
                        {{ $task->assignedEmployee->name ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-gray-500">
                        {{ $task->scheduled_at->format('M d, H:i') }}
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                            {{ ucfirst(str_replace('_',' ',$task->status)) }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex justify-end gap-2">
                            {{-- Quick status update --}}
                            @if($task->status === 'scheduled')
                            <form method="POST" action="{{ route('housekeeping.update-status', $task) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded hover:bg-amber-200 transition">Start</button>
                            </form>
                            @elseif($task->status === 'in_progress')
                            <form method="POST" action="{{ route('housekeeping.update-status', $task) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 transition">Done</button>
                            </form>
                            @endif
                            <a href="{{ route('housekeeping.edit', $task) }}"
                               class="text-blue-400 hover:text-blue-700 transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if(auth('employee')->user()->hasRole(['admin','manager']))
                            <form method="POST" action="{{ route('housekeeping.destroy', $task) }}"
                                  onsubmit="return confirm('Delete this task?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">No tasks found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $query->links() }}</div>
</div>
@endsection