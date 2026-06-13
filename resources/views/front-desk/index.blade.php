@extends('layouts.app')

@section('title', 'Front Desk')
@section('page-title', 'Front Desk')

@section('header-actions')
<div class="flex items-center gap-3">
    <span class="text-sm text-slate-500 hidden sm:block">
        {{ today()->format('l, d F Y') }}
    </span>
    <a href="{{ route('bookings.create') }}"
       class="inline-flex items-center gap-1.5 bg-amber-400 hover:bg-amber-300
              text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
        + New Booking
    </a>
    <a href="{{ route('front-desk.room-board') }}"
       class="inline-flex items-center gap-1.5 bg-white border border-slate-200
              hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2
              rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14
                     6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2
                     2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2
                     0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        Room Board
    </a>
</div>
@endsection

@section('content')

{{-- ── Overdue checkout alert ───────────────────────────────── --}}
@if($overdueCheckouts->isNotEmpty())
<div class="bg-red-50 border border-red-300 rounded-xl p-4 mb-6">
    <div class="flex items-start gap-3">
        <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center
                    flex-shrink-0 mt-0.5">
            <span class="text-white text-xs font-bold">!</span>
        </div>
        <div>
            <p class="text-sm font-semibold text-red-800 mb-2">
                {{ $overdueCheckouts->count() }}
                overdue checkout{{ $overdueCheckouts->count() > 1 ? 's' : '' }} — action required
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach($overdueCheckouts as $overdue)
                <div class="flex items-center gap-2 bg-red-100 rounded-lg px-3 py-2">
                    <div>
                        <p class="text-xs font-semibold text-red-800">
                            Room {{ $overdue->room->number }} — {{ $overdue->guest->full_name }}
                        </p>
                        <p class="text-xs text-red-600">
                            Was due: {{ $overdue->check_out->format('d M Y') }}
                            ({{ $overdue->check_out->diffForHumans() }})
                        </p>
                    </div>
                    <form method="POST"
                          action="{{ route('bookings.check-out', $overdue) }}">
                        @csrf
                        <button type="submit"
                                class="text-xs font-semibold bg-red-500 hover:bg-red-600
                                       text-white px-2.5 py-1 rounded-lg transition-colors
                                       whitespace-nowrap">
                            Check Out
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Summary stats ─────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <button onclick="switchTab('arrivals')"
            class="stat-card bg-white rounded-xl border border-slate-200 p-4 text-left
                   hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-slate-500">Arrivals Today</p>
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0
                             01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800">{{ $stats['arrivals_today'] }}</p>
        <p class="text-xs text-slate-400 mt-1">guests checking in</p>
    </button>

    <button onclick="switchTab('departures')"
            class="stat-card bg-white rounded-xl border border-slate-200 p-4 text-left
                   hover:border-amber-300 hover:shadow-sm transition-all">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-slate-500">Departures Today</p>
            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0
                             01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800">{{ $stats['departures_today'] }}</p>
        <p class="text-xs text-slate-400 mt-1">guests checking out</p>
    </button>

    <button onclick="switchTab('inhouse')"
            class="stat-card bg-white rounded-xl border border-slate-200 p-4 text-left
                   hover:border-emerald-300 hover:shadow-sm transition-all">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-slate-500">In House</p>
            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10
                             a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011
                             1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800">{{ $stats['in_house'] }}</p>
        <p class="text-xs text-slate-400 mt-1">guests currently staying</p>
    </button>

    <button onclick="switchTab('upcoming')"
            class="stat-card bg-white rounded-xl border border-slate-200 p-4 text-left
                   hover:border-slate-300 hover:shadow-sm transition-all">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-slate-500">Upcoming (7 days)</p>
            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5
                             a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800">{{ $upcomingArrivals->count() }}</p>
        <p class="text-xs text-slate-400 mt-1">confirmed bookings</p>
    </button>

</div>

{{-- ── Tab navigation ─────────────────────────────────────────── --}}
<div class="flex gap-1 bg-slate-100 p-1 rounded-xl mb-6 w-fit">
    @foreach([
        ['id' => 'arrivals',   'label' => 'Arrivals Today'],
        ['id' => 'departures', 'label' => 'Departures Today'],
        ['id' => 'inhouse',    'label' => 'In House'],
        ['id' => 'upcoming',   'label' => 'Upcoming'],
    ] as $tab)
    <button id="tab-btn-{{ $tab['id'] }}"
            onclick="switchTab('{{ $tab['id'] }}')"
            class="tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors
                   text-slate-500 hover:text-slate-700">
        {{ $tab['label'] }}
    </button>
    @endforeach
