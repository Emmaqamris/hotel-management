@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('header-actions')
<a href="{{ route('bookings.create') }}"
   class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    New Booking
</a>
@endsection

@section('content')

{{-- ── Status summary bar ───────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    @php
        $cards = [
            ['label'=>'Pending',     'status'=>'pending',     'color'=>'yellow'],
            ['label'=>'Confirmed',   'status'=>'confirmed',   'color'=>'blue'],
            ['label'=>'Checked In',  'status'=>'checked_in',  'color'=>'green'],
            ['label'=>'Checked Out', 'status'=>'checked_out', 'color'=>'slate'],
            ['label'=>'Cancelled',   'status'=>'cancelled',   'color'=>'red'],
            ['label'=>'No Show',     'status'=>'no_show',     'color'=>'orange'],
        ];
    @endphp
    @foreach($cards as $card)
    <a href="{{ route('bookings.index', ['status' => $card['status']]) }}"
       class="bg-white rounded-xl border p-3 text-center
              {{ $status === $card['status'] ? 'border-'.$card['color'].'-400 ring-1 ring-'.$card['color'].'-400' : 'border-slate-200' }}
              hover:border-{{ $card['color'] }}-300 transition-colors">
        <p class="text-xl font-bold text-slate-800">{{ $summary[$card['status']] ?? 0 }}</p>
        <p class="text-xs text-slate-500 mt-0.5">{{ $card['label'] }}</p>
    </a>
    @endforeach
</div>

{{-- ── Filters ──────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Booking #, guest name, room…"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 flex-1 min-w-48">

        <select name="status" onchange="this.form.submit()"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
            <option value="">All statuses</option>
            @foreach(['pending','confirmed','checked_in','checked_out','cancelled','no_show'] as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>

        <input type="date" name="date" value="{{ $date }}"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
               title="Filter by check-in date">

        <button type="submit"
                class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Search
        </button>

        @if($search || $status || $date)
        <a href="{{ route('bookings.index') }}" class="text-sm text-slate-400 hover:text-slate-600">Clear</a>
        @endif
    </form>
</div>

{{-- ── Bookings table ───────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50">
            <tr>
                @foreach(['Booking #','Guest','Room','Check-in','Check-out','Nights','Amount','Status',''] as $h)
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @forelse($bookings as $booking)
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-4 py-3">
                <a href="{{ route('bookings.show', $booking) }}"
                   class="text-sm font-mono font-semibold text-amber-600 hover:text-amber-700">
                    {{ $booking->booking_number }}
                </a>
            </td>
            <td class="px-4 py-3">
                <p class="text-sm font-medium text-slate-800">{{ $booking->guest->full_name }}</p>
                <p class="text-xs text-slate-400">{{ $booking->guest->phone }}</p>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm font-medium text-slate-700">{{ $booking->room->number }}</span>
                <span class="text-xs text-slate-400 ml-1">{{ $booking->room->type_display }}</span>
            </td>
            <td class="px-4 py-3 text-sm text-slate-600">
                {{ $booking->checkin_date?->format('d M Y') }}
            </td>
            <td class="px-4 py-3 text-sm text-slate-600">
                {{ $booking->checkout_date?->format('d M Y') }}
            </td>
            <td class="px-4 py-3 text-sm text-slate-600">{{ $booking->nights }}</td>
            <td class="px-4 py-3 text-sm font-semibold text-slate-800">
                {{ number_format($booking->total_amount, 2) }}
            </td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                    bg-{{ $booking->status_color }}-100 text-{{ $booking->status_color }}-700">
                    {{ $booking->status_display }}
                </span>
            </td>
            <td class="px-4 py-3">
                <a href="{{ route('bookings.show', $booking) }}"
                   class="text-xs text-slate-400 hover:text-amber-600 font-medium">
                    View →
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="px-4 py-16 text-center">
                <p class="text-sm text-slate-400">No bookings found.</p>
                <a href="{{ route('bookings.create') }}"
                   class="mt-2 inline-block text-sm text-amber-600 hover:text-amber-700 font-medium">
                    Create first booking →
                </a>
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>

    @if($bookings->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $bookings->links() }}
    </div>
    @endif
</div>

@endsection