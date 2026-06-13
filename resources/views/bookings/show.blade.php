@extends('layouts.app')

@section('title', 'Booking ' . $booking->booking_number)
@section('page-title', 'Booking ' . $booking->booking_number)

@section('header-actions')
{{-- Check In --}}
@if($booking->canCheckIn())
<form method="POST" action="{{ route('bookings.check-in', $booking) }}">
    @csrf
    <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
        ✓ Check In Guest
    </button>
</form>
@endif

{{-- Check Out --}}
@if($booking->canCheckOut())
<form method="POST" action="{{ route('bookings.check-out', $booking) }}">
    @csrf
    <button class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
        ✓ Check Out Guest
    </button>
</form>
@endif

{{-- No Show --}}
@if($booking->canMarkNoShow())
<form method="POST" action="{{ route('bookings.no-show', $booking) }}">
    @csrf
    <button class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
        Mark No Show
    </button>
</form>
@endif

{{-- Cancel --}}
@if($booking->canCancel())
<button onclick="document.getElementById('cancel-modal').classList.remove('hidden')"
        class="bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
    Cancel Booking
</button>
@endif
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Main booking card ────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <p class="text-xs text-slate-400 mb-1 font-medium uppercase tracking-wide">
                        Booking Reference
                    </p>
                    <h2 class="text-3xl font-bold font-mono text-slate-800">
                        {{ $booking->booking_number }}
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Created {{ $booking->created_at->format('d M Y, H:i') }}
                        @if($booking->employee) by {{ $booking->employee->name }} @endif
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold
                    bg-{{ $booking->status_color }}-100 text-{{ $booking->status_color }}-700">
                    <span class="w-2 h-2 rounded-full bg-{{ $booking->status_color }}-500"></span>
                    {{ $booking->status_display }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 border-y border-slate-100">
                <div>
                    <p class="text-xs text-slate-400 font-medium mb-1">Check-in</p>
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $booking->check_in->format('D, d M Y') }}
                    </p>
                    @if($booking->actual_checkin)
                    <p class="text-xs text-emerald-500 mt-0.5">
                        ✓ {{ $booking->actual_checkin->format('H:i') }}
                    </p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium mb-1">Check-out</p>
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $booking->check_out->format('D, d M Y') }}
                    </p>
                    @if($booking->actual_checkout)
                    <p class="text-xs text-blue-500 mt-0.5">
                        ✓ {{ $booking->actual_checkout->format('H:i') }}
                    </p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium mb-1">Duration</p>
                    <p class="text-sm font-semibold text-slate-800">{{ $booking->nights }} night(s)</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium mb-1">Guests</p>
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $booking->adults }} adult{{ $booking->adults > 1 ? 's' : '' }}
                        @if($booking->children > 0)
                            + {{ $booking->children }} child{{ $booking->children > 1 ? 'ren' : '' }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="mt-4 grid grid-cols-3 gap-4">
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-400 mb-1">Room Rate</p>
                    <p class="text-sm font-bold text-slate-800">
                        {{ number_format($booking->room_rate, 2) }}<span class="font-normal text-slate-400">/night</span>
                    </p>
                </div>
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-400 mb-1">Source</p>
                    <p class="text-sm font-bold text-slate-800 capitalize">
                        {{ str_replace('_', ' ', $booking->source) }}
                    </p>
                </div>
                <div class="bg-amber-50 rounded-lg p-3">
                    <p class="text-xs text-amber-600 mb-1">Total Amount</p>
                    <p class="text-lg font-bold text-slate-800">
                        {{ number_format($booking->total_amount, 2) }}
                    </p>
                </div>
            </div>

            @if($booking->special_requests)
            <div class="mt-4 bg-blue-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-blue-600 mb-1">Special Requests</p>
                <p class="text-sm text-slate-700">{{ $booking->special_requests }}</p>
            </div>
            @endif

            @if($booking->cancellation_reason)
            <div class="mt-4 bg-red-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-red-600 mb-1">Cancellation Reason</p>
                <p class="text-sm text-slate-700">{{ $booking->cancellation_reason }}</p>
                <p class="text-xs text-slate-400 mt-1">
                    Cancelled on {{ $booking->cancelled_at->format('d M Y, H:i') }}
                </p>
            </div>
            @endif
        </div>

    </div>

    {{-- ── Right sidebar ─────────────────────────── --}}
    <div class="space-y-4">

        {{-- Room card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Room</p>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg
                    {{ match($booking->room->type) {
                        'standard'       => 'bg-slate-100 text-slate-600',
                        'deluxe'         => 'bg-amber-100 text-amber-700',
                        'family_suite'   => 'bg-blue-100 text-blue-700',
                        'business_suite' => 'bg-purple-100 text-purple-700',
                    } }}">
                    {{ $booking->room->number }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Room {{ $booking->room->number }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $booking->room->type_display }} · Floor {{ $booking->room->floor }}
                    </p>
                    <p class="text-xs text-slate-400">Capacity: {{ $booking->room->capacity }}</p>
                </div>
            </div>
            <a href="{{ route('rooms.show', $booking->room) }}"
               class="mt-3 inline-block text-xs text-amber-600 hover:text-amber-700 font-medium">
                View room details →
            </a>
        </div>

        {{-- Guest card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Guest</p>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center
                            text-sm font-bold text-blue-600 flex-shrink-0">
                    {{ strtoupper(substr($booking->guest->first_name, 0, 1)) }}{{ strtoupper(substr($booking->guest->last_name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ $booking->guest->full_name }}</p>
                    <p class="text-xs text-slate-400">{{ $booking->guest->phone }}</p>
                </div>
            </div>
            <dl class="space-y-1">
                @if($booking->guest->email)
                <div class="flex justify-between text-xs">
                    <dt class="text-slate-400">Email</dt>
                    <dd class="text-slate-600 font-medium">{{ $booking->guest->email }}</dd>
                </div>
                @endif
                <div class="flex justify-between text-xs">
                    <dt class="text-slate-400">{{ $booking->guest->id_type_display }}</dt>
                    <dd class="text-slate-600 font-medium font-mono">{{ $booking->guest->id_number }}</dd>
                </div>
            </dl>
            <a href="{{ route('guests.show', $booking->guest) }}"
               class="mt-3 inline-block text-xs text-amber-600 hover:text-amber-700 font-medium">
                View guest profile →
            </a>
        </div>

        {{-- Timeline --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Timeline</p>
            <div class="space-y-3">
                @php
                    $events = [
                        ['label' => 'Booking created',   'time' => $booking->created_at,     'done' => true],
                        ['label' => 'Confirmed',          'time' => $booking->created_at,     'done' => in_array($booking->status, ['confirmed','checked_in','checked_out'])],
                        ['label' => 'Guest checked in',   'time' => $booking->actual_checkin, 'done' => $booking->actual_checkin !== null],
                        ['label' => 'Guest checked out',  'time' => $booking->actual_checkout,'done' => $booking->actual_checkout !== null],
                    ];
                @endphp
                @foreach($events as $event)
                <div class="flex items-start gap-3">
                    <div class="w-5 h-5 rounded-full flex-shrink-0 mt-0.5 flex items-center justify-center
                        {{ $event['done'] ? 'bg-emerald-100' : 'bg-slate-100' }}">
                        @if($event['done'])
                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        @else
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-medium {{ $event['done'] ? 'text-slate-700' : 'text-slate-400' }}">
                            {{ $event['label'] }}
                        </p>
                        @if($event['done'] && $event['time'])
                        <p class="text-xs text-slate-400">{{ $event['time']->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

{{-- ── Cancel modal ──────────────────────────────── --}}
<div id="cancel-modal"
     class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-base font-semibold text-slate-800 mb-1">Cancel Booking</h3>
        <p class="text-sm text-slate-500 mb-4">
            This will permanently cancel booking <span class="font-mono font-semibold">{{ $booking->booking_number }}</span>
            and free up Room {{ $booking->room->number }}.
        </p>
        <form method="POST" action="{{ route('bookings.cancel', $booking) }}">
            @csrf
            <textarea name="reason" rows="3"
                      placeholder="Reason for cancellation (optional)…"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none mb-4"></textarea>
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                    Yes, Cancel Booking
                </button>
                <button type="button"
                        onclick="document.getElementById('cancel-modal').classList.add('hidden')"
                        class="flex-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium py-2.5 rounded-lg text-sm transition-colors">
                    Keep Booking
                </button>
            </div>
        </form>
    </div>
</div>

@endsection