</div>

{{-- ── TAB: Arrivals Today ─────────────────────────────────────── --}}
<div id="tab-arrivals" class="tab-panel">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">
                Arrivals Today
                <span class="ml-2 text-blue-600 font-bold">
                    {{ $arrivalsToday->count() }}
                </span>
            </h2>
            <span class="text-xs text-slate-400">{{ today()->format('d M Y') }}</span>
        </div>

        @if($arrivalsToday->isEmpty())
        <div class="py-16 text-center">
            <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center
                        justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-500">No arrivals today</p>
            <p class="text-xs text-slate-400 mt-1">All clear — no guests expected today.</p>
        </div>

        @else
        <div class="divide-y divide-slate-100">
            @foreach($arrivalsToday as $booking)
            <div class="px-6 py-4 flex items-center gap-4">

                {{-- Avatar --}}
                <div class="w-11 h-11 rounded-full bg-blue-100 flex items-center
                            justify-center text-sm font-bold text-blue-600 flex-shrink-0">
                    {{ strtoupper(substr($booking->guest->first_name, 0, 1)) }}{{ strtoupper(substr($booking->guest->last_name, 0, 1)) }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-slate-800">
                            {{ $booking->guest->full_name }}
                        </p>
                        @if($booking->special_requests)
                        <span class="inline-flex items-center gap-1 text-xs bg-amber-100
                                     text-amber-700 px-2 py-0.5 rounded-full font-medium">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75
                                         1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98
                                         l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1
                                         1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                      clip-rule="evenodd"/>
                            </svg>
                            Special request
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 mt-0.5 text-xs text-slate-400
                                flex-wrap">
                        <span>{{ $booking->guest->phone }}</span>
                        <span class="text-slate-200">·</span>
                        <span class="font-medium text-slate-600">
                            Room {{ $booking->room->number }}
                        </span>
                        <span class="bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">
                            {{ $booking->room->type_display }}
                        </span>
                        <span class="text-slate-200">·</span>
                        <span>
                            {{ $booking->nights }}
                            night{{ $booking->nights !== 1 ? 's' : '' }}
                        </span>
                        <span>
                            {{ $booking->adults }} adult{{ $booking->adults !== 1 ? 's' : '' }}
                            @if($booking->children > 0)
                                + {{ $booking->children }} child{{ $booking->children !== 1 ? 'ren' : '' }}
                            @endif
                        </span>
                    </div>

                    @if($booking->special_requests)
                    <p class="text-xs text-amber-700 mt-1 italic">
                        "{{ \Illuminate\Support\Str::limit($booking->special_requests, 90) }}"
                    </p>
                    @endif
                </div>

                {{-- Booking ref + amount --}}
                <div class="text-right hidden md:block flex-shrink-0">
                    <p class="text-xs font-mono text-slate-400">
                        {{ $booking->booking_number }}
                    </p>
                    <p class="text-sm font-semibold text-slate-700 mt-0.5">
                        {{ number_format($booking->total_amount, 2) }}
                    </p>
                    <p class="text-xs text-slate-400">
                        Out: {{ $booking->check_out->format('d M') }}
                    </p>
                </div>

                {{-- Check In button --}}
                <form method="POST"
                      action="{{ route('bookings.check-in', $booking) }}"
                      class="flex-shrink-0">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-emerald-500
                                   hover:bg-emerald-600 text-white text-sm font-semibold
                                   px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap
                                   shadow-sm shadow-emerald-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Check In
                    </button>
                </form>

            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>

{{-- ── TAB: Departures Today ───────────────────────────────────── --}}
<div id="tab-departures" class="tab-panel hidden">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">
                Departures Today
                <span class="ml-2 text-amber-600 font-bold">
                    {{ $departuresToday->count() }}
                </span>
            </h2>
            <span class="text-xs text-slate-400">{{ today()->format('d M Y') }}</span>
        </div>

        @if($departuresToday->isEmpty())
        <div class="py-16 text-center">
            <div class="w-14 h-14 bg-amber-50 rounded-full flex items-center
                        justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-500">No departures today</p>
            <p class="text-xs text-slate-400 mt-1">No guests scheduled to check out today.</p>
        </div>

        @else
        <div class="divide-y divide-slate-100">
            @foreach($departuresToday as $booking)
            <div class="px-6 py-4 flex items-center gap-4">

                {{-- Avatar --}}
                <div class="w-11 h-11 rounded-full bg-amber-100 flex items-center
                            justify-center text-sm font-bold text-amber-600 flex-shrink-0">
                    {{ strtoupper(substr($booking->guest->first_name, 0, 1)) }}{{ strtoupper(substr($booking->guest->last_name, 0, 1)) }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $booking->guest->full_name }}
                    </p>
                    <div class="flex items-center gap-3 mt-0.5 text-xs text-slate-400 flex-wrap">
                        <span>{{ $booking->guest->phone }}</span>
                        <span class="text-slate-200">·</span>
                        <span class="font-medium text-slate-600">
                            Room {{ $booking->room->number }}
                        </span>
                        <span class="bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">
                            {{ $booking->room->type_display }}
                        </span>
                        @if($booking->actual_checkin)
                        <span class="text-slate-200">·</span>
                        <span>
                            Checked in: {{ $booking->actual_checkin->format('d M, H:i') }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Amount --}}
                <div class="text-right hidden md:block flex-shrink-0">
                    <p class="text-xs font-mono text-slate-400">
                        {{ $booking->booking_number }}
                    </p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5">
                        {{ number_format($booking->total_amount, 2) }}
                    </p>
                </div>

                {{-- View button --}}
                <a href="{{ route('bookings.show', $booking) }}"
                   class="flex-shrink-0 text-xs font-medium bg-white border border-slate-200
                          hover:bg-slate-50 text-slate-600 px-3 py-2 rounded-lg
                          transition-colors hidden sm:block">
                    View
                </a>

                {{-- Check Out button --}}
                <form method="POST"
                      action="{{ route('bookings.check-out', $booking) }}"
                      class="flex-shrink-0">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-500
                                   hover:bg-blue-600 text-white text-sm font-semibold
                                   px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap
                                   shadow-sm shadow-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3
                                     3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Check Out
                    </button>
                </form>

            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>

