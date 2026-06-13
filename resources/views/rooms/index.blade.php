@extends('layouts.app')

@section('title', 'Rooms')
@section('page-title', 'Rooms')

@section('header-actions')
@if(auth('employee')->user()->hasRole(['admin','manager']))
<a href="{{ route('rooms.create') }}"
   class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add Room
</a>
@endif
@endsection

@section('content')

{{-- ── Status summary bar ───────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @php
        $statCards = [
            ['label' => 'Available',   'status' => 'available',   'color' => 'emerald'],
            ['label' => 'Maintenance', 'status' => 'maintenance', 'color' => 'slate'],
        ];
    @endphp
    @foreach($statCards as $card)
    <a href="{{ route('rooms.index', ['status' => $card['status']]) }}"
       class="bg-white rounded-xl border border-slate-200 p-4 hover:border-{{ $card['color'] }}-300 transition-colors group">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-medium text-slate-500">{{ $card['label'] }}</span>
            <div class="w-2 h-2 rounded-full bg-{{ $card['color'] }}-400"></div>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $summary[$card['status']] ?? 0 }}</p>
    </a>
    @endforeach
</div>

{{-- ── Filters ──────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-center">

        <input type="text" name="search" value="{{ $search }}"
               placeholder="Room number…"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-36">

        <select name="type" onchange="this.form.submit()"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
            <option value="">All types</option>
            @foreach(['standard' => 'Standard', 'deluxe' => 'Deluxe', 'family_suite' => 'Family Suite', 'business_suite' => 'Business Suite'] as $v => $l)
                <option value="{{ $v }}" @selected($type === $v)>{{ $l }}</option>
            @endforeach
        </select>

        <select name="status" onchange="this.form.submit()"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
            <option value="">All statuses</option>
            @foreach(['available', 'maintenance'] as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>

        <select name="floor" onchange="this.form.submit()"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
            <option value="">All floors</option>
            @foreach($floors as $f)
                <option value="{{ $f }}" @selected($floor == $f)>Floor {{ $f }}</option>
            @endforeach
        </select>

        <button type="submit"
                class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Search
        </button>

        @if($search || $type || $status || $floor)
            <a href="{{ route('rooms.index') }}"
               class="text-sm text-slate-400 hover:text-slate-600">Clear filters</a>
        @endif
    </form>
</div>

{{-- ── Room grid ───────────────────────────────────────────────── --}}
@if($rooms->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 py-20 text-center">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <p class="text-slate-400 text-sm">No rooms found matching your filters.</p>
        @if(auth('employee')->user()->hasRole(['admin','manager']))
        <a href="{{ route('rooms.create') }}"
           class="mt-4 inline-block text-sm text-amber-600 hover:text-amber-700 font-medium">Add the first room →</a>
        @endif
    </div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @foreach($rooms as $room)
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-md transition-shadow
                {{ !$room->is_active ? 'opacity-60' : '' }}">

        {{-- Room image / placeholder --}}
        <div class="relative h-36 bg-gradient-to-br
            {{ match($room->type) {
                'standard'       => 'from-slate-100 to-slate-200',
                'deluxe'         => 'from-amber-50 to-amber-100',
                'family_suite'   => 'from-blue-50 to-blue-100',
                'business_suite' => 'from-purple-50 to-purple-100',
            } }}">

            @if($room->image)
                <img src="{{ $room->image_url }}" alt="Room {{ $room->number }}"
                     class="w-full h-full object-cover">
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-slate-500">{{ $room->number }}</p>
                        <p class="text-xs text-slate-400 mt-1">Floor {{ $room->floor }}</p>
                    </div>
                </div>
            @endif

            {{-- Status badge --}}
            <div class="absolute top-2 right-2">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                    bg-{{ $room->status_color }}-100 text-{{ $room->status_color }}-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $room->status_color }}-500"></span>
                    {{ $room->status_display }}
                </span>
            </div>

            {{-- Inactive overlay --}}
            @if(!$room->is_active)
            <div class="absolute inset-0 bg-white/60 flex items-center justify-center">
                <span class="text-xs font-semibold text-slate-500 bg-white px-2 py-1 rounded shadow-sm">Inactive</span>
            </div>
            @endif
        </div>

        {{-- Card body --}}
        <div class="p-4">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Room {{ $room->number }}</h3>
                    <p class="text-xs text-slate-500">{{ $room->type_display }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-800">
                        {{ number_format($room->price_per_night, 0) }}
                    </p>
                    <p class="text-xs text-slate-400">/night</p>
                </div>
            </div>

            <div class="flex items-center gap-3 text-xs text-slate-400 mb-4">
                <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20H7m10 0a3 3 0 003-3V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a3 3 0 003 3zm0 0v-8a2 2 0 00-2-2H9a2 2 0 00-2 2v8"/>
                    </svg>
                    {{ $room->capacity }} guests
                </span>
                <span>·</span>
                <span>Floor {{ $room->floor }}</span>
            </div>

            {{-- Amenity pills (first 3) --}}
            @if(!empty($room->amenities))
            <div class="flex flex-wrap gap-1 mb-4">
                @foreach(array_slice($room->amenities, 0, 3) as $amenity)
                <span class="text-xs bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">{{ $amenity }}</span>
                @endforeach
                @if(count($room->amenities) > 3)
                <span class="text-xs text-slate-400">+{{ count($room->amenities) - 3 }}</span>
                @endif
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex gap-2">
                <a href="{{ route('rooms.show', $room) }}"
                   class="flex-1 text-center text-xs font-semibold py-1.5 rounded-lg
                          bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                    View
                </a>
                @if(auth('employee')->user()->hasRole(['admin','manager']))
                <a href="{{ route('rooms.edit', $room) }}"
                   class="flex-1 text-center text-xs font-semibold py-1.5 rounded-lg
                          bg-amber-50 hover:bg-amber-100 text-amber-700 transition-colors">
                    Edit
                </a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if($rooms->hasPages())
<div class="mt-6">
    {{ $rooms->links() }}
</div>
@endif
@endif

@endsection