@extends('layouts.app')

@section('title', 'Edit Room ' . $room->number)
@section('page-title', 'Edit Room ' . $room->number)

@section('content')
<div class="max-w-2xl">

<form method="POST" action="{{ route('rooms.update', $room) }}"
      enctype="multipart/form-data"
      class="space-y-6">
    @csrf
    @method('PUT')

    {{-- ── Basic Info ─────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Room Details</h2>

        <div class="grid grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Room Number <span class="text-red-500">*</span>
                </label>
                <input type="text" name="number"
                       value="{{ old('number', $room->number) }}"
                       class="w-full border {{ $errors->has('number') ? 'border-red-400' : 'border-slate-200' }}
                              rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required>
                @error('number')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Floor</label>
                <input type="number" name="floor"
                       value="{{ old('floor', $room->floor) }}"
                       min="1" max="100"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Room Type</label>
                <select name="type"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white"
                        required>
                    @foreach(['standard' => 'Standard', 'deluxe' => 'Deluxe', 'family_suite' => 'Family Suite', 'business_suite' => 'Business Suite'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('type', $room->type) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Capacity</label>
                <input type="number" name="capacity"
                       value="{{ old('capacity', $room->capacity) }}"
                       min="1" max="10"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Price Per Night</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">$</span>
                    <input type="number" name="price_per_night"
                           value="{{ old('price_per_night', $room->price_per_night) }}"
                           step="0.01" min="1"
                           class="w-full border border-slate-200 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                           required>
                </div>
                @error('price_per_night')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Status (editable only in edit form) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                <select name="status"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                    @foreach(['available', 'maintenance'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $room->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Active toggle --}}
            <div class="flex items-center">
                <label class="flex items-center gap-3 cursor-pointer mt-6">
                    <div class="relative">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $room->is_active) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-amber-400 rounded-full peer
                                    peer-checked:after:translate-x-full peer-checked:after:border-white
                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                    after:bg-white after:border-gray-300 after:border after:rounded-full
                                    after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-400"></div>
                    </div>
                    <span class="text-sm text-slate-700">Room is active</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Description --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Description</h2>
        <textarea name="description" rows="4"
                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
                  placeholder="Describe the room…">{{ old('description', $room->description) }}</textarea>
    </div>

    {{-- Amenities --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Amenities</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @php
                $allAmenities = ['WiFi','TV','AC','Hot Water','Mini Bar','Safe','Balcony','Room Service','Hair Dryer','Bath Tub','Work Desk','Lounge Area','Kitchenette','Jacuzzi'];
                $selected = old('amenities', $room->amenities ?? []);
            @endphp
            @foreach($allAmenities as $amenity)
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="amenities[]" value="{{ $amenity }}"
                       {{ in_array($amenity, $selected) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-amber-400 focus:ring-amber-400">
                <span class="text-sm text-slate-600">{{ $amenity }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Image --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Room Photo</h2>

        @if($room->image)
        <div class="mb-4 flex items-center gap-4">
            <img src="{{ $room->image_url }}" alt="Current image"
                 class="h-32 w-48 object-cover rounded-lg border border-slate-200">
            <div>
                <p class="text-xs text-slate-500 mb-2">Current photo</p>
                <p class="text-xs text-slate-400">Upload a new image below to replace it.</p>
            </div>
        </div>
        @endif

        <input type="file" name="image" accept="image/*"
               class="text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                      file:bg-amber-50 file:text-amber-700 file:font-medium hover:file:bg-amber-100 cursor-pointer">

        @error('image')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    {{-- Actions --}}
    <div class="flex gap-3">
        <button type="submit"
                class="bg-amber-400 hover:bg-amber-300 text-slate-900 font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            Save Changes
        </button>
        <a href="{{ route('rooms.show', $room) }}"
           class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium px-6 py-2.5 rounded-lg text-sm transition-colors">
            Cancel
        </a>
    </div>

</form>
</div>
@endsection