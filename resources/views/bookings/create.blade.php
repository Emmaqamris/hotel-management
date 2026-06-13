@extends('layouts.app')

@section('title', 'New Booking')
@section('page-title', 'New Booking')

@section('content')
<div class="max-w-4xl">

{{-- ── Step 1: Search form ─────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">
        Search Available Rooms
    </h2>

    <form method="GET"
          action="{{ route('bookings.create') }}"
          class="grid grid-cols-2 sm:grid-cols-4 gap-4">

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Check-in</label>
            <input type="date" name="checkin_date"
                   value="{{ $checkin }}"
                   min="{{ today()->format('Y-m-d') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-amber-400"
                   required>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Check-out</label>
            <input type="date" name="checkout_date"
                   value="{{ $checkout }}"
                   min="{{ today()->addDay()->format('Y-m-d') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-amber-400"
                   required>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Adults</label>
            <input type="number" name="adults"
                   value="{{ old('adults', $adults ?? 1) }}"
                   min="1" max="10"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Room Type</label>
            <select name="type"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                <option value="">Any type</option>
                @foreach([
                    'standard'       => 'Standard',
                    'deluxe'         => 'Deluxe',
                    'family_suite'   => 'Family Suite',
                    'business_suite' => 'Business Suite',
                ] as $v => $l)
                    <option value="{{ $v }}" @selected(($type ?? '')=== $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-span-2 sm:col-span-4">
            <button type="submit"
                    class="bg-slate-800 hover:bg-slate-700 text-white text-sm
                           font-medium px-5 py-2 rounded-lg transition-colors">
                Search Rooms
            </button>
        </div>
    </form>
</div>

{{-- ── Step 2: Booking form ─────────────────────────────────── --}}
<form method="POST"
      action="{{ route('bookings.store') }}"
      id="booking-form">
@csrf

{{-- These carry the searched dates into the POST --}}
<input type="hidden" name="checkin_date"  value="{{ $checkin }}">
<input type="hidden" name="checkout_date" value="{{ $checkout }}">

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Room list (left 2/3) ───────────────────────────── --}}
    <div class="lg:col-span-2">

        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">
            Available Rooms
            <span class="ml-2 text-amber-600 font-bold">{{ $availableRooms->count() }}</span>
        </h3>

        @if($availableRooms->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
            <p class="text-sm font-medium text-amber-800">No rooms available for these dates.</p>
            <p class="text-xs text-amber-600 mt-1">Try different dates or remove the type filter.</p>
        </div>

        @else
        <div class="space-y-3">
            @foreach($availableRooms as $room)
            <label class="flex items-center gap-4 p-4 bg-white border-2 rounded-xl
                          cursor-pointer border-slate-200
                          hover:border-amber-400 hover:bg-amber-50
                          has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50
                          transition-colors">

                {{-- FIX 1: onchange calls updateTotal() which is now defined below --}}
                <input type="radio"
                       name="room_id"
                       value="{{ $room->id }}"
                       data-rate="{{ $room->price_per_night }}"
                       class="text-amber-400 focus:ring-amber-400 cursor-pointer"
                       {{ old('room_id') == $room->id ? 'checked' : '' }}
                       onchange="updateTotal({{ $room->price_per_night }})">

                {{-- Colour swatch --}}
                <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center
                            justify-center font-bold text-sm
                    {{ match($room->type) {
                        'standard'       => 'bg-slate-100 text-slate-600',
                        'deluxe'         => 'bg-amber-100 text-amber-700',
                        'family_suite'   => 'bg-blue-100 text-blue-700',
                        'business_suite' => 'bg-purple-100 text-purple-700',
                    } }}">
                    {{ $room->number }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-slate-800">
                            Room {{ $room->number }}
                        </span>
                        <span class="text-xs bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">
                            {{ $room->type_display }}
                        </span>
                        <span class="text-xs text-slate-400">Floor {{ $room->floor }}</span>
                    </div>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-xs text-slate-400">Up to {{ $room->capacity }} guests</span>
                        @if(!empty($room->amenities))
                        <span class="text-xs text-slate-300">·</span>
                        <span class="text-xs text-slate-400">
                            {{ implode(', ', array_slice($room->amenities, 0, 3)) }}
                            @if(count($room->amenities) > 3)
                                +{{ count($room->amenities) - 3 }} more
                            @endif
                        </span>
                        @endif
                    </div>
                </div>

                <div class="text-right flex-shrink-0">
                    <p class="text-base font-bold text-slate-800">
                        {{ number_format($room->price_per_night, 2) }}
                    </p>
                    <p class="text-xs text-slate-400">/night</p>
                </div>
            </label>
            @endforeach
        </div>

        @error('room_id')
            <p class="mt-2 text-sm text-red-500 bg-red-50 px-3 py-2 rounded-lg">
                {{ $message }}
            </p>
        @enderror
        @endif
    </div>

    {{-- ── Right sidebar ───────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Guest selector --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-700">Guest</h3>
                <a href="{{ route('guests.create', ['quick' => 1]) }}"
                   target="_blank"
                   class="text-xs text-amber-600 hover:text-amber-700 font-medium">
                    + New guest
                </a>
            </div>

            {{-- Live search input --}}
            <div class="relative mb-3">
                <input type="text" id="guest-search"
                       placeholder="Search by name, phone or ID…"
                       autocomplete="off"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2
                              text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 pr-8">
                <div id="guest-loading" class="hidden absolute right-2.5 top-2.5">
                    <svg class="w-4 h-4 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </div>

            {{-- Search results dropdown --}}
            <div id="guest-results"
                 class="hidden border border-slate-200 rounded-lg divide-y divide-slate-100
                        mb-3 max-h-52 overflow-y-auto">
            </div>

            {{-- Selected guest display --}}
            <div id="guest-selected"
                 class="hidden bg-slate-50 rounded-lg p-3 flex items-center gap-3">
                <div id="guest-avatar"
                     class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center
                            text-sm font-bold text-blue-600 flex-shrink-0">
                </div>
                <div class="flex-1 min-w-0">
                    <p id="guest-name" class="text-sm font-semibold text-slate-800"></p>
                    <p id="guest-phone" class="text-xs text-slate-400"></p>
                </div>
                <button type="button" onclick="clearGuest()"
                        class="text-xs text-slate-400 hover:text-red-500">
                    Change
                </button>
            </div>

            {{--
                FIX 2: Fallback dropdown now has onchange to keep the hidden
                input in sync when a user picks from the list directly.
            --}}
            <select id="guest-id-select"
                    class="{{ old('guest_id') ? '' : 'hidden' }}
                           w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white"
                    onchange="document.getElementById('guest-id-value').value = this.value">
                <option value="">Select guest…</option>
                @foreach($guests as $g)
                    <option value="{{ $g->id }}" @selected(old('guest_id') == $g->id)>
                        {{ $g->full_name }} — {{ $g->phone }}
                    </option>
                @endforeach
            </select>

            {{-- This hidden input is what actually gets submitted --}}
            <input type="hidden"
                   name="guest_id"
                   id="guest-id-value"
                   value="{{ old('guest_id') }}">

            @error('guest_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{--
            FIX 3: Guest count inputs.
            The search form passes adults as a query param,
            but we need them as real POST fields here.
        --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Guest Count</h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Adults</label>
                    <input type="number" name="adults"
                           value="{{ old('adults', $adults) }}"
                           min="1" max="10"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2
                                  text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @error('adults')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Children</label>
                    <input type="number" name="children"
                           value="{{ old('children', 0) }}"
                           min="0" max="10"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2
                                  text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>
        </div>

        {{--
            FIX 4: Booking details (source + special requests).
            These were completely missing from the document version.
        --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Details</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Source</label>
                    <select name="source"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2
                                   text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                        @foreach([
                            'walk_in' => 'Walk-in',
                            'phone'   => 'Phone',
                            'online'  => 'Online',
                            'ota'     => 'OTA',
                        ] as $v => $l)
                            <option value="{{ $v }}"
                                    @selected(old('source', 'walk_in') === $v)>
                                {{ $l }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Special Requests</label>
                    <textarea name="special_requests" rows="3"
                              placeholder="Any special needs…"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2
                                     text-sm focus:outline-none focus:ring-2
                                     focus:ring-amber-400 resize-none">{{ old('special_requests') }}</textarea>
                </div>
            </div>
        </div>

        {{--
            FIX 5: Price summary card.
            Was completely missing — the JS updateTotal() writes into these elements.
        --}}
        <div class="bg-slate-900 rounded-xl p-5 text-white">
            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-4">
                Price Summary
            </h3>

            <div class="space-y-2 mb-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-400">Check-in</span>
                    <span class="font-medium">
                        {{ \Carbon\Carbon::parse($checkin)->format('d M Y') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Check-out</span>
                    <span class="font-medium">
                        {{ \Carbon\Carbon::parse($checkout)->format('d M Y') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Nights</span>
                    <span id="summary-nights" class="font-medium">
                        {{ \Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout)) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Rate/night</span>
                    <span id="summary-rate" class="font-medium">—</span>
                </div>
                <div class="flex justify-between border-t border-slate-700 pt-2">
                    <span class="text-slate-400">Subtotal</span>
                    <span id="summary-subtotal" class="font-medium">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Tax (16%)</span>
                    <span id="summary-tax" class="font-medium">—</span>
                </div>
            </div>

            <div class="border-t border-slate-700 pt-3 flex justify-between items-center">
                <span class="text-sm font-semibold text-slate-300">Total</span>
                <span id="summary-total" class="text-xl font-bold text-amber-400">—</span>
            </div>

            <p id="select-room-hint" class="text-xs text-slate-500 mt-3 text-center">
                Select a room above to see the total
            </p>
        </div>

        {{--
            FIX 6: Submit button and cancel link.
            Were completely missing — the form could never be submitted.
        --}}
        @if($availableRooms->isNotEmpty())
        <button type="submit"
                class="w-full bg-amber-400 hover:bg-amber-300 text-slate-900
                       font-bold py-3 rounded-xl text-sm transition-colors">
            Confirm Booking
        </button>
        @endif

        <a href="{{ route('bookings.index') }}"
           class="block text-center text-sm text-slate-400 hover:text-slate-600">
            Cancel
        </a>

    </div>{{-- end right sidebar --}}
</div>{{-- end grid --}}
</form>
</div>{{-- end max-w-4xl --}}

@push('scripts')
<script>
// ─────────────────────────────────────────────────────────────
//  FIX 1: updateTotal() — was missing, now fully implemented
// ─────────────────────────────────────────────────────────────
const nights   = {{ \Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout)) }};
const TAX_RATE = 0.16;

function fmt(n) {
    return parseFloat(n).toLocaleString('en-US', {
        minimumFractionDigits:  2,
        maximumFractionDigits:  2,
    });
}

function updateTotal(rate) {
    const subtotal = rate * nights;
    const tax      = subtotal * TAX_RATE;
    const total    = subtotal + tax;

    document.getElementById('summary-rate').textContent     = fmt(rate);
    document.getElementById('summary-subtotal').textContent = fmt(subtotal);
    document.getElementById('summary-tax').textContent      = fmt(tax);
    document.getElementById('summary-total').textContent    = fmt(total);
    document.getElementById('select-room-hint').classList.add('hidden');
}

// Restore total if the page was reloaded after a validation error
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('input[name="room_id"]:checked');
    if (checked) {
        updateTotal(parseFloat(checked.dataset.rate));
    }
});

// ─────────────────────────────────────────────────────────────
//  Live Guest Search
// ─────────────────────────────────────────────────────────────
const searchInput = document.getElementById('guest-search');
const resultsBox  = document.getElementById('guest-results');
const selectedBox = document.getElementById('guest-selected');
const loadingIcon = document.getElementById('guest-loading');
const hiddenInput = document.getElementById('guest-id-value');
let   searchTimer;

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    const q = searchInput.value.trim();

    if (q.length < 2) {
        resultsBox.classList.add('hidden');
        resultsBox.innerHTML = '';
        return;
    }

    loadingIcon.classList.remove('hidden');

    searchTimer = setTimeout(async () => {
        try {
            const res  = await fetch(
                `{{ route('guests.search') }}?q=${encodeURIComponent(q)}`,
                { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
            );
            const data = await res.json();

            loadingIcon.classList.add('hidden');

            if (!data.length) {
                resultsBox.innerHTML = `
                    <div class="px-4 py-3 text-sm text-slate-400 text-center">
                        No guests found.
                        <a href="{{ route('guests.create', ['quick'=>1]) }}"
                           target="_blank"
                           class="text-amber-600 hover:underline ml-1">
                           Register now →
                        </a>
                    </div>`;
                resultsBox.classList.remove('hidden');
                return;
            }

            resultsBox.innerHTML = data.map(g => `
                <div class="px-4 py-3 flex items-center gap-3 hover:bg-amber-50
                            cursor-pointer transition-colors"
                     onclick="selectGuest(
                         ${g.id},
                         '${escHtml(g.name)}',
                         '${escHtml(g.phone)}'
                     )">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center
                                justify-center text-xs font-bold text-blue-600 flex-shrink-0">
                        ${g.name.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase()}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800">
                            ${escHtml(g.name)}
                        </p>
                        <p class="text-xs text-slate-400">
                            ${escHtml(g.phone)}
                            ${g.id_number ? ' · ' + escHtml(g.id_number) : ''}
                        </p>
                    </div>
                </div>
            `).join('');

            resultsBox.classList.remove('hidden');

        } catch (e) {
            loadingIcon.classList.add('hidden');
            console.error('Guest search failed', e);
        }
    }, 300);
});

function selectGuest(id, name, phone) {
    hiddenInput.value = id;
    document.getElementById('guest-name').textContent  = name;
    document.getElementById('guest-phone').textContent = phone;
    document.getElementById('guest-avatar').textContent =
        name.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase();

    selectedBox.classList.remove('hidden');
    searchInput.closest('.relative').classList.add('hidden');
    resultsBox.classList.add('hidden');
    resultsBox.innerHTML = '';
}

function clearGuest() {
    hiddenInput.value = '';
    selectedBox.classList.add('hidden');
    searchInput.closest('.relative').classList.remove('hidden');
    searchInput.value = '';
    searchInput.focus();
}

function escHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Close results when clicking outside
document.addEventListener('click', (e) => {
    if (!resultsBox.contains(e.target) && e.target !== searchInput) {
        resultsBox.classList.add('hidden');
    }
});
</script>
@endpush

@endsection