{{-- ── TAB: In House ───────────────────────────────────────────── --}}
<div id="tab-inhouse" class="tab-panel hidden">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">
                All In-House Guests
                <span class="ml-2 text-emerald-600 font-bold">
                    {{ $inHouseGuests->count() }}
                </span>
            </h2>
        </div>

        @if($inHouseGuests->isEmpty())
        <div class="py-16 text-center">
            <p class="text-sm text-slate-400">No guests currently in house.</p>
        </div>

        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500
                                   uppercase tracking-wide">Guest</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500
                                   uppercase tracking-wide">Room</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500
                                   uppercase tracking-wide">Checked In</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500
                                   uppercase tracking-wide">Due Out</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500
                                   uppercase tracking-wide">Amount</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($inHouseGuests as $booking)
                @php
                    $isToday   = $booking->check_out->isToday();
                    $isOverdue = $booking->check_out->isPast() && !$isToday;
                @endphp
                <tr class="hover:bg-slate-50 transition-colors
                           {{ $isOverdue ? 'bg-red-50' : '' }}">

                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center
                                        justify-center text-xs font-bold text-emerald-700
                                        flex-shrink-0">
                                {{ strtoupper(substr($booking->guest->first_name, 0, 1)) }}{{ strtoupper(substr($booking->guest->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ $booking->guest->full_name }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ $booking->guest->phone }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-3">
                        <span class="text-sm font-semibold text-slate-700">
                            {{ $booking->room->number }}
                        </span>
                        <span class="text-xs text-slate-400 ml-1">
                            {{ $booking->room->type_display }}
                        </span>
                    </td>

                    <td class="px-5 py-3 text-sm text-slate-600">
                        {{ $booking->actual_checkin?->format('d M, H:i') ?? '—' }}
                    </td>

                    <td class="px-5 py-3">
                        <span class="text-sm font-semibold
                            {{ $isOverdue ? 'text-red-600' : ($isToday ? 'text-amber-600' : 'text-slate-600') }}">
                            {{ $booking->check_out->format('d M Y') }}
                        </span>
                        @if($isOverdue)
                        <span class="block text-xs text-red-500 font-medium">Overdue</span>
                        @elseif($isToday)
                        <span class="block text-xs text-amber-600 font-medium">Today</span>
                        @endif
                    </td>

                    <td class="px-5 py-3 text-sm font-medium text-slate-700">
                        {{ number_format($booking->total_amount, 2) }}
                    </td>

                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('bookings.show', $booking) }}"
                               class="text-xs text-slate-400 hover:text-slate-600 font-medium">
                                View
                            </a>
                            @if($booking->canCheckOut())
                            <form method="POST"
                                  action="{{ route('bookings.check-out', $booking) }}">
                                @csrf
                                <button type="submit"
                                        class="text-xs font-semibold bg-blue-100
                                               hover:bg-blue-200 text-blue-700 px-2.5 py-1
                                               rounded-lg transition-colors">
                                    Check Out
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>

                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>

