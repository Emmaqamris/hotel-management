@extends('layouts.app')

@section('title', 'Process Payment')
@section('page-title', 'Process Payment')

@section('content')
<div class="max-w-3xl">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ── Left: Invoice summary ──────────────────────────── --}}
    <div class="space-y-4">

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-4">
                Invoice Summary
            </p>

            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-xs text-slate-400">Invoice</p>
                    <p class="font-mono font-bold text-slate-800">
                        {{ $invoice->invoice_number }}
                    </p>
                </div>
                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full
                             font-semibold">
                    {{ $invoice->status_display }}
                </span>
            </div>

            {{-- Guest --}}
            <div class="flex items-center gap-3 mb-4 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center
                            justify-center text-sm font-bold text-blue-600 flex-shrink-0">
                    {{ strtoupper(substr($invoice->guest->first_name, 0, 1)) }}{{ strtoupper(substr($invoice->guest->last_name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $invoice->guest->full_name }}
                    </p>
                    <p class="text-xs text-slate-400">{{ $invoice->guest->phone }}</p>
                </div>
            </div>

            {{-- Booking --}}
            <div class="space-y-1.5 text-sm mb-4 pb-4 border-b border-slate-100">
                <div class="flex justify-between">
                    <span class="text-slate-400">Room</span>
                    <span class="font-medium text-slate-700">
                        {{ $invoice->booking->room->number }}
                        ({{ $invoice->booking->room->type_display }})
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Nights</span>
                    <span class="font-medium text-slate-700">
                        {{ $invoice->booking->nights }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Check-in</span>
                    <span class="font-medium text-slate-700">
                        {{ $invoice->booking->check_in->format('d M Y') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Check-out</span>
                    <span class="font-medium text-slate-700">
                        {{ $invoice->booking->check_out->format('d M Y') }}
                    </span>
                </div>
            </div>

            {{-- Line items --}}
            <div class="space-y-1 mb-4">
                @foreach($invoice->items as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600 truncate pr-2">
                        {{ $item->description }}
                    </span>
                    <span class="text-slate-700 font-medium flex-shrink-0">
                        {{ number_format($item->total, 2) }}
                    </span>
                </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="border-t border-slate-200 pt-3 space-y-1">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Subtotal</span>
                    <span>{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if((float)$invoice->discount_amount > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">Discount</span>
                    <span class="text-red-600">
                        − {{ number_format($invoice->discount_amount, 2) }}
                    </span>
                </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400">
                        Tax ({{ number_format($invoice->tax_rate, 0) }}%)
                    </span>
                    <span>{{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-200 mt-2">
                    <span class="text-base font-bold text-slate-800">Total Due</span>
                    <span class="text-xl font-bold text-slate-800">
                        {{ number_format($invoice->total, 2) }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Right: Payment form ─────────────────────────────── --}}
    <div>
        <form method="POST"
              action="{{ route('payments.store', $invoice) }}"
              class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            @csrf

            {{-- Payment method --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-3">
                    Payment Method <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 gap-2">
                    @php
                        $methods = [
                            ['value' => 'cash',          'label' => 'Cash',          'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                            ['value' => 'credit_card',   'label' => 'Credit Card',   'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                            ['value' => 'debit_card',    'label' => 'Debit Card',    'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                            ['value' => 'bank_transfer', 'label' => 'Bank Transfer', 'icon' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
                            ['value' => 'check',         'label' => 'Check',         'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ];
                    @endphp

                    @foreach($methods as $method)
                    <label class="flex items-center gap-3 p-3 border-2 rounded-xl
                                  cursor-pointer transition-colors border-slate-200
                                  hover:border-amber-400 hover:bg-amber-50
                                  has-[:checked]:border-amber-500
                                  has-[:checked]:bg-amber-50">
                        <input type="radio"
                               name="method"
                               value="{{ $method['value'] }}"
                               {{ old('method', 'cash') === $method['value'] ? 'checked' : '' }}
                               class="text-amber-400 focus:ring-amber-400">
                        <svg class="w-5 h-5 text-slate-500 flex-shrink-0" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="{{ $method['icon'] }}"/>
                        </svg>
                        <span class="text-sm font-medium text-slate-700">
                            {{ $method['label'] }}
                        </span>
                    </label>
                    @endforeach
                </div>
                @error('method')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Amount --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number"
                           name="amount"
                           value="{{ old('amount', number_format($invoice->total, 2, '.', '')) }}"
                           step="0.01"
                           min="0.01"
                           class="w-full border {{ $errors->has('amount') ? 'border-red-400' : 'border-slate-200' }}
                                  rounded-xl px-4 py-3 text-lg font-bold text-slate-800
                                  focus:outline-none focus:ring-2 focus:ring-amber-400"
                           required>
                </div>
                @error('amount')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reference number --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Reference Number
                    <span class="text-slate-400 font-normal text-xs ml-1">
                        (optional — auto-generated if blank)
                    </span>
                </label>
                <input type="text"
                       name="reference_number"
                       value="{{ old('reference_number') }}"
                       placeholder="Card terminal ref, bank slip no…"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2
                              text-sm focus:outline-none focus:ring-2
                              focus:ring-amber-400">
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Notes
                </label>
                <textarea name="notes"
                          rows="2"
                          placeholder="Any payment notes…"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2
                                 text-sm focus:outline-none focus:ring-2
                                 focus:ring-amber-400 resize-none">{{ old('notes') }}</textarea>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white
                           font-bold py-3.5 rounded-xl text-base transition-colors
                           shadow-sm shadow-emerald-200">
                Confirm Payment
            </button>

            <a href="{{ route('invoices.show', $invoice) }}"
               class="block text-center text-sm text-slate-400 hover:text-slate-600">
                Back to Invoice
            </a>

        </form>
    </div>

</div>
</div>
@endsection