@extends('layouts.app')

@section('title', 'Room ' . $room->number)
@section('page-title', 'Room ' . $room->number)

@section('header-actions')
    {{-- Quick status buttons --}}
    @if(auth('employee')->user()->hasRole(['admin','manager','receptionist']))
    <div class="flex items-center gap-2">
        <span class="text-xs text-slate-400">Quick status:</span>
        @foreach(['available' => 'emerald', 'maintenance' => 'slate'] as $s => $c)
        @if($room->status !== $s)
        <form method="POST" action="{{ route('rooms.status', $room) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="{{ $s }}">
            <button type="submit"
                    class="text-xs font-medium px-3 py-1.5 rounded-lg border
                           border-{{ $c }}-200 bg-{{ $c }}-50 text-{{ $c }}-700
                           hover:bg-{{ $c }}-100 transition-colors">
                {{ ucfirst($s) }}
            </button>
        </form>
        @endif
        @endforeach
    </div>
    @endif

    @if(auth('employee')->user()->hasRole(['admin','manager']))
    <a href="{{ route('rooms.edit', $room) }}"
       class="inline-flex items-center gap-1.5 bg-amber-400 hover:bg-amber-300 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
        Edit Room
    </a>
    @endif
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left: Room info ──────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Room hero card --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            @if($room->image)
                <img src="{{ $room->image_url }}" alt="Room {{ $room->number }}"
                     class="w-full h-56 object-cover">
            @else
                <div class="w-full h-48 bg-gradient-to-br
                    {{ match($room->type) {
                        'standard'       => 'from-slate-100 to-slate-200',
                        'deluxe'         => 'from-amber-50 to-amber-100',
                        'family_suite'   => 'from-blue-50 to-blue-100',
                        'business_suite' => 'from-purple-50 to-purple-100',
                    } }} flex items-center justify-center">
                    <p class="text-5xl font-bold text-slate-400">{{ $room->number }}</p>
                </div>
            @endif

            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-2xl font-bold text-slate-800">Room {{ $room->number }}</h2>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                bg-{{ $room->status_color }}-100 text-{{ $room->status_color }}-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-{{ $room->status_color }}-500"></span>
                                {{ $room->status_display }}
                            </span>
                            @if(!$room->is_active)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                Inactive
                            </span>
                            @endif
                        </div>
                        <p class="text-slate-500">{{ $room->type_display }} · Floor {{ $room->floor }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-slate-800">{{ number_format($room->price_per_night, 2) }}</p>
                        <p class="text-sm text-slate-400">per night</p>
                    </div>
                </div>

                {{-- Key facts --}}
                <div class="grid grid-cols-3 gap-4 py-4 border-y border-slate-100 mb-4">
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-800">{{ $room->capacity }}</p>
                        <p class="text-xs text-slate-400">Max guests</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-800">{{ $room->floor }}</p>
                        <p class="text-xs text-slate-400">Floor</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-800">{{ $room->bookings->count() }}</p>
                        <p class="text-xs text-slate-400">Total bookings</p>
                    </div>
                </div>

                @if($room->description)
                <p class="text-sm text-slate-600 leading-relaxed">{{ $room->description }}</p>
                @endif
            </div>
        </div>

        {{-- Amenities --}}
        @if(!empty($room->amenities))
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-4">Amenities</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($room->amenities as $amenity)
                <span class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200
                             text-slate-700 text-sm px-3 py-1.5 rounded-full">
                    <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ $amenity }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Recent bookings --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Recent Bookings</h3>
            </div>
            @if($room->bookings->isEmpty())
            <div class="px-6 py-8 text-center">
                <p class="text-sm text-slate-400">No bookings yet for this room.</p>
            </div>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($room->bookings as $booking)
                <div class="px-6 py-3 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('bookings.show', $booking) }}"
                               class="text-sm font-mono font-medium text-amber-600 hover:underline">
                                {{ $booking->booking_number }}
                            </a>
                            @php
                                $bc = ['pending'=>'yellow','confirmed'=>'blue','checked_in'=>'green','checked_out'=>'slate','cancelled'=>'red'][$booking->status] ?? 'slate';
                            @endphp
                            <span class="text-xs bg-{{ $bc }}-100 text-{{ $bc }}-700 px-1.5 py-0.5 rounded-full font-medium">
                                {{ ucwords(str_replace('_',' ',$booking->status)) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $booking->guest->full_name }} ·
                            {{ $booking->checkin_date?->format('d M') ?? '-' }} -
                            {{ $booking->checkout_date?->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                    <p class="text-sm font-medium text-slate-700">
                        {{ number_format($booking->total_amount, 2) }}
                    </p>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    {{-- ── Right sidebar ─────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Current booking (should be implemented with a booking relationship) --}}
        @if($room->current_booking)
        @php $cb = $room->current_booking; @endphp
        <div class="bg-red-50 border border-red-200 rounded-xl p-5">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-3">Current Booking</p>
            <p class="text-sm font-semibold text-slate-800">{{ $cb->guest->full_name }}</p>
            <p class="text-xs text-slate-500 mt-1">
                Check-out: {{ $cb->checkout_date->format('d M Y') }}
            </p>
            <a href="{{ route('bookings.show', $cb) }}"
               class="mt-3 inline-block text-xs text-red-600 hover:text-red-700 font-medium">
                View booking →
            </a>
        </div>
        @endif

        {{-- Danger zone: delete --}}
        @if(auth('employee')->user()->hasRole(['admin','manager']))
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h4 class="text-sm font-semibold text-slate-700 mb-3">Actions</h4>

            {{-- Toggle active --}}
            <form method="POST" action="{{ route('rooms.toggle', $room) }}" class="mb-2">
                @csrf @method('PATCH')
                <button type="submit"
                        class="w-full text-sm font-medium px-4 py-2 rounded-lg border transition-colors
                               {{ $room->is_active
                                    ? 'border-slate-200 text-slate-600 hover:bg-slate-50'
                                    : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    {{ $room->is_active ? 'Deactivate Room' : 'Activate Room' }}
                </button>
            </form>

            {{-- Delete --}}
            <form method="POST" action="{{ route('rooms.destroy', $room) }}"
                  onsubmit="return confirm('Delete Room {{ $room->number }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full text-sm font-medium px-4 py-2 rounded-lg border
                               border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                    Delete Room
                </button>
            </form>
        </div>
        @endif

        {{-- Meta --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Info</h4>
            <dl class="space-y-2">
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-500">Created</dt>
                    <dd class="text-slate-700 font-medium">{{ $room->created_at->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-500">Last updated</dt>
                    <dd class="text-slate-700 font-medium">{{ $room->updated_at->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-500">Room ID</dt>
                    <dd class="text-slate-400 font-mono text-xs">#{{ $room->id }}</dd>
                </div>
            </dl>
        </div>
    </div>

</div>
@endsection