{{-- ── TAB: Upcoming ──────────────────────────────────────────── --}}
<div id="tab-upcoming" class="tab-panel hidden">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700">
                Upcoming Arrivals
                <span class="ml-2 text-slate-500 font-normal text-xs">
                    Next 7 days · {{ $upcomingArrivals->count() }} bookings
                </span>
            </h2>
        </div>

        @if($upcomingArrivals->isEmpty())
        <div class="py-16 text-center">
            <p class="text-sm text-slate-400">No confirmed arrivals in the next 7 days.</p>
            <a href="{{ route('bookings.create') }}"
               class="mt-2 inline-block text-sm text-amber-600 hover:text-amber-700 font-medium">
                Create a booking →
            </a>
        </div>

        @else
        {{-- Group by date --}}
        @foreach($upcomingArrivals->groupBy(fn($b) => $b->check_in->format('Y-m-d')) as $date => $dateBookings)

        <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-600">
                {{ \Carbon\Carbon::parse($date)->format('l, d F') }}
            </span>
            <span class="text-xs text-slate-400">
                {{ $dateBookings->count() }}
                arrival{{ $dateBookings->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        @foreach($dateBookings as $booking)
        <div class="px-6 py-3 flex items-center gap-4 border-b border-slate-100
                    hover:bg-slate-50 transition-colors">

            {{-- Date pill --}}
            <div class="w-12 text-center flex-shrink-0">
                <p class="text-xs text-slate-400 leading-none">
                    {{ $booking->check_in->format('M') }}
                </p>
                <p class="text-2xl font-bold text-slate-800 leading-tight">
                    {{ $booking->check_in->format('d') }}
                </p>
                <p class="text-xs text-slate-400 leading-none">
                    {{ $booking->check_in->format('D') }}
                </p>
            </div>

            <div class="w-px h-10 bg-slate-200 flex-shrink-0"></div>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800">
                    {{ $booking->guest->full_name }}
                </p>
                <div class="flex items-center gap-3 mt-0.5 text-xs text-slate-400 flex-wrap">
                    <span class="font-medium text-slate-600">
                        Room {{ $booking->room->number }}
                    </span>
                    <span class="bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">
                        {{ $booking->room->type_display }}
                    </span>
                    <span>
                        {{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }}
                    </span>
                    <span>
                        {{ $booking->adults }} adult{{ $booking->adults !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <p class="text-sm font-semibold text-slate-700 hidden sm:block">
                    {{ number_format($booking->total_amount, 2) }}
                </p>
                <a href="{{ route('bookings.show', $booking) }}"
                   class="text-xs text-slate-400 hover:text-amber-600 font-medium">
                    View →
                </a>
            </div>

        </div>
        @endforeach
        @endforeach
        @endif

    </div>
</div>

@push('scripts')
<script>
// ── Tab system ─────────────────────────────────────────────────
function switchTab(id) {
    // Hide every panel
    document.querySelectorAll('.tab-panel').forEach(function(panel) {
        panel.classList.add('hidden');
    });

    // Reset every button to inactive style
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.classList.remove('bg-white', 'text-slate-800', 'shadow-sm', 'font-semibold');
        btn.classList.add('text-slate-500');
    });

    // Show the selected panel
    var panel = document.getElementById('tab-' + id);
    if (panel) {
        panel.classList.remove('hidden');
    }

    // Activate the selected button
    var btn = document.getElementById('tab-btn-' + id);
    if (btn) {
        btn.classList.remove('text-slate-500');
        btn.classList.add('bg-white', 'text-slate-800', 'shadow-sm', 'font-semibold');
    }
}

// Default to arrivals tab on page load
document.addEventListener('DOMContentLoaded', function() {
    switchTab('arrivals');
});
</script>
@endpush

@endsection