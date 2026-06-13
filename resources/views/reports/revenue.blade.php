@extends('layouts.app')

@section('title', 'Revenue Report')
@section('page-title', 'Revenue Report')

@section('header-actions')
<a href="{{ route('reports.revenue.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
   class="inline-flex items-center gap-1.5 bg-white border border-slate-200
          hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2
          rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                 a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    Export CSV
</a>
@endsection

@section('content')

{{-- ── Date filter ─────────────────────────────────────────────── --}}
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
        {{-- Shortcut buttons --}}
        @foreach([
            ['label' => 'This Week',  'from' => now()->startOfWeek()->format('Y-m-d'),  'to' => now()->endOfWeek()->format('Y-m-d')],
            ['label' => 'This Month', 'from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->endOfMonth()->format('Y-m-d')],
            ['label' => 'Last Month', 'from' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'to' => now()->subMonth()->endOfMonth()->format('Y-m-d')],
            ['label' => 'This Year',  'from' => now()->startOfYear()->format('Y-m-d'),  'to' => now()->endOfYear()->format('Y-m-d')],
        ] as $shortcut)
        <a href="{{ route('reports.revenue', ['from' => $shortcut['from'], 'to' => $shortcut['to']]) }}"
           class="text-xs font-medium px-3 py-1.5 rounded-lg border transition-colors
                  {{ $from->format('Y-m-d') === $shortcut['from'] && $to->format('Y-m-d') === $shortcut['to']
                      ? 'bg-slate-800 text-white border-slate-800'
                      : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            {{ $shortcut['label'] }}
        </a>
        @endforeach
        <button type="submit"
                class="bg-slate-800 hover:bg-slate-700 text-white text-sm
                       font-medium px-4 py-2 rounded-lg transition-colors">
            Apply
        </button>
    </form>
</div>

{{-- ── Key metrics ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $metricCards = [
            ['label' => 'Total Revenue',   'value' => number_format($data['total_revenue'], 2),    'sub' => $data['total_payments'].' payments'],
            ['label' => 'Avg Daily Revenue','value' => number_format($data['avg_daily_revenue'], 2), 'sub' => 'per day'],
            ['label' => 'ADR',             'value' => number_format($data['adr'], 2),              'sub' => 'avg daily rate'],
            ['label' => 'RevPAR',          'value' => number_format($data['revpar'], 2),           'sub' => 'revenue per avail. room'],
        ];
    @endphp
    @foreach($metricCards as $card)
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs text-slate-400 font-medium mb-1">{{ $card['label'] }}</p>
        <p class="text-2xl font-bold text-slate-800">{{ $card['value'] }}</p>
        <p class="text-xs text-slate-400 mt-0.5">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Revenue chart (React) --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Daily Revenue</h2>
        <div id="report-revenue-chart" class="h-64"></div>
    </div>

    {{-- Payment method breakdown --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">By Payment Method</h2>
        @forelse($data['by_method'] as $method)
        <div class="mb-4">
            <div class="flex justify-between text-sm mb-1">
                <span class="font-medium text-slate-700">{{ $method['label'] }}</span>
                <span class="text-slate-500">{{ number_format($method['total'], 2) }}</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2">
                <div class="bg-amber-400 h-2 rounded-full transition-all"
                     style="width: {{ $method['percent'] }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ $method['percent'] }}% · {{ $method['count'] }}
                payment{{ $method['count'] !== 1 ? 's' : '' }}
            </p>
        </div>
        @empty
        <p class="text-sm text-slate-400 text-center py-8">No payments in this period.</p>
        @endforelse
    </div>
</div>

{{-- ── Daily breakdown table ───────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-sm font-semibold text-slate-700">Daily Breakdown</h2>
    </div>
    <div class="max-h-96 overflow-y-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50 sticky top-0">
                <tr>
                    @foreach(['Date','Revenue','Transactions'] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500
                               uppercase tracking-wide">
                        {{ $h }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach(array_reverse($data['daily_data']) as $row)
            <tr class="{{ $row['revenue'] > 0 ? 'hover:bg-slate-50' : '' }}">
                <td class="px-5 py-2.5 text-sm text-slate-700">
                    {{ \Carbon\Carbon::parse($row['date'])->format('D, d M Y') }}
                </td>
                <td class="px-5 py-2.5 text-sm font-semibold
                    {{ $row['revenue'] > 0 ? 'text-slate-800' : 'text-slate-300' }}">
                    {{ $row['revenue'] > 0 ? number_format($row['revenue'], 2) : '—' }}
                </td>
                <td class="px-5 py-2.5 text-sm text-slate-500">
                    {{ $row['count'] > 0 ? $row['count'] : '—' }}
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot class="bg-slate-50 border-t-2 border-slate-200 sticky bottom-0">
                <tr>
                    <td class="px-5 py-3 text-sm font-bold text-slate-800">Total</td>
                    <td class="px-5 py-3 text-sm font-bold text-slate-800">
                        {{ number_format($data['total_revenue'], 2) }}
                    </td>
                    <td class="px-5 py-3 text-sm font-bold text-slate-800">
                        {{ $data['total_payments'] }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('scripts')
<script>
window.HMS_REPORT_REVENUE = @json($data['daily_data']);
</script>
@endpush

@endsection