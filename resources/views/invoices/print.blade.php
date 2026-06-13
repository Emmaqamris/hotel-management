<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} — {{ $invoice->hotel->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #f8fafc;
            padding: 40px 20px;
        }

        .invoice-wrap {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        .header {
            background: #0f172a;
            padding: 32px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .hotel-name  { color: #fbbf24; font-weight: 700; font-size: 20px; }
        .hotel-info  { color: #94a3b8; font-size: 12px; margin-top: 4px; }
        .inv-label   { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; }
        .inv-number  { color: #ffffff; font-weight: 700; font-size: 20px; font-family: monospace; margin-top: 4px; }

        .status-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-paid      { background: #dcfce7; color: #166534; }
        .status-issued    { background: #dbeafe; color: #1e40af; }
        .status-draft     { background: #f1f5f9; color: #475569; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .body { padding: 32px 40px; }

        .bill-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 28px;
            padding-bottom: 28px;
            border-bottom: 1px solid #e2e8f0;
        }

        .section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        .guest-name    { font-weight: 700; font-size: 15px; color: #1e293b; }
        .guest-contact { color: #64748b; margin-top: 2px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead th {
            text-align: left;
            padding: 8px 0;
            border-bottom: 2px solid #e2e8f0;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        thead th:not(:first-child) { text-align: right; }

        tbody td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        tbody td:not(:first-child) { text-align: right; }

        .item-type {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            background: #f1f5f9;
            color: #475569;
            margin-bottom: 3px;
        }

        .totals-section {
            border-top: 2px solid #e2e8f0;
            padding-top: 16px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }

        .totals-row .label   { color: #64748b; }
        .totals-row .amount  { font-weight: 600; color: #1e293b; }
        .totals-row.discount .amount { color: #dc2626; }

        .total-final {
            display: flex;
            justify-content: space-between;
            border-top: 2px solid #1e293b;
            margin-top: 8px;
            padding-top: 12px;
        }

        .total-final .label  { font-size: 16px; font-weight: 700; }
        .total-final .amount { font-size: 22px; font-weight: 700; }

        .paid-section {
            margin-top: 24px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 16px;
        }

        .paid-title  { font-weight: 700; color: #166534; margin-bottom: 4px; }
        .paid-detail { color: #166534; font-size: 12px; }

        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 40px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }

        .print-btn {
            position: fixed;
            bottom: 32px;
            right: 32px;
            background: #0f172a;
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }

        .print-btn:hover { background: #1e293b; }

        @media print {
            body { background: #fff; padding: 0; }
            .invoice-wrap { box-shadow: none; border-radius: 0; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="invoice-wrap">

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="hotel-name">{{ $invoice->hotel->name }}</div>
            <div class="hotel-info">{{ $invoice->hotel->address }}</div>
            <div class="hotel-info">
                {{ $invoice->hotel->phone }} · {{ $invoice->hotel->email }}
            </div>
        </div>
        <div style="text-align:right">
            <div class="inv-label">Invoice</div>
            <div class="inv-number">{{ $invoice->invoice_number }}</div>
            <div class="status-badge status-{{ $invoice->status }}">
                {{ $invoice->status_display }}
            </div>
        </div>
    </div>

    {{-- Body --}}
    <div class="body">

        {{-- Bill To + Booking --}}
        <div class="bill-grid">
            <div>
                <div class="section-label">Bill To</div>
                <div class="guest-name">{{ $invoice->guest->full_name }}</div>
                @if($invoice->guest->email)
                <div class="guest-contact">{{ $invoice->guest->email }}</div>
                @endif
                <div class="guest-contact">{{ $invoice->guest->phone }}</div>
                <div class="guest-contact" style="margin-top:4px; font-size:11px">
                    {{ $invoice->guest->id_type_display }}: {{ $invoice->guest->id_number }}
                </div>
            </div>
            <div>
                <div class="section-label">Booking Details</div>
                <div style="color:#64748b; margin-bottom:4px">
                    Ref:
                    <span style="font-family:monospace; font-weight:700; color:#d97706">
                        {{ $invoice->booking->booking_number }}
                    </span>
                </div>
                <div style="color:#475569">
                    Room {{ $invoice->booking->room->number }}
                    ({{ $invoice->booking->room->type_display }})
                </div>
                <div style="color:#475569">
                    {{ $invoice->booking->check_in->format('d M Y') }}
                    →
                    {{ $invoice->booking->check_out->format('d M Y') }}
                </div>
                <div style="color:#94a3b8; font-size:12px; margin-top:4px">
                    {{ $invoice->booking->nights }}
                    night{{ $invoice->booking->nights !== 1 ? 's' : '' }}
                    ·
                    {{ $invoice->booking->adults }}
                    adult{{ $invoice->booking->adults !== 1 ? 's' : '' }}
                </div>
                @if($invoice->issued_at)
                <div style="color:#94a3b8; font-size:12px; margin-top:4px">
                    Issued: {{ $invoice->issued_at->format('d M Y') }}
                </div>
                @endif
            </div>
        </div>

        {{-- Line items --}}
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align:right; width:50px">Qty</th>
                    <th style="text-align:right; width:90px">Rate</th>
                    <th style="text-align:right; width:100px">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <div class="item-type">{{ $item->type_display }}</div>
                        <div>{{ $item->description }}</div>
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td style="font-weight:600">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals-section">
            <div class="totals-row">
                <span class="label">Subtotal</span>
                <span class="amount">{{ number_format($invoice->subtotal, 2) }}</span>
            </div>

            @if((float)$invoice->discount_amount > 0)
            <div class="totals-row discount">
                <span class="label">Discount</span>
                <span class="amount">− {{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif

            <div class="totals-row">
                <span class="label">
                    Tax ({{ number_format($invoice->tax_rate, 0) }}%)
                </span>
                <span class="amount">{{ number_format($invoice->tax_amount, 2) }}</span>
            </div>

            <div class="total-final">
                <span class="label">Total Due</span>
                <span class="amount">{{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>

        {{-- Payment confirmation --}}
        @if($invoice->isPaid() && $invoice->payment)
        <div class="paid-section">
            <div class="paid-title">✓ Payment Received</div>
            <div class="paid-detail">
                {{ number_format($invoice->payment->amount, 2) }}
                via {{ $invoice->payment->method_display }}
                · {{ $invoice->payment->paid_at->format('d M Y, H:i') }}
            </div>
            <div class="paid-detail">
                Reference: {{ $invoice->payment->reference_number }}
            </div>
        </div>
        @endif

    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>
            Thank you for choosing {{ $invoice->hotel->name }}.
            We look forward to welcoming you again.
        </p>
        <p style="margin-top:4px">
            {{ $invoice->hotel->address }}
            · {{ $invoice->hotel->phone }}
            · {{ $invoice->hotel->email }}
        </p>
    </div>

</div>

{{-- Print button (hidden when printing) --}}
<button class="print-btn" onclick="window.print()">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002
                 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0
                 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
    </svg>
    Print / Save as PDF
</button>

</body>
</html>