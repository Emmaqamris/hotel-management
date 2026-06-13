@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('header-actions')
<a href="{{ route('bookings.create') }}"
   class="inline-flex items-center gap-2 text-sm font-bold text-white
          px-4 py-2.5 rounded-xl hover:opacity-90 shadow-md shadow-amber-200/50"
   style="background: linear-gradient(135deg, #f59e0b, #d97706);">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
              d="M12 4v16m8-8H4"/>
    </svg>
    New Booking
</a>
@endsection

@section('content')
<div class="space-y-6">

{{-- ── Greeting ─────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-200 p-6 relative overflow-hidden"
     style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
    <div class="relative z-10">
        <h2 class="text-2xl font-bold text-slate-900">
            {{ $greeting }}, {{ $firstName }}! 👋
        </h2>
        <p class="text-slate-400 text-sm mt-1">
            {{ now()->format('l, d F Y') }} · Here's your hotel at a glance
        </p>
    </div>
    <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full opacity-5"
         style="background: #f59e0b;"></div>
</div>

{{-- ── Overdue alert ────────────────────────────────────────── --}}
@if($stats['overdueCheckouts'] > 0)
<div class="flex items-center gap-3 bg-red-50 border border-red-200
            rounded-xl px-5 py-3.5">
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
    <p class="text-sm font-semibold text-red-800">
        {{ $stats['overdueCheckouts'] }}
        overdue checkout{{ $stats['overdueCheckouts'] > 1 ? 's' : '' }} —
        <a href="{{ route('front-desk.index') }}"
           class="underline hover:no-underline">
            action required
        </a>
    </p>
</div>
@endif

{{-- ── Metric cards (data from controller, no PHP here) ─────── --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
    @foreach($metricCards as $card)
    <div class="bg-white rounded-xl border border-slate-200 p-4
                hover:shadow-md transition-all"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="flex items-start justify-between mb-3">
            <p class="text-xs font-medium text-slate-400">{{ $card['label'] }}</p>
            <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                 style="background: {{ $card['iconBg'] }};">
                <svg class="w-4 h-4" fill="none"
                     stroke="{{ $card['iconColor'] }}"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900 leading-none mb-1">
            {{ $card['value'] }}
        </p>
        <p class="text-xs {{ $card['subColor'] }}">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Charts row ───────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-700">
                    Revenue — Last 30 Days
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    This month:
                    <span class="font-semibold text-slate-600">
                        {{ number_format($stats['revenueThisMonth'], 2) }}
                    </span>
                </p>
            </div>
            <a href="{{ route('reports.revenue') }}"
               class="text-xs font-semibold text-amber-600 hover:text-amber-700">
                Full report →
            </a>
        </div>
        <div id="revenue-chart" class="h-56"></div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-700">Room Status</h3>
            <a href="{{ route('front-desk.room-board') }}"
               class="text-xs font-semibold text-amber-600 hover:text-amber-700">
                Room board →
            </a>
        </div>
        <div id="room-status-chart" class="h-56"></div>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-white rounded-xl border border-slate-200 p-6"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <h3 class="text-sm font-bold text-slate-700 mb-4">
            Booking Trends — Last 30 Days
        </h3>
        <div id="bookings-chart" class="h-48"></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-6"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <h3 class="text-sm font-bold text-slate-700 mb-4">
            Monthly Revenue — {{ now()->year }}
        </h3>
        <div id="monthly-revenue-chart" class="h-48"></div>
    </div>
</div>

{{-- ── Additional stats ─────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 p-5"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <p class="text-xs text-slate-400 mb-1">RevPAR</p>
        <p class="text-xl font-bold text-slate-800">
            {{ number_format($stats['revpar'], 2) }}
        </p>
        <p class="text-xs text-slate-400 mt-0.5">revenue / available room</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <p class="text-xs text-slate-400 mb-1">Revenue Today</p>
        <p class="text-xl font-bold text-slate-800">
            {{ number_format($stats['revenueToday'], 2) }}
        </p>
        <p class="text-xs text-slate-400 mt-0.5">{{ now()->format('d M Y') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <p class="text-xs text-slate-400 mb-1">Revenue This Year</p>
        <p class="text-xl font-bold text-slate-800">
            {{ number_format($stats['revenueThisYear'], 2) }}
        </p>
        <p class="text-xs text-slate-400 mt-0.5">{{ now()->year }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <p class="text-xs text-slate-400 mb-1">Pending Housekeeping</p>
        <p class="text-xl font-bold
                  {{ $stats['pendingHousekeeping'] > 0 ? 'text-amber-600' : 'text-slate-800' }}">
            {{ $stats['pendingHousekeeping'] }}
        </p>
        <a href="{{ route('housekeeping.index') }}"
           class="text-xs text-amber-600 hover:text-amber-700 font-medium mt-0.5 block">
            View tasks →
        </a>
    </div>
</div>

{{-- ── Recent bookings ──────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden"
     style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-700">Recent Bookings</h3>
        <a href="{{ route('bookings.index') }}"
           class="text-xs font-semibold text-amber-600 hover:text-amber-700">
            View all →
        </a>
    </div>
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50">
            <tr>
                @foreach(['Reference','Guest','Room','Dates','Amount','Status'] as $h)
                <th class="px-5 py-3 text-left text-xs font-semibold
                           text-slate-400 uppercase tracking-wide">
                    {{ $h }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($recentBookings as $b)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3">
                    <a href="{{ route('bookings.show', $b) }}"
                       class="text-xs font-mono font-bold text-amber-600
                              hover:text-amber-700">
                        {{ $b->booking_number }}
                    </a>
                </td>
                <td class="px-5 py-3 text-sm font-medium text-slate-800">
                    {{ $b->guest->full_name }}
                </td>
                <td class="px-5 py-3 text-sm text-slate-500">
                    Room {{ $b->room->number }}
                </td>
                <td class="px-5 py-3 text-xs text-slate-400">
                    {{ $b->checkin_date?->format('d M Y') ?? '-' }} →
                    {{ $b->checkout_date?->format('d M Y') ?? '-' }}
                </td>
                <td class="px-5 py-3 text-sm font-bold text-slate-700">
                    {{ number_format($b->total_amount, 2) }}
                </td>
                <td class="px-5 py-3">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold
                                 bg-{{ $b->status_color }}-100
                                 text-{{ $b->status_color }}-700">
                        {{ $b->status_display }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6"
                    class="px-5 py-12 text-center text-sm text-slate-400">
                    No bookings yet. Create your first booking.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

</div>

{{-- ── Chart data — passed as pre-encoded JSON from controller ─ --}}
@push('scripts')
<script>
window.HMS_DASHBOARD = {!! $chartData !!};
</script>
@endpush

@endsection