@extends('layouts.app')

@section('title', 'Bookings Report')
@section('page-title', 'Bookings Report')

@section('content')

{{-- Date filter --}}
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-500 font-medium">From</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                   class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-500 font-medium">To</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                   class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>
        <button type="submit"
                class="bg-slate-800 hover:bg-slate-700 text-white text-sm
                       font-medium px-4 py-2 rounded-lg transition-colors">
            Apply
        </button>
    </form>
</div>

{{-- Key stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $bCards = [
            ['label' => 'Total Bookings',    'value' => $data['total'],                           'color' => 'slate'],
            ['label' => 'Total Value',        'value' => number_format($data['total_value'], 2),   'color' => 'emerald'],
            ['label' => 'Cancellation Rate',  'value' => $data['cancellation_rate'].'%',           'color' => $data['cancellation_rate'] > 20 ? 'red' : 'slate'],
            ['label' => 'Avg Stay Length',    'value' => $data['avg_stay_length'].' nights',       'color' => 'blue'],
        ];
    @endphp
    @foreach($bCards as $card)
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs text-slate-400 font-medium mb-1">{{ $card['label'] }}</p>
        <p class="text-2xl font-bold text-{{ $card['color'] }}-700">{{ $card['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Status breakdown --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    @php
        $statusBreakdown = [
            ['label' => 'Confirmed',   'value' => $data['confirmed'],   'color' => 'blue'],
            ['label' => 'Checked In',  'value' => $data['checked_in'],  'color' => 'green'],
            ['label' => 'Checked Out', 'value' => $data['checked_out'], 'color' => 'slate'],
            ['label' => 'Cancelled',   'value' => $data['cancelled'],   'color' => 'red'],
            ['label' => 'No Show',     'value' => $data['no_show'],     'color' => 'orange'],
        ];
    @endphp
    @foreach($statusBreakdown as $item)
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex
                items-center gap-3">
        <div class="w-3 h-3 rounded-full bg-{{ $item['color'] }}-500 flex-shrink-0">
        </div>
        <div>
            <p class="text-xs text-slate-400">{{ $item['label'] }}</p>
            <p class="text-xl font-bold text-slate-800">{{ $item['value'] }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- By source --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">By Booking Source</h2>
        @php
            $sourceLabels = ['walk_in'=>'Walk-in','phone'=>'Phone','online'=>'Online','ota'=>'OTA'];
        @endphp
        @forelse($data['by_source'] as $source)
        <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
            <span class="text-sm font-medium text-slate-700">
                {{ $sourceLabels[$source->source] ?? ucfirst($source->source) }}
            </span>
            <div class="text-right">
                <span class="text-sm font-bold text-slate-800">{{ $source->count }}</span>
                <span class="text-xs text-slate-400 ml-1">bookings</span>
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-400 text-center py-4">No data.</p>
        @endforelse
    </div>

    {{-- By room type --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">By Room Type</h2>
        @php
            $typeLabels = ['standard'=>'Standard','deluxe'=>'Deluxe','family_suite'=>'Family Suite','business_suite'=>'Business Suite'];
        @endphp
        @forelse($data['by_room_type'] as $type)
        <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
            <span class="text-sm font-medium text-slate-700">
                {{ $typeLabels[$type->type] ?? ucfirst($type->type) }}
            </span>
            <div class="text-right">
                <span class="text-sm font-bold text-slate-800">{{ $type->count }}</span>
                <span class="text-xs text-slate-400 ml-1">
                    · {{ number_format($type->total, 2) }}
                </span>
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-400 text-center py-4">No data.</p>
        @endforelse
    </div>

</div>

@endsection