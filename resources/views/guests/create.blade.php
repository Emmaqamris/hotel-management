@extends('layouts.app')

@section('title', 'Register Guest')
@section('page-title', $isQuick ? 'Quick Register Guest' : 'Register New Guest')

@section('content')
<div class="{{ $isQuick ? 'max-w-xl' : 'max-w-2xl' }}">

@if($isQuick)
<div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex items-start gap-3">
    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-sm text-blue-700">
        After registering, you'll be taken back to the booking form with this guest pre-selected.
    </p>
</div>
@endif

<form method="POST" action="{{ route('guests.store') }}" class="space-y-6">
    @csrf
    <input type="hidden" name="quick" value="{{ $isQuick ? '1' : '0' }}">

    {{-- ── Personal Information ─────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">
            Personal Information
        </h2>

        <div class="grid grid-cols-2 gap-4">

            {{-- First Name --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="first_name"
                       value="{{ old('first_name') }}"
                       placeholder="e.g. Alice"
                       class="w-full border {{ $errors->has('first_name') ? 'border-red-400' : 'border-slate-200' }}
                              rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required autofocus>
                @error('first_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Last Name --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="last_name"
                       value="{{ old('last_name') }}"
                       placeholder="e.g. Johnson"
                       class="w-full border {{ $errors->has('last_name') ? 'border-red-400' : 'border-slate-200' }}
                              rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required>
                @error('last_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Phone <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="phone"
                       value="{{ old('phone') }}"
                       placeholder="+255 700 000 000"
                       class="w-full border {{ $errors->has('phone') ? 'border-red-400' : 'border-slate-200' }}
                              rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required>
                @error('phone')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="guest@example.com"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            {{-- Nationality --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nationality</label>
                <input type="text" name="nationality"
                       value="{{ old('nationality') }}"
                       placeholder="e.g. Tanzanian"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            {{-- Date of Birth --}}
            @if(!$isQuick)
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Date of Birth</label>
                <input type="date" name="date_of_birth"
                       value="{{ old('date_of_birth') }}"
                       max="{{ now()->subYears(1)->format('Y-m-d') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            @endif

        </div>
    </div>

    {{-- ── Identification ───────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">
            Identification
        </h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    ID Type <span class="text-red-500">*</span>
                </label>
                <select name="id_type" required
                        class="w-full border {{ $errors->has('id_type') ? 'border-red-400' : 'border-slate-200' }}
                               rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                    <option value="">— Select —</option>
                    @foreach(['national_id'=>'National ID','passport'=>'Passport','drivers_license'=>"Driver's License"] as $v => $l)
                        <option value="{{ $v }}" @selected(old('id_type') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                @error('id_type')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    ID Number <span class="text-red-500">*</span>
                </label>
                <input type="text" name="id_number"
                       value="{{ old('id_number') }}"
                       placeholder="Document number"
                       class="w-full border {{ $errors->has('id_number') ? 'border-red-400' : 'border-slate-200' }}
                              rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required>
                @error('id_number')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- ── Address & Notes (full form only) ────────── --}}
    @if(!$isQuick)
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">
            Additional Details
        </h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Address</label>
                <textarea name="address" rows="2"
                          placeholder="Home address…"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none">{{ old('address') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Internal Notes</label>
                <textarea name="notes" rows="3"
                          placeholder="VIP guest, allergies, preferences…"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none">{{ old('notes') }}</textarea>
                <p class="mt-1 text-xs text-slate-400">Only visible to staff — not shared with the guest.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Submit ───────────────────────────────── --}}
    <div class="flex gap-3">
        <button type="submit"
                class="bg-amber-400 hover:bg-amber-300 text-slate-900 font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            {{ $isQuick ? 'Register & Continue Booking' : 'Register Guest' }}
        </button>
        <a href="{{ $isQuick ? route('bookings.create') : route('guests.index') }}"
           class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium px-6 py-2.5 rounded-lg text-sm transition-colors">
            Cancel
        </a>
    </div>
</form>
</div>
@endsection