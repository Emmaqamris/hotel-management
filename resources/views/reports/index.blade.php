@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')

@section('content')

{{-- ── Quick stats at a glance ─────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $quickStats = [
            ['label' => 'Occupancy Rate',    'value' => $stats['occupancyRate'].'%',               'color' => 'blue'],
            ['label' => 'Revenue This Month', 'value' => number_format($stats['revenueThisMonth'], 2), 'color' => 'emerald'],
            ['label' => 'ADR',               'value' => number_format($stats['adr'], 2),            'color' => 'amber'],
            ['label' => 'RevPAR',            'value' => number_format($stats['revpar'], 2),         'color' => 'purple'],
        ];
    @endphp
    @foreach($quickStats as $card)
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs font-medium text-slate-400 mb-1">{{ $card['label'] }}</p>
        <p class="text-2xl font-bold text-{{ $card['color'] }}-600">
            {{ $card['value'] }}
        </p>
    </div>
    @endforeach
</div>

{{-- ── Report cards ────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    <a href="{{ route('reports.occupancy') }}"
       class="bg-white rounded-xl border border-slate-200 p-6 hover:border-blue-300
              hover:shadow-sm transition-all group">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center
                    justify-center mb-4 group-hover:bg-blue-200 transition-colors">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Occupancy Report</h3>
        <p class="text-sm text-slate-500 mb-4">
            Daily occupancy rates, room type breakdown, and occupied nights by period.
        </p>
        <p class="text-xs font-semibold text-blue-600 group-hover:underline">
            View report →
        </p>
    </a>

    <a href="{{ route('reports.revenue') }}"
       class="bg-white rounded-xl border border-slate-200 p-6 hover:border-emerald-300
              hover:shadow-sm transition-all group">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center
                    justify-center mb-4 group-hover:bg-emerald-200 transition-colors">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343
                         2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1
                         c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Revenue Report</h3>
        <p class="text-sm text-slate-500 mb-4">
            Daily revenue, ADR, RevPAR, and payment method breakdown for any period.
        </p>
        <p class="text-xs font-semibold text-emerald-600 group-hover:underline">
            View report →
        </p>
    </a>

    <a href="{{ route('reports.bookings') }}"
       class="bg-white rounded-xl border border-slate-200 p-6 hover:border-amber-300
              hover:shadow-sm transition-all group">
        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center
                    justify-center mb-4 group-hover:bg-amber-200 transition-colors">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2
                         H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Bookings Report</h3>
        <p class="text-sm text-slate-500 mb-4">
            Booking trends, cancellation rates, source breakdown, and average stay length.
        </p>
        <p class="text-xs font-semibold text-amber-600 group-hover:underline">
            View report →
        </p>
    </a>

</div>

@endsection