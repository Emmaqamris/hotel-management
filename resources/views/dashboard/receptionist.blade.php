@extends('layouts.app')
@section('title', 'Front Desk')
@section('page-title', 'Front Desk')

@section('header-actions')
<a href="{{ route('front-desk.room-board') }}"
   class="inline-flex items-center gap-2 bg-white border border-slate-200
          hover:bg-slate-50 text-slate-700 text-sm font-semibold px-4 py-2.5
          rounded-xl transition-all">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6
                 zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2
                 -2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0
                 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2
                 2 0 01-2-2v-2z"/>
    </svg>
    Room Board
</a>
<a href="{{ route('bookings.create') }}"
   class="inline-flex items-center gap-2 text-sm font-bold text-white
          px-4 py-2.5 rounded-xl transition-all hover:opacity-90
          shadow-md shadow-amber-200/50"
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
@php
    $hour     = now()->hour;
    $greeting = match(true) {
        $hour >= 5  && $hour < 12 => 'Good morning',
        $hour >= 12 && $hour < 17 => 'Good afternoon',
        $hour >= 17 && $hour < 21 => 'Good evening',
        default                   => 'Good night',
    };
    $emoji = match(true) {
        $hour >= 5  && $hour < 12 => '☀️',
        $hour >= 12 && $hour < 17 => '🌤️',
        $hour >= 17 && $hour < 21 => '🌅',
        default                   => '🌙',
    };
@endphp

<div class="bg-white rounded-2xl border border-slate-200 p-6 flex items-center
            justify-between overflow-hidden relative"
     style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
    <div class="relative z-10">
        <h2 class="text-2xl font-bold text-slate-900">
            {{ $greeting }}, {{ explode(' ', $user->name)[0] }}! {{ $emoji }}
        </h2>
        <p class="text-slate-400 text-sm mt-1">
            {{ now()->format('l, d F Y') }} ·
            <span class="font-semibold text-slate-600">
                {{ $availableRooms }} room{{ $availableRooms !== 1 ? 's' : '' }} available
            </span>
            out of {{ $totalRooms }} total
        </p>
    </div>
    {{-- Decorative --}}
    <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full opacity-5"
         style="background: #f59e0b;"></div>
    <div class="absolute -right-4 -bottom-6 w-28 h-28 rounded-full opacity-5"
         style="background: #f59e0b;"></div>
</div>

