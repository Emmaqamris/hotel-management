@extends('layouts.app')

@section('title', 'Room Board')
@section('page-title', 'Room Board')

@section('header-actions')
<a href="{{ route('front-desk.index') }}"
   class="inline-flex items-center gap-1.5 bg-white border border-slate-200
          hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2
          rounded-lg transition-colors">
    ← Front Desk
</a>
@endsection

@section('content')

{{-- ── Legend ─────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
    <div class="flex flex-wrap items-center gap-6">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
            Legend
        </span>
        @foreach([
            ['color' => 'emerald', 'label' => 'Available'],
            ['color' => 'red',     'label' => 'Occupied'],
            ['color' => 'blue',    'label' => 'Arriving Today'],
            ['color' => 'slate',   'label' => 'Maintenance'],
        ] as $item)
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-{{ $item['color'] }}-500"></div>
            <span class="text-xs text-slate-600">{{ $item['label'] }}</span>
        </div>
        @endforeach

        {{-- Live count --}}
        <div class="ml-auto flex items-center gap-4 text-xs text-slate-400">
            <span>{{ $checkedInBookings->count() }} occupied</span>
            <span>{{ $arrivingTodayBookings->count() }} arriving</span>
        </div>
    </div>
</div>

{{-- ── Floor by floor ───────────────────────────────────────────── --}}
@forelse($roomsByFloor as $floor => $rooms)

<div class="mb-8">

    {{-- Floor header --}}
    <div class="flex items-center gap-3 mb-4">
        <div class="bg-slate-800 text-white text-xs font-bold px-3 py-1.5 rounded-full">
            Floor {{ $floor }}
        </div>
        <div class="flex-1 h-px bg-slate-200"></div>
        <span class="text-xs text-slate-400">{{ $rooms->count() }} rooms</span>
    </div>

    {{-- Room cards grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5
                xl:grid-cols-6 gap-3">

        @foreach($rooms as $room)
        @php
            // Resolve the display status for this room from booking data
            $activeBooking   = $checkedInBookings->get($room->id);
            $arrivingBooking = $arrivingTodayBookings->get($room->id);

            if ($room->status === 'maintenance') {
                $cardBg       = 'bg-slate-100';
                $cardBorder   = 'border-slate-300';
                $dotColor     = 'bg-slate-400';
                $cardStatus   = 'maintenance';
                $statusLabel  = 'Maintenance';
                $labelColor   = 'text-slate-500';
            } elseif ($activeBooking) {
                $cardBg       = 'bg-red-50';
                $cardBorder   = 'border-red-300';
                $dotColor     = 'bg-red-500';
                $cardStatus   = 'occupied';
                $statusLabel  = 'Occupied';
                $labelColor   = 'text-red-600';
            } elseif ($arrivingBooking) {
                $cardBg       = 'bg-blue-50';
                $cardBorder   = 'border-blue-300';
                $dotColor     = 'bg-blue-500';
                $cardStatus   = 'arriving';
                $statusLabel  = 'Arriving Today';
                $labelColor   = 'text-blue-600';
            } else {
                $cardBg       = 'bg-white';
                $cardBorder   = 'border-emerald-300';
                $dotColor     = 'bg-emerald-500';
                $cardStatus   = 'available';
                $statusLabel  = 'Available';
                $labelColor   = 'text-emerald-600';
            }
        @endphp

        <div class="border-2 {{ $cardBorder }} {{ $cardBg }} rounded-xl p-3
                    flex flex-col min-h-36 transition-shadow hover:shadow-md">

            {{-- Header: room number + status dot --}}
            <div class="flex items-start justify-between mb-1">
                <span class="text-xl font-bold text-slate-800 leading-none">
                    {{ $room->number }}
                </span>
                <div class="w-2.5 h-2.5 rounded-full {{ $dotColor }} mt-1 flex-shrink-0">
                </div>
            </div>

            {{-- Room type --}}
            <p class="text-xs text-slate-400 mb-2 leading-none">
                {{ $room->type_display }}
            </p>

            {{-- Guest info or status --}}
            <div class="flex-1">
                @if($cardStatus === 'occupied' && $activeBooking)

                <p class="text-xs font-semibold text-slate-700 truncate">
                    {{ $activeBooking->guest->full_name }}
                </p>
                <p class="text-xs text-slate-400 mt-0.5">
                    Out:
                    <span class="{{ $activeBooking->check_out->isToday() ? 'text-amber-600 font-semibold' : '' }}">
                        {{ $activeBooking->check_out->format('d M') }}
                    </span>
                </p>

                @elseif($cardStatus === 'arriving' && $arrivingBooking)

                <p class="text-xs font-semibold text-slate-700 truncate">
                    {{ $arrivingBooking->guest->full_name }}
                </p>
                <p class="text-xs {{ $labelColor }} font-medium mt-0.5">
                    Arriving today
                </p>

                @else

                <p class="text-xs {{ $labelColor }} font-medium">
                    {{ $statusLabel }}
                </p>

                @endif
            </div>

            {{-- Action button --}}
            <div class="mt-3">

                @if($cardStatus === 'occupied' && $activeBooking)
                <form method="POST"
                      action="{{ route('bookings.check-out', $activeBooking) }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-xs font-semibold bg-blue-500
                                   hover:bg-blue-600 text-white py-1.5 rounded-lg
                                   transition-colors">
                        Check Out
                    </button>
                </form>

                @elseif($cardStatus === 'arriving' && $arrivingBooking)
                <form method="POST"
                      action="{{ route('bookings.check-in', $arrivingBooking) }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-xs font-semibold bg-emerald-500
                                   hover:bg-emerald-600 text-white py-1.5 rounded-lg
                                   transition-colors">
                        Check In
                    </button>
                </form>

                @elseif($cardStatus === 'maintenance')
                <form method="POST"
                      action="{{ route('rooms.status', $room) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="available">
                    <button type="submit"
                            class="w-full text-xs font-semibold bg-slate-200
                                   hover:bg-slate-300 text-slate-600 py-1.5 rounded-lg
                                   transition-colors">
                        Mark Available
                    </button>
                </form>

                @else
                <a href="{{ route('bookings.create',  [
                       'checkin_date'  => today()->format('Y-m-d'),
                       'checkout_date' => today()->addDay()->format('Y-m-d'),
                   ]) }}"
                   class="block text-center text-xs font-semibold bg-emerald-100
                          hover:bg-emerald-200 text-emerald-700 py-1.5 rounded-lg
                          transition-colors">
                    Book
                </a>
                @endif

            </div>
        </div>

        @endforeach
    </div>
</div>

@empty
<div class="bg-white rounded-xl border border-slate-200 py-20 text-center">
    <p class="text-sm text-slate-400">No active rooms found.</p>
    <a href="{{ route('rooms.create') }}"
       class="mt-2 inline-block text-sm text-amber-600 hover:text-amber-700 font-medium">
        Add rooms →
    </a>
</div>
@endforelse

@endsection