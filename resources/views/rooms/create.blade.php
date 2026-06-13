@extends('layouts.app')

@section('title', 'Add Room')
@section('page-title', 'Add New Room')

@section('content')
<div class="max-w-2xl">

<form method="POST" action="{{ route('rooms.store') }}"
      enctype="multipart/form-data"
      class="space-y-6">
    @csrf

    {{-- ── Basic Info ─────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Room Details</h2>

        <div class="grid grid-cols-2 gap-4">

            {{-- Room Number --}}
            <div>
                <label for="number" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Room Number <span class="text-red-500">*</span>
                </label>
                <input id="number" type="text" name="number"
                       value="{{ old('number') }}"
                       placeholder="e.g. 101, 2A, PH1"
                       class="w-full border {{ $errors->has('number') ? 'border-red-400' : 'border-slate-200' }}
                              rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required>
                @error('number')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Floor --}}
            <div>
                <label for="floor" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Floor <span class="text-red-500">*</span>
                </label>
                <input id="floor" type="number" name="floor"
                       value="{{ old('floor', 1) }}"
                       min="1" max="100"
                       class="w-full border {{ $errors->has('floor') ? 'border-red-400' : 'border-slate-200' }}
                              rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required>
                @error('floor')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Room Type --}}
            <div>
                <label for="type" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Room Type <span class="text-red-500">*</span>
                </label>
                <select id="type" name="type"
                        class="w-full border {{ $errors->has('type') ? 'border-red-400' : 'border-slate-200' }}
                               rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white"
                        required>
                    <option value="">— Select type —</option>
                    @foreach(['standard' => 'Standard', 'deluxe' => 'Deluxe', 'family_suite' => 'Family Suite', 'business_suite' => 'Business Suite'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('type') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Capacity --}}
            <div>
                <label for="capacity" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Capacity (guests) <span class="text-red-500">*</span>
                </label>
                <input id="capacity" type="number" name="capacity"
                       value="{{ old('capacity', 2) }}"
                       min="1" max="10"
                       class="w-full border {{ $errors->has('capacity') ? 'border-red-400' : 'border-slate-200' }}
                              rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       required>
                @error('capacity')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Price --}}
            <div class="col-span-2">
                <label for="price_per_night" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Price Per Night <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">$</span>
                    <input id="price_per_night" type="number" name="price_per_night"
                           value="{{ old('price_per_night') }}"
                           step="0.01" min="1"
                           placeholder="0.00"
                           class="w-full border {{ $errors->has('price_per_night') ? 'border-red-400' : 'border-slate-200' }}
                                  rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                           required>
                </div>
                @error('price_per_night')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    {{-- ── Description ─────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Description</h2>
        <textarea id="description" name="description" rows="4"
                  placeholder="Describe the room — view, décor, special features…"
                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                         focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none">{{ old('description') }}</textarea>
    </div>

    {{-- ── Amenities ────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Amenities</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @php
                $allAmenities = [
                    'WiFi', 'TV', 'AC', 'Hot Water', 'Mini Bar', 'Safe',
                    'Balcony', 'Room Service', 'Hair Dryer', 'Bath Tub',
                    'Work Desk', 'Lounge Area', 'Kitchenette', 'Jacuzzi',
                ];
                $selected = old('amenities', []);
            @endphp
            @foreach($allAmenities as $amenity)
            <label class="flex items-center gap-2.5 cursor-pointer group">
                <input type="checkbox"
                       name="amenities[]"
                       value="{{ $amenity }}"
                       {{ in_array($amenity, $selected) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-amber-400 focus:ring-amber-400 cursor-pointer">
                <span class="text-sm text-slate-600 group-hover:text-slate-800">{{ $amenity }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- ── Image ────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-5">Room Photo</h2>
        <div id="drop-zone"
             class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center hover:border-amber-400 transition-colors cursor-pointer">
            <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm text-slate-500 mb-1">Click to upload or drag and drop</p>
            <p class="text-xs text-slate-400">JPEG, PNG, WebP · max 2MB</p>
            <input id="image-input" type="file" name="image" accept="image/*"
                   class="hidden" onchange="previewImage(this)">
        </div>

        {{-- Image preview --}}
        <div id="image-preview" class="mt-4 hidden">
            <img id="preview-img" src="" alt="Preview"
                 class="h-40 rounded-lg object-cover border border-slate-200">
            <button type="button" onclick="clearImage()"
                    class="mt-2 text-xs text-red-500 hover:text-red-700">Remove photo</button>
        </div>

        @error('image')
            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- ── Submit ───────────────────────────────────── --}}
    <div class="flex gap-3">
        <button type="submit"
                class="bg-amber-400 hover:bg-amber-300 text-slate-900 font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            Create Room
        </button>
        <a href="{{ route('rooms.index') }}"
           class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium px-6 py-2.5 rounded-lg text-sm transition-colors">
            Cancel
        </a>
    </div>

</form>
</div>

@push('scripts')
<script>
// Click the hidden file input when the drop zone is clicked
document.getElementById('drop-zone').addEventListener('click', () => {
    document.getElementById('image-input').click();
});

// Preview the selected image
function previewImage(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('preview-img').src = e.target.result;
        document.getElementById('image-preview').classList.remove('hidden');
        document.getElementById('drop-zone').classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

// Remove the selected image
function clearImage() {
    document.getElementById('image-input').value = '';
    document.getElementById('image-preview').classList.add('hidden');
    document.getElementById('drop-zone').classList.remove('hidden');
}

// Drag-and-drop support
const dz = document.getElementById('drop-zone');
dz.addEventListener('dragover',  (e) => { e.preventDefault(); dz.classList.add('border-amber-400'); });
dz.addEventListener('dragleave', ()  => dz.classList.remove('border-amber-400'));
dz.addEventListener('drop', (e) => {
    e.preventDefault();
    dz.classList.remove('border-amber-400');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('image-input').files = dt.files;
        previewImage(document.getElementById('image-input'));
    }
});
</script>
@endpush

@endsection