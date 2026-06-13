@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('content')

{{-- ── Summary bar ─────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $statusCards = [
            ['status' => 'draft',     'label' => 'Draft',     'color' => 'slate'],
            ['status' => 'issued',    'label' => 'Issued',    'color' => 'blue'],
            ['status' => 'paid',      'label' => 'Paid',      'color' => 'emerald'],
            ['status' => 'cancelled', 'label' => 'Cancelled', 'color' => 'red'],
        ];
    @endphp

    @foreach($statusCards as $card)
    @php $row = $summary[$card['status']] ?? null; @endphp
    <a href="{{ route('invoices.index', ['status' => $card['status']]) }}"
       class="bg-white rounded-xl border p-4 transition-colors
              {{ $status === $card['status']
                    ? 'border-'.$card['color'].'-400 ring-1 ring-'.$card['color'].'-400'
                    : 'border-slate-200 hover:border-'.$card['color'].'-300' }}">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
            <div class="w-2 h-2 rounded-full bg-{{ $card['color'] }}-400"></div>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $row?->count ?? 0 }}</p>
        @if($row && $row->total_amount > 0)
        <p class="text-xs text-slate-400 mt-0.5">
            {{ number_format($row->total_amount, 2) }}
        </p>
        @endif
    </a>
    @endforeach
</div>

{{-- ── Filters ──────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Invoice number or guest name…"
               class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-amber-400 flex-1 min-w-56">

        <select name="status" onchange="this.form.submit()"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
            <option value="">All statuses</option>
            @foreach(['draft','issued','paid','cancelled'] as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>

        <button type="submit"
                class="bg-slate-800 hover:bg-slate-700 text-white text-sm
                       font-medium px-4 py-2 rounded-lg transition-colors">
            Search
        </button>

        @if($search || $status)
        <a href="{{ route('invoices.index') }}"
           class="text-sm text-slate-400 hover:text-slate-600">Clear</a>
        @endif
    </form>
</div>

{{-- ── Invoice table ───────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50">
            <tr>
                @foreach(['Invoice','Guest','Booking','Room','Charges','Status','Date',''] as $h)
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500
                           uppercase tracking-wide">
                    {{ $h }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @forelse($invoices as $invoice)
        <tr class="hover:bg-slate-50 transition-colors">

            <td class="px-5 py-3">
                <a href="{{ route('invoices.show', $invoice) }}"
                   class="text-sm font-mono font-semibold text-amber-600 hover:text-amber-700">
                    {{ $invoice->invoice_number }}
                </a>
            </td>

            <td class="px-5 py-3">
                <p class="text-sm font-medium text-slate-800">
                    {{ $invoice->guest->full_name }}
                </p>
                <p class="text-xs text-slate-400">{{ $invoice->guest->phone }}</p>
            </td>

            <td class="px-5 py-3">
                <a href="{{ route('bookings.show', $invoice->booking) }}"
                   class="text-xs font-mono text-slate-500 hover:text-amber-600">
                    {{ $invoice->booking->booking_number }}
                </a>
            </td>

            <td class="px-5 py-3 text-sm text-slate-600">
                Room {{ $invoice->booking->room->number }}
                <span class="text-xs text-slate-400">
                    ({{ $invoice->booking->nights }}n)
                </span>
            </td>

            <td class="px-5 py-3">
                <p class="text-sm font-bold text-slate-800">
                    {{ number_format($invoice->total, 2) }}
                </p>
                @if((float)$invoice->extra_charges > 0)
                <p class="text-xs text-slate-400">
                    + {{ number_format($invoice->extra_charges, 2) }} extras
                </p>
                @endif
            </td>

            <td class="px-5 py-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full
                             text-xs font-semibold
                             bg-{{ $invoice->status_color }}-100
                             text-{{ $invoice->status_color }}-700">
                    {{ $invoice->status_display }}
                </span>
            </td>

            <td class="px-5 py-3 text-xs text-slate-400">
                {{ $invoice->created_at->format('d M Y') }}
            </td>

            <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('invoices.show', $invoice) }}"
                       class="text-xs text-slate-400 hover:text-amber-600 font-medium">
                        View
                    </a>
                    @if($invoice->canBePaid() && !$invoice->isPaid())
                    <span class="text-slate-200">|</span>
                    <a href="{{ route('payments.create', $invoice) }}"
                       class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                        Pay
                    </a>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="px-5 py-16 text-center">
                <p class="text-sm text-slate-400">No invoices found.</p>
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>

    @if($invoices->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">
        {{ $invoices->links() }}
    </div>
    @endif
</div>

@endsection