{{-- ── Overdue alert ────────────────────────────────────────── --}}
@if($overdueCheckouts->count() > 0)
<div class="bg-red-50 border border-red-200 rounded-xl p-4">
    <div class="flex items-center gap-3 mb-3">
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
            ⚠️ {{ $overdueCheckouts->count() }} overdue checkout{{ $overdueCheckouts->count() > 1 ? 's' : '' }}
        </p>
    </div>
    <div class="space-y-2">
        @foreach($overdueCheckouts as $b)
        <div class="flex items-center justify-between bg-white rounded-lg px-4 py-2.5
                    border border-red-100">
            <div>
                <p class="text-sm font-semibold text-slate-800">
                    {{ $b->guest->full_name }}
                </p>
                <p class="text-xs text-red-600">
                    Room {{ $b->room->number }} · Was due
                    {{ $b->checkout_date->diffForHumans() }}
                </p>
            </div>
            <form method="POST"
                  action="{{ route('bookings.check-out', $b) }}">
                @csrf
                <button type="submit"
                        class="text-xs font-bold text-white bg-red-500
                               hover:bg-red-600 px-3 py-1.5 rounded-lg
                               transition-colors">
                    Check Out Now
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Stat cards ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @php
    $statCards = [
        ['label'   => 'Arrivals Today',
         'value'   => $arrivals->count(),
         'sub'     => 'guests checking in',
         'color'   => '#059669',
         'bg'      => '#d1fae5',
         'border'  => '#a7f3d0'],
        ['label'   => 'Departures Today',
         'value'   => $departures->count(),
         'sub'     => 'guests checking out',
         'color'   => '#dc2626',
         'bg'      => '#fee2e2',
         'border'  => '#fecaca'],
        ['label'   => 'In House',
         'value'   => $inHouse->count(),
         'sub'     => 'guests currently staying',
         'color'   => '#2563eb',
         'bg'      => '#dbeafe',
         'border'  => '#bfdbfe'],
        ['label'   => 'Upcoming (7 days)',
         'value'   => $upcoming->count(),
         'sub'     => 'confirmed bookings',
         'color'   => '#d97706',
         'bg'      => '#fef3c7',
         'border'  => '#fde68a'],
    ];
    @endphp

    @foreach($statCards as $card)
    <div class="bg-white rounded-xl p-5 border transition-all
                hover:shadow-md hover:shadow-slate-100"
         style="border-color: {{ $card['border'] }};
                box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <p class="text-3xl font-bold mb-1" style="color: {{ $card['color'] }};">
            {{ $card['value'] }}
        </p>
        <p class="text-sm font-semibold text-slate-700">{{ $card['label'] }}</p>
        <p class="text-xs text-slate-400 mt-0.5">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Main content grid ────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Arrivals Today --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                <h3 class="text-sm font-bold text-slate-700">Arrivals Today</h3>
                @if($arrivals->count() > 0)
                <span class="bg-emerald-100 text-emerald-700 text-xs font-bold
                             px-2 py-0.5 rounded-full">
                    {{ $arrivals->count() }}
                </span>
                @endif
            </div>
        </div>

        <div class="divide-y divide-slate-50">
            @forelse($arrivals as $booking)
            <div class="px-5 py-4 flex items-center gap-4 hover:bg-slate-50
                        transition-colors">
                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full flex items-center justify-center
                            text-sm font-bold text-white flex-shrink-0"
                     style="background: linear-gradient(135deg, #10b981, #059669);">
                    {{ strtoupper(substr($booking->guest->first_name, 0, 1)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">
                        {{ $booking->guest->full_name }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Room {{ $booking->room->number }} ·
                        {{ $booking->room->type_display }} ·
                        {{ $booking->nights }} night{{ $booking->nights > 1 ? 's' : '' }}
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('bookings.show', $booking) }}"
                       class="text-xs text-slate-400 hover:text-slate-600
                              font-medium px-2 py-1 rounded-lg hover:bg-slate-100
                              transition-colors">
                        View
                    </a>
                    @if($booking->canCheckIn())
                    <form method="POST"
                          action="{{ route('bookings.check-in', $booking) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1 text-xs font-bold
                                       text-white px-3 py-1.5 rounded-lg transition-all
                                       hover:opacity-90"
                                style="background: linear-gradient(135deg, #10b981, #059669);">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Check In
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center">
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center
                            justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-emerald-400" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-500">No arrivals today</p>
                <p class="text-xs text-slate-400 mt-0.5">All clear — no guests expected</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Departures Today --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                <h3 class="text-sm font-bold text-slate-700">Departures Today</h3>
                @if($departures->count() > 0)
                <span class="bg-red-100 text-red-700 text-xs font-bold
                             px-2 py-0.5 rounded-full">
                    {{ $departures->count() }}
                </span>
                @endif
            </div>
        </div>

        <div class="divide-y divide-slate-50">
            @forelse($departures as $booking)
            <div class="px-5 py-4 flex items-center gap-4 hover:bg-slate-50
                        transition-colors">
                <div class="w-10 h-10 rounded-full flex items-center justify-center
                            text-sm font-bold text-white flex-shrink-0"
                     style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    {{ strtoupper(substr($booking->guest->first_name, 0, 1)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">
                        {{ $booking->guest->full_name }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Room {{ $booking->room->number }} ·
                        @if($booking->invoice && !$booking->invoice->isPaid())
                            <span class="text-amber-600 font-semibold">
                                Balance: {{ number_format($booking->invoice->total, 2) }}
                            </span>
                        @else
                            <span class="text-emerald-600">Paid ✓</span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($booking->invoice && !$booking->invoice->isPaid())
                    <a href="{{ route('payments.create', $booking->invoice) }}"
                       class="text-xs font-bold text-amber-600 hover:text-amber-700
                              px-2 py-1 rounded-lg hover:bg-amber-50 transition-colors">
                        Pay
                    </a>
                    @endif
                    <form method="POST"
                          action="{{ route('bookings.check-out', $booking) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1 text-xs font-bold
                                       text-white px-3 py-1.5 rounded-lg transition-all
                                       hover:opacity-90"
                                style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                            </svg>
                            Check Out
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center">
                <div class="w-12 h-12 bg-red-50 rounded-full flex items-center
                            justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-red-300" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-500">No departures today</p>
                <p class="text-xs text-slate-400 mt-0.5">No guests scheduled to leave</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ── In House + Upcoming ──────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- In House --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                <h3 class="text-sm font-bold text-slate-700">Currently In House</h3>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold
                             px-2 py-0.5 rounded-full">
                    {{ $inHouse->count() }}
                </span>
            </div>
        </div>
        <div class="divide-y divide-slate-50 max-h-64 overflow-y-auto">
            @forelse($inHouse as $booking)
            <div class="px-5 py-3 flex items-center gap-3 hover:bg-slate-50 transition-colors">
                <div class="w-8 h-8 rounded-full flex items-center justify-center
                            text-xs font-bold text-white flex-shrink-0"
                     style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    {{ strtoupper(substr($booking->guest->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">
                        {{ $booking->guest->full_name }}
                    </p>
                    <p class="text-xs text-slate-400">
                        Room {{ $booking->room->number }} ·
                        Checkout {{ $booking->checkout_date->diffForHumans() }}
                    </p>
                </div>
                <a href="{{ route('bookings.show', $booking) }}"
                   class="text-xs text-slate-400 hover:text-amber-600 font-medium">
                    View →
                </a>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-sm text-slate-400">
                No guests currently in house.
            </div>
            @endforelse
        </div>
    </div>

    {{-- Upcoming --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden"
         style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                <h3 class="text-sm font-bold text-slate-700">Upcoming (Next 7 Days)</h3>
            </div>
            <a href="{{ route('bookings.index') }}"
               class="text-xs text-amber-600 hover:text-amber-700 font-semibold">
                All →
            </a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($upcoming as $booking)
            <div class="px-5 py-3 flex items-center gap-3 hover:bg-slate-50 transition-colors">
                <div class="w-10 text-center flex-shrink-0">
                    <p class="text-lg font-bold text-amber-500 leading-none">
                        {{ $booking->checkin_date->format('d') }}
                    </p>
                    <p class="text-[10px] text-slate-400 uppercase">
                        {{ $booking->checkin_date->format('M') }}
                    </p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">
                        {{ $booking->guest->full_name }}
                    </p>
                    <p class="text-xs text-slate-400">
                        Room {{ $booking->room->number }} ·
                        {{ $booking->nights }} night{{ $booking->nights > 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-sm text-slate-400">
                No upcoming bookings this week.
            </div>
            @endforelse
        </div>
    </div>

</div>

</div>
@endsection