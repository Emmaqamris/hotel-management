@extends('layouts.app')

@section('title', 'Guests')
@section('page-title', 'Guests')

@section('header-actions')
<a href="{{ route('guests.create') }}"
   class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Register Guest
</a>
@endsection

@section('content')

{{-- ── Summary cards ─────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-xs font-medium text-slate-500 mb-1">Total Guests</p>
        <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-xs font-medium text-slate-500 mb-1">New This Month</p>
        <p class="text-2xl font-bold text-blue-600">{{ $stats['new_this_month'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-xs font-medium text-slate-500 mb-1">Currently In-House</p>
        <p class="text-2xl font-bold text-emerald-600">{{ $stats['with_active_booking'] }}</p>
    </div>
</div>

{{-- ── Filters ──────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Name, phone, email, ID number…"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 flex-1 min-w-56">

        @if($nationalities->isNotEmpty())
        <select name="nationality" onchange="this.form.submit()"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
            <option value="">All nationalities</option>
            @foreach($nationalities as $nat)
                <option value="{{ $nat }}" @selected($nationality === $nat)>{{ $nat }}</option>
            @endforeach
        </select>
        @endif

        <select name="sort" onchange="this.form.submit()"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
            <option value="name"     @selected($sort==='name')>Sort: Name</option>
            <option value="recent"   @selected($sort==='recent')>Sort: Newest first</option>
            <option value="bookings" @selected($sort==='bookings')>Sort: Most bookings</option>
        </select>

        <button type="submit"
                class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Search
        </button>

        @if($search || $nationality)
        <a href="{{ route('guests.index') }}"
           class="text-sm text-slate-400 hover:text-slate-600">Clear</a>
        @endif
    </form>
</div>

{{-- ── Guests table ─────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50">
            <tr>
                @foreach(['Guest','Contact','ID','Nationality','Bookings','Total Spent','Status',''] as $h)
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    {{ $h }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @forelse($guests as $guest)
        @php
            $hasActive = $guest->bookings_count > 0 &&
                         $guest->bookings()->whereIn('status',['confirmed','checked_in'])->exists();
        @endphp
        <tr class="hover:bg-slate-50 transition-colors">

            {{-- Avatar + Name --}}
            <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center
                                text-sm font-bold text-blue-600 flex-shrink-0">
                        {{ strtoupper(substr($guest->first_name,0,1)) }}{{ strtoupper(substr($guest->last_name,0,1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $guest->full_name }}</p>
                        @if($guest->date_of_birth)
                        <p class="text-xs text-slate-400">
                            {{ $guest->date_of_birth->age }} yrs
                        </p>
                        @endif
                    </div>
                </div>
            </td>

            {{-- Contact --}}
            <td class="px-4 py-3">
                <p class="text-sm text-slate-700">{{ $guest->phone }}</p>
                @if($guest->email)
                <p class="text-xs text-slate-400 truncate max-w-40">{{ $guest->email }}</p>
                @endif
            </td>

            {{-- ID --}}
            <td class="px-4 py-3">
                <p class="text-xs font-mono text-slate-600">{{ $guest->id_number }}</p>
                <p class="text-xs text-slate-400">{{ $guest->id_type_display }}</p>
            </td>

            {{-- Nationality --}}
            <td class="px-4 py-3 text-sm text-slate-600">
                {{ $guest->nationality ?? '—' }}
            </td>

            {{-- Bookings count --}}
            <td class="px-4 py-3">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                    {{ $guest->bookings_count > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $guest->bookings_count }}
                </span>
            </td>

            {{-- Total spent --}}
            <td class="px-4 py-3 text-sm font-medium text-slate-700">
                {{ $guest->total_spent > 0 ? number_format($guest->total_spent, 2) : '—' }}
            </td>

            {{-- Status --}}
            <td class="px-4 py-3">
                @if($hasActive)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                             bg-emerald-100 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    In-house
                </span>
                @else
                <span class="text-xs text-slate-400">—</span>
                @endif
            </td>

            {{-- Actions --}}
            <td class="px-4 py-3 text-right">
                <div class="flex gap-2 justify-end">
                    <a href="{{ route('guests.show', $guest) }}"
                       class="text-xs text-slate-500 hover:text-amber-600 font-medium">
                        View
                    </a>
                    <span class="text-slate-200">|</span>
                    <a href="{{ route('bookings.create', ['guest_id' => $guest->id]) }}"
                       class="text-xs text-amber-600 hover:text-amber-700 font-medium">
                        Book
                    </a>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="px-4 py-16 text-center">
                <p class="text-sm text-slate-400">No guests found.</p>
                <a href="{{ route('guests.create') }}"
                   class="mt-2 inline-block text-sm text-amber-600 hover:text-amber-700 font-medium">
                    Register first guest →
                </a>
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>

    @if($guests->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $guests->links() }}
    </div>
    @endif
</div>

@endsection