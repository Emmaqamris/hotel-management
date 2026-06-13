@extends('layouts.app')

@section('title', 'Occupancy Report')
@section('page-title', 'Occupancy Report')

@section('header-actions')
<a href="{{ route('reports.occupancy.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
   class="inline-flex items-center gap-1.5 bg-white border border-slate-200
          hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2
          rounded-lg transition-colors">
    Export CSV
</a>
@endsection

@section('content')

{{-- Date filter --}}
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-500 font-medium">From</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                   class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-500 font-medium">To</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                   class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>
        @foreach([
            ['label' => 'This Month', 'from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->endOfMonth()->format('Y-m-d')],
            ['label' => 'Last Month', 'from' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'to' => now()->subMonth()->endOfMonth()->format('Y-m-d')],
            ['label' => 'Last 90 Days','from' => now()->subDays(90)->format('Y-m-d'), 'to' => now()->format('Y-m-d')],
        ] as $shortcut)
        <a href="{{ route('reports.occupancy', ['from' => $shortcut['from'], 'to' => $shortcut['to']]) }}"
           class="text-xs font-medium px-3 py-1.5 rounded-lg border transition-colors
                  {{ $from->format('Y-m-d') === $shortcut['from'] && $to->format('Y-m-d') === $shortcut['to']
                      ? 'bg-slate-800 text-white border-slate-800'
                      : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            {{ $shortcut['label'] }}
        </a>
        @endforeach
        <button type="submit"
                class="bg-slate-800 hover:bg-slate-700 text-white text-sm
                       font-medium px-4 py-2 rounded-lg transition-colors">
            Apply
        </button>
    </form>
</div>

{{-- Key metrics --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs text-slate-400 font-medium mb-1">Avg Occupancy Rate</p>
        <p class="text-3xl font-bold text-blue-600">
            {{ $data['avg_occupancy_rate'] }}%
        </p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs text-slate-400 font-medium mb-1">Occupied Nights</p>
        <p class="text-3xl font-bold text-slate-800">
            {{ number_format($data['total_occupied_nights']) }}
        </p>
        <p class="text-xs text-slate-400 mt-0.5">of {{ number_format($data['total_nights']) }} available</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs text-slate-400 font-medium mb-1">Total Rooms</p>
        <p class="text-3xl font-bold text-slate-800">{{ $data['total_rooms'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs text-slate-400 font-medium mb-1">Days in Period</p>
        <p class="text-3xl font-bold text-slate-800">{{ $data['total_days'] }}</p>
        <p class="text-xs text-slate-400 mt-0.5">
            {{ $from->format('d M') }} – {{ $to->format('d M Y') }}
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Occupancy chart (React) --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Daily Occupancy Rate</h2>
        <div id="occupancy-chart" class="h-64"></div>
    </div>

    {{-- Room type breakdown --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">By Room Type</h2>
        @forelse($data['by_room_type'] as $type)
        <div class="mb-4">
            <div class="flex justify-between text-sm mb-1">
                <span class="font-medium text-slate-700">{{ $type['label'] }}</span>
                <span class="text-slate-500">{{ $type['rate'] }}%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2">
                <div class="bg-blue-500 h-2 rounded-full transition-all"
                     style="width: {{ $type['rate'] }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ $type['rooms'] }} room{{ $type['rooms'] !== 1 ? 's' : '' }}
                · {{ $type['occupied'] }} occupied bookings
            </p>
        </div>
        @empty
        <p class="text-sm text-slate-400 text-center py-8">No data available.</p>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
window.HMS_REPORT_OCCUPANCY = @json($data['daily_data']);
</script>
@endpush

@endsection