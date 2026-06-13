@extends('layouts.app')
@section('title', 'My Tasks')
@section('page-title', 'My Tasks')

@section('content')
<div class="space-y-6">

{{-- ── Greeting ─────────────────────────────────────────────── --}}
@php
    $hour     = now()->hour;
    $greeting = match(true) {
        $hour >= 5  && $hour < 12 => 'Good morning',
        $hour >= 12 && $hour < 17 => 'Good afternoon',
        $hour >= 17 && $hour < 21 => 'Good evening',
        default                   => 'Good night',
    };
@endphp

<div class="bg-white rounded-2xl border border-slate-200 p-6 relative overflow-hidden"
     style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
    <div class="relative z-10">
        <h2 class="text-2xl font-bold text-slate-900">
            {{ $greeting }}, {{ explode(' ', $user->name)[0] }}! 🧹
        </h2>
        <p class="text-slate-400 text-sm mt-1">
            {{ now()->format('l, d F Y') }} · Here are your tasks for today
        </p>

        {{-- Progress bar --}}
        @if($allTasks->count() > 0)
        <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-600">
                    Today's Progress
                </span>
                <span class="text-xs font-bold text-slate-700">
                    {{ $completedTasks->count() }} / {{ $allTasks->count() }} completed
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                <div class="h-3 rounded-full transition-all duration-700"
                     style="width: {{ $progressPercent }}%;
                            background: linear-gradient(90deg, #10b981, #059669);">
                </div>
            </div>
            <p class="text-xs text-emerald-600 font-semibold mt-1">
                {{ $progressPercent }}% complete
                @if($progressPercent === 100) 🎉 All done! @endif
            </p>
        </div>
        @endif
    </div>
    <div class="absolute -right-6 -top-6 w-36 h-36 rounded-full opacity-5"
         style="background: #10b981;"></div>
</div>

{{-- ── Urgent tasks alert ───────────────────────────────────── --}}
@if($urgentTasks->count() > 0)
<div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center
                justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667
                     1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34
                     16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <p class="text-sm font-bold text-red-800">
        🔴 {{ $urgentTasks->count() }} urgent task{{ $urgentTasks->count() > 1 ? 's' : '' }}
        need immediate attention!
    </p>
</div>
@endif

{{-- ── Stat cards ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @php
    $taskStats = [
        ['label' => 'Total Tasks',   'value' => $allTasks->count(),        'color' => '#2563eb', 'bg' => '#dbeafe'],
        ['label' => 'Pending',       'value' => $pendingTasks->count(),    'color' => '#d97706', 'bg' => '#fef3c7'],
        ['label' => 'Completed',     'value' => $completedTasks->count(),  'color' => '#059669', 'bg' => '#d1fae5'],
        ['label' => 'Urgent',        'value' => $urgentTasks->count(),     'color' => '#dc2626', 'bg' => '#fee2e2'],
    ];
    @endphp

    @foreach($taskStats as $stat)
    <div class="bg-white rounded-xl p-5 border border-slate-200 transition-all
                hover:shadow-md hover:shadow-slate-100"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <p class="text-3xl font-bold mb-1" style="color: {{ $stat['color'] }};">
            {{ $stat['value'] }}
        </p>
        <p class="text-sm font-semibold text-slate-600">{{ $stat['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Today's Tasks ────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden"
     style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-700">
            📋 My Tasks for Today — {{ now()->format('d F Y') }}
        </h3>
    </div>

    <div class="divide-y divide-slate-100">
        @forelse($allTasks as $task)
        @php
            $isDone = $task->status === 'completed';
            $priorityConfig = [
                'urgent' => ['label' => 'URGENT', 'color' => '#dc2626', 'bg' => '#fee2e2', 'dot' => 'bg-red-500'],
                'high'   => ['label' => 'HIGH',   'color' => '#d97706', 'bg' => '#fef3c7', 'dot' => 'bg-amber-500'],
                'normal' => ['label' => 'NORMAL', 'color' => '#2563eb', 'bg' => '#dbeafe', 'dot' => 'bg-blue-500'],
                'low'    => ['label' => 'LOW',    'color' => '#64748b', 'bg' => '#f1f5f9', 'dot' => 'bg-slate-400'],
            ][$task->priority] ?? ['label' => 'NORMAL', 'color' => '#2563eb', 'bg' => '#dbeafe', 'dot' => 'bg-blue-500'];
        @endphp

        <div class="px-5 py-4 flex items-start gap-4 {{ $isDone ? 'opacity-50' : '' }}
                    hover:bg-slate-50 transition-colors">

            {{-- Completion checkbox --}}
            <div class="mt-0.5 flex-shrink-0">
                @if($isDone)
                <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center
                            justify-center">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                @else
                <div class="w-6 h-6 rounded-full border-2 border-slate-300"></div>
                @endif
            </div>

            {{-- Task info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="text-sm font-bold {{ $isDone ? 'line-through text-slate-400' : 'text-slate-800' }}">
                        Room {{ $task->room->number }}
                    </span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                          style="background: {{ $priorityConfig['bg'] }};
                                 color: {{ $priorityConfig['color'] }};">
                        {{ $priorityConfig['label'] }}
                    </span>
                    <span class="text-xs bg-slate-100 text-slate-600 font-semibold
                                 px-2 py-0.5 rounded-full capitalize">
                        {{ $task->task_type ?? 'Cleaning' }}
                    </span>
                </div>

                @if($task->notes)
                <p class="text-xs text-slate-500 {{ $isDone ? 'line-through' : '' }}">
                    {{ $task->notes }}
                </p>
                @endif

                <p class="text-xs text-slate-400 mt-1">
                    Floor {{ $task->room->floor }} ·
                    {{ $task->room->type_display }}
                    @if($task->completed_at)
                        · Completed {{ $task->completed_at->format('H:i') }}
                    @endif
                </p>
            </div>

            {{-- Action buttons --}}
            @if(!$isDone)
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($task->status === 'scheduled')
                <form method="POST"
                      action="{{ route('housekeeping.update', $task) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit"
                            class="text-xs font-bold text-white px-3 py-1.5
                                   rounded-lg transition-all hover:opacity-90"
                            style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                        Start
                    </button>
                </form>
                @endif

                @if($task->status === 'in_progress')
                <span class="text-xs font-bold text-blue-600 bg-blue-50
                             border border-blue-200 px-2 py-1 rounded-lg
                             animate-pulse">
                    In Progress…
                </span>
                @endif

                <form method="POST"
                      action="{{ route('housekeeping.update', $task) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit"
                            class="text-xs font-bold text-white px-3 py-1.5
                                   rounded-lg transition-all hover:opacity-90"
                            style="background: linear-gradient(135deg, #10b981, #059669);">
                        ✓ Done
                    </button>
                </form>
            </div>
            @endif

        </div>
        @empty
        <div class="px-6 py-16 text-center">
            <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center
                        justify-center mx-auto mb-4">
                <span class="text-3xl">🎉</span>
            </div>
            <p class="text-base font-bold text-slate-700">
                No tasks assigned for today!
            </p>
            <p class="text-sm text-slate-400 mt-1">
                Enjoy your day — you're all caught up.
            </p>
        </div>
        @endforelse
    </div>
</div>

{{-- ── Upcoming tasks ───────────────────────────────────────── --}}
@if($upcomingTasks->count() > 0)
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden"
     style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-700">📅 Upcoming Tasks (Next 7 Days)</h3>
    </div>
    <div class="divide-y divide-slate-100">
        @foreach($upcomingTasks as $task)
        <div class="px-5 py-3 flex items-center gap-4 hover:bg-slate-50 transition-colors">
            <div class="w-10 text-center flex-shrink-0">
                <p class="text-base font-bold text-amber-500 leading-none">
                    {{ \Carbon\Carbon::parse($task->scheduled_at)->format('d') }}
                </p>
                <p class="text-[10px] text-slate-400 uppercase">
                    {{ \Carbon\Carbon::parse($task->scheduled_at)->format('M') }}
                </p>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-slate-700">
                    Room {{ $task->room->number }}
                    <span class="font-normal text-slate-400">·</span>
                    {{ $task->room->type_display }}
                </p>
                <p class="text-xs text-slate-400 capitalize">
                    {{ $task->task_type ?? 'Cleaning' }} ·
                    Priority: {{ ucfirst($task->priority) }}
                </p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

</div>
@endsection