@extends('layouts.app')

@section('title', $guest->full_name)
@section('page-title', $guest->full_name)

@section('header-actions')
<a href="{{ route('bookings.create', ['guest_id' => $guest->id]) }}"
   class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    New Booking
</a>
<a href="{{ route('guests.edit', $guest) }}"
   class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
    Edit Guest
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left: Profile + stats ───────────────── --}}
    <div class="space-y-5">

        {{-- Profile card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center">
            {{-- Avatar --}}
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600
                        flex items-center justify-center text-2xl font-bold text-white mx-auto mb-4">
                {{ strtoupper(substr($guest->first_name,0,1)) }}{{ strtoupper(substr($guest->last_name,0,1)) }}
            </div>

            <h2 class="text-lg font-bold text-slate-800">{{ $guest->full_name }}</h2>
            @if($guest->nationality)
            <p class="text-sm text-slate-500 mt-0.5">{{ $guest->nationality }}</p>
            @endif

            @if($activeBooking)
            <div class="mt-3 inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-700
                        px-3 py-1 rounded-full text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Currently in Room {{ $activeBooking->room->number }}
            </div>
            @endif

            <div class="mt-5 pt-5 border-t border-slate-100 space-y-2.5 text-sm text-left">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="text-slate-700">{{ $guest->phone }}</span>
                </div>
                @if($guest->email)
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-slate-700 truncate">{{ $guest->email }}</span>
                </div>
                @endif
                @if($guest->date_of_birth)
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-slate-700">
                        {{ $guest->date_of_birth->format('d M Y') }}
                        <span class="text-slate-400">({{ $guest->date_of_birth->age }} yrs)</span>
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- ID card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Identification</p>
            <p class="text-xs text-slate-500 mb-1">{{ $guest->id_type_display }}</p>
            <p class="text-base font-bold font-mono text-slate-800">{{ $guest->id_number }}</p>
        </div>

        {{-- Lifetime stats --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Lifetime Stats</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Total stays</span>
                    <span class="text-sm font-bold text-slate-800">{{ $stats['total_bookings'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Total nights</span>
                    <span class="text-sm font-bold text-slate-800">{{ $stats['total_nights'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Total spent</span>
                    <span class="text-sm font-bold text-amber-600">
                        {{ $stats['total_spent'] > 0 ? number_format($stats['total_spent'], 2) : '—' }}
                    </span>
                </div>
                @if($stats['favourite_type'])
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Favourite type</span>
                    <span class="text-sm font-bold text-slate-800">
                        {{ match($stats['favourite_type']) {
                            'standard'       => 'Standard',
                            'deluxe'         => 'Deluxe',
                            'family_suite'   => 'Family Suite',
                            'business_suite' => 'Business Suite',
                            default          => ucfirst($stats['favourite_type']),
                        } }}
                    </span>
                </div>
                @endif
                @if($stats['last_visit'])
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Last visit</span>
                    <span class="text-sm font-bold text-slate-800">
                        {{ \Carbon\Carbon::parse($stats['last_visit'])->format('d M Y') }}
                    </span>
                </div>
                @endif
                @if($stats['cancelled'] > 0)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Cancelled</span>
                    <span class="text-sm font-bold text-red-500">{{ $stats['cancelled'] }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Notes --}}
        @if($guest->notes)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide mb-2">Staff Notes</p>
            <p class="text-sm text-slate-700 leading-relaxed">{{ $guest->notes }}</p>
        </div>
        @endif

        {{-- Address --}}
        @if($guest->address)
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Address</p>
            <p class="text-sm text-slate-700">{{ $guest->address }}</p>
        </div>
        @endif

    </div>

    {{-- ── Right: Booking history ───────────────── --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Booking History</h3>
                <span class="text-xs text-slate-400">{{ $stats['total_bookings'] }} total</span>
            </div>

            @if($bookings->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-slate-400">No bookings yet for this guest.</p>
                <a href="{{ route('bookings.create', ['guest_id' => $guest->id]) }}"
                   class="mt-2 inline-block text-sm text-amber-600 hover:text-amber-700 font-medium">
                    Create first booking →
                </a>
            </div>
            @else

            <div class="divide-y divide-slate-100">
                @foreach($bookings as $booking)
                <div class="px-6 py-4 flex items-start gap-4 hover:bg-slate-50 transition-colors">

                    {{-- Room badge --}}
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center
                                text-xs font-bold mt-0.5
                        {{ match($booking->room->type) {
                            'standard'       => 'bg-slate-100 text-slate-600',
                            'deluxe'         => 'bg-amber-100 text-amber-700',
                            'family_suite'   => 'bg-blue-100 text-blue-700',
                            'business_suite' => 'bg-purple-100 text-purple-700',
                        } }}">
                        {{ $booking->room->number }}
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('bookings.show', $booking) }}"
                               class="text-sm font-semibold font-mono text-amber-600 hover:text-amber-700">
                                {{ $booking->booking_number }}
                            </a>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                bg-{{ $booking->status_color }}-100 text-{{ $booking->status_color }}-700">
                                {{ $booking->status_display }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $booking->room->type_display }} · Floor {{ $booking->room->floor }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $booking->checkin_date?->format('d M Y') ?? '-' }}
                            →
                            {{ $booking->checkout_date?->format('d M Y') ?? '-' }}
                            <span class="text-slate-300 mx-1">·</span>
                            {{ $booking->nights }} night{{ $booking->nights > 1 ? 's' : '' }}
                        </p>
                        @if($booking->special_requests)
                        <p class="text-xs text-slate-400 mt-1 italic truncate">
                            "{{ $booking->special_requests }}"
                        </p>
                        @endif
                    </div>

                    {{-- Amount + date --}}
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-slate-800">
                            {{ number_format($booking->total_amount, 2) }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $booking->created_at->format('d M Y') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>

            @if($bookings->hasPages())
            <div class="px-6 py-3 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>

</div>
@endsection