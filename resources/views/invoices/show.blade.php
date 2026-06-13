@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page-title', 'Invoice ' . $invoice->invoice_number)

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
       class="inline-flex items-center gap-1.5 bg-white border border-slate-200
              hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2
              rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0
                     002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2
                     2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print Invoice
    </a>

    @if($invoice->canBePaid())
    <a href="{{ route('payments.create', $invoice) }}"
       class="inline-flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600
              text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3
                     3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
        Process Payment
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left: Invoice document ──────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Invoice header --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

            {{-- Top band --}}
            <div class="bg-slate-900 px-6 py-5 flex items-start justify-between">
                <div>
                    <p class="text-amber-400 font-bold text-lg leading-tight">
                        {{ $invoice->hotel->name }}
                    </p>
                    <p class="text-slate-400 text-xs mt-1">
                        {{ $invoice->hotel->address }}
                    </p>
                    <p class="text-slate-400 text-xs">
                        {{ $invoice->hotel->phone }} · {{ $invoice->hotel->email }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400 uppercase tracking-widest">Invoice</p>
                    <p class="text-white font-bold font-mono text-xl mt-0.5">
                        {{ $invoice->invoice_number }}
                    </p>
                    <span class="inline-flex mt-2 px-2 py-0.5 rounded-full text-xs
                                 font-semibold
                                 bg-{{ $invoice->status_color }}-500
                                 text-white">
                        {{ $invoice->status_display }}
                    </span>
                </div>
            </div>

            <div class="p-6">

                {{-- Bill To + Booking details --}}
                <div class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase
                                  tracking-wide mb-2">
                            Bill To
                        </p>
                        <p class="text-sm font-bold text-slate-800">
                            {{ $invoice->guest->full_name }}
                        </p>
                        @if($invoice->guest->email)
                        <p class="text-sm text-slate-500 mt-0.5">
                            {{ $invoice->guest->email }}
                        </p>
                        @endif
                        <p class="text-sm text-slate-500">{{ $invoice->guest->phone }}</p>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ $invoice->guest->id_type_display }}:
                            {{ $invoice->guest->id_number }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase
                                  tracking-wide mb-2">
                            Booking Details
                        </p>
                        <p class="text-sm text-slate-700">
                            <span class="text-slate-400">Ref: </span>
                            <a href="{{ route('bookings.show', $invoice->booking) }}"
                               class="font-mono font-semibold text-amber-600 hover:underline">
                                {{ $invoice->booking->booking_number }}
                            </a>
                        </p>
                        <p class="text-sm text-slate-600 mt-0.5">
                            Room {{ $invoice->booking->room->number }}
                            ({{ $invoice->booking->room->type_display }})
                        </p>
                        <p class="text-sm text-slate-600">
                            {{ $invoice->booking->check_in->format('d M Y') }}
                            →
                            {{ $invoice->booking->check_out->format('d M Y') }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $invoice->booking->nights }}
                            night{{ $invoice->booking->nights !== 1 ? 's' : '' }}
                            ·
                            {{ $invoice->booking->adults }}
                            adult{{ $invoice->booking->adults !== 1 ? 's' : '' }}
                        </p>
                        @if($invoice->issued_at)
                        <p class="text-xs text-slate-400 mt-1">
                            Issued: {{ $invoice->issued_at->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Line items --}}
                <table class="w-full text-sm mb-6">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left pb-2 text-xs font-semibold text-slate-400
                                       uppercase tracking-wide">
                                Description
                            </th>
                            <th class="text-right pb-2 w-16 text-xs font-semibold
                                       text-slate-400 uppercase tracking-wide">
                                Qty
                            </th>
                            <th class="text-right pb-2 w-24 text-xs font-semibold
                                       text-slate-400 uppercase tracking-wide">
                                Rate
                            </th>
                            <th class="text-right pb-2 w-28 text-xs font-semibold
                                       text-slate-400 uppercase tracking-wide">
                                Amount
                            </th>
                            @if($invoice->canBeEdited())
                            <th class="w-8 pb-2"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex text-xs font-medium px-1.5
                                                 py-0.5 rounded
                                                 bg-{{ $item->type_color }}-100
                                                 text-{{ $item->type_color }}-700">
                                        {{ $item->type_display }}
                                    </span>
                                    <span class="text-slate-700">
                                        {{ $item->description }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 text-right text-slate-500">
                                {{ $item->quantity }}
                            </td>
                            <td class="py-3 text-right text-slate-500">
                                {{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="py-3 text-right font-semibold text-slate-800">
                                {{ number_format($item->total, 2) }}
                            </td>
                            @if($invoice->canBeEdited())
                            <td class="py-3 text-right">
                                @if($item->type !== 'room_charge')
                                <form method="POST"
                                      action="{{ route('invoices.charges.destroy', [$invoice, $item]) }}"
                                      onsubmit="return confirm('Remove this charge?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-red-400 hover:text-red-600
                                                   transition-colors">
                                        <svg class="w-4 h-4" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Totals --}}
                <div class="border-t border-slate-200 pt-4 space-y-1.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-medium text-slate-700">
                            {{ number_format($invoice->subtotal, 2) }}
                        </span>
                    </div>
                    @if((float)$invoice->discount_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Discount</span>
                        <span class="font-medium text-red-600">
                            − {{ number_format($invoice->discount_amount, 2) }}
                        </span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">
                            Tax ({{ number_format($invoice->tax_rate, 0) }}%)
                        </span>
                        <span class="font-medium text-slate-700">
                            {{ number_format($invoice->tax_amount, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-3 mt-2">
                        <span class="text-base font-bold text-slate-800">Total Due</span>
                        <span class="text-xl font-bold text-slate-800">
                            {{ number_format($invoice->total, 2) }}
                        </span>
                    </div>
                </div>

                {{-- Paid stamp --}}
                @if($invoice->isPaid() && $invoice->payment)
                <div class="mt-6 bg-emerald-50 border border-emerald-200 rounded-xl
                            p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center
                                justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="currentColor"
                             viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414
                                     0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1
                                     1 0 011.414 0z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-800">Payment Received</p>
                        <p class="text-sm text-emerald-700">
                            {{ number_format($invoice->payment->amount, 2) }}
                            via {{ $invoice->payment->method_display }}
                            on {{ $invoice->payment->paid_at->format('d M Y, H:i') }}
                        </p>
                        <p class="text-xs text-emerald-600 mt-0.5">
                            Ref: {{ $invoice->payment->reference_number }}
                            @if($invoice->payment->processedBy)
                            · By {{ $invoice->payment->processedBy->name }}
                            @endif
                        </p>
                    </div>
                    <div class="ml-auto">
                        <a href="{{ route('payments.receipt', $invoice->payment) }}"
                           target="_blank"
                           class="text-xs text-emerald-600 hover:text-emerald-700
                                  font-medium">
                            View receipt →
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Add extra charges (only for unpaid, not cancelled) --}}
        @if($invoice->canBeEdited())
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">
                Add Extra Charge
            </h3>
            <form method="POST"
                  action="{{ route('invoices.charges.store', $invoice) }}"
                  class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
                @csrf

                <div class="col-span-2 sm:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="description"
                           placeholder="e.g. Room Service — Dinner"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2
                                  text-sm focus:outline-none focus:ring-2
                                  focus:ring-amber-400"
                           required>
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2
                                   text-sm focus:outline-none focus:ring-2
                                   focus:ring-amber-400 bg-white">
                        @foreach([
                            'service'  => 'Service',
                            'food'     => 'Food & Bev',
                            'minibar'  => 'Minibar',
                            'laundry'  => 'Laundry',
                            'other'    => 'Other',
                        ] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">Qty</label>
                    <input type="number" name="quantity" value="1" min="1" max="100"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2
                                  text-sm focus:outline-none focus:ring-2
                                  focus:ring-amber-400">
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        Amount <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="unit_price" step="0.01" min="0.01"
                           placeholder="0.00"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2
                                  text-sm focus:outline-none focus:ring-2
                                  focus:ring-amber-400"
                           required>
                </div>

                <div class="col-span-2 sm:col-span-1 flex items-end">
                    <button type="submit"
                            class="w-full bg-slate-800 hover:bg-slate-700 text-white
                                   text-sm font-semibold py-2 rounded-lg transition-colors">
                        Add Charge
                    </button>
                </div>

            </form>
        </div>

        {{-- Apply discount --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Apply Discount</h3>
            <form method="POST"
                  action="{{ route('invoices.discount', $invoice) }}"
                  class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs text-slate-500 mb-1">
                        Discount Amount
                        @if((float)$invoice->discount_amount > 0)
                        <span class="ml-2 text-amber-600 font-medium">
                            Current: {{ number_format($invoice->discount_amount, 2) }}
                        </span>
                        @endif
                    </label>
                    <input type="number"
                           name="discount_amount"
                           value="{{ old('discount_amount', number_format($invoice->discount_amount, 2, '.', '')) }}"
                           step="0.01" min="0"
                           placeholder="0.00"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2
                                  text-sm focus:outline-none focus:ring-2
                                  focus:ring-amber-400">
                </div>
                <button type="submit"
                        class="bg-slate-800 hover:bg-slate-700 text-white text-sm
                               font-semibold px-5 py-2 rounded-lg transition-colors">
                    Apply
                </button>
            </form>
        </div>
        @endif

    </div>

    {{-- ── Right sidebar ───────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Payment status card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-4">
                Payment Status
            </p>

            @if($invoice->isPaid())
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center
                            justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414
                                 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1
                                 1 0 011.414 0z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
                <span class="text-sm font-bold text-emerald-700">Paid in full</span>
            </div>
            <p class="text-xs text-slate-400">
                {{ $invoice->payment->method_display }}
                · {{ $invoice->payment->paid_at->format('d M Y') }}
            </p>

            @elseif($invoice->isCancelled())
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                         bg-red-100 text-red-700">
                Cancelled
            </span>

            @else
            <div class="text-center py-2">
                <p class="text-2xl font-bold text-slate-800">
                    {{ number_format($invoice->total, 2) }}
                </p>
                <p class="text-xs text-slate-400 mt-0.5">outstanding balance</p>
            </div>
            <a href="{{ route('payments.create', $invoice) }}"
               class="mt-4 block w-full text-center bg-emerald-500 hover:bg-emerald-600
                      text-white text-sm font-bold py-3 rounded-xl transition-colors">
                Process Payment
            </a>
            @endif
        </div>

        {{-- Invoice meta --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">
                Invoice Info
            </p>
            <dl class="space-y-2">
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">Number</dt>
                    <dd class="font-mono font-medium text-slate-700">
                        {{ $invoice->invoice_number }}
                    </dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">Created</dt>
                    <dd class="text-slate-600">
                        {{ $invoice->created_at->format('d M Y') }}
                    </dd>
                </div>
                @if($invoice->issued_at)
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">Issued</dt>
                    <dd class="text-slate-600">
                        {{ $invoice->issued_at->format('d M Y') }}
                    </dd>
                </div>
                @endif
                @if($invoice->due_at && !$invoice->isPaid())
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">Due By</dt>
                    <dd class="font-medium
                        {{ $invoice->due_at->isPast()
                            ? 'text-red-600'
                            : 'text-slate-600' }}">
                        {{ $invoice->due_at->format('d M Y') }}
                    </dd>
                </div>
                @endif
                <div class="flex justify-between text-sm border-t border-slate-100 pt-2">
                    <dt class="text-slate-400">Tax Rate</dt>
                    <dd class="text-slate-600">
                        {{ number_format($invoice->tax_rate, 0) }}%
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Quick links --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">
                Quick Links
            </p>
            <div class="space-y-2">
                <a href="{{ route('bookings.show', $invoice->booking) }}"
                   class="flex items-center gap-2 text-sm text-slate-600
                          hover:text-amber-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0
                                 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    View Booking
                </a>
                <a href="{{ route('guests.show', $invoice->guest) }}"
                   class="flex items-center gap-2 text-sm text-slate-600
                          hover:text-amber-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7
                                 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Guest Profile
                </a>
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
                   class="flex items-center gap-2 text-sm text-slate-600
                          hover:text-amber-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2
                                 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2
                                 -2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2
                                 -2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Invoice
                </a>
                @if($invoice->payment)
                <a href="{{ route('payments.receipt', $invoice->payment) }}"
                   target="_blank"
                   class="flex items-center gap-2 text-sm text-slate-600
                          hover:text-amber-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                 a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2
                                 2 0 01-2 2z"/>
                    </svg>
                    Print Receipt
                </a>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection