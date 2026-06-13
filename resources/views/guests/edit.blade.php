@extends('layouts.app')

@section('title', 'Edit ' . $guest->full_name)
@section('page-title', 'Edit Guest')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('guests.update', $guest) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Personal Information</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name', $guest->first_name) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                @error('first_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                <input type="text" name="last_name" value="{{ old('last_name', $guest->last_name) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                @error('last_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone', $guest->phone) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', $guest->email) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nationality</label>
                <input type="text" name="nationality" value="{{ old('nationality', $guest->nationality) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Date of Birth</label>
                <input type="date" name="date_of_birth"
                       value="{{ old('date_of_birth', $guest->date_of_birth?->format('Y-m-d')) }}"
                       max="{{ now()->subYears(1)->format('Y-m-d') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Identification</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ID Type <span class="text-red-500">*</span></label>
                <select name="id_type" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                    @foreach(['national_id'=>'National ID','passport'=>'Passport','drivers_license'=>"Driver's License"] as $v => $l)
                        <option value="{{ $v }}" @selected(old('id_type', $guest->id_type) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ID Number <span class="text-red-500">*</span></label>
                <input type="text" name="id_number" value="{{ old('id_number', $guest->id_number) }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" required>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Additional Details</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Address</label>
                <textarea name="address" rows="2"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none">{{ old('address', $guest->address) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Internal Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none">{{ old('notes', $guest->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit"
                class="bg-amber-400 hover:bg-amber-300 text-slate-900 font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            Save Changes
        </button>
        <a href="{{ route('guests.show', $guest) }}"
           class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium px-6 py-2.5 rounded-lg text-sm transition-colors">
            Cancel
        </a>
    </div>
</form>
</div>
@endsection