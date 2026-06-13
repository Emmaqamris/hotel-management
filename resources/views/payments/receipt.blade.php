<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Receipt {{ $payment->reference_number }}
        — {{ $payment->invoice->hotel->name }}
    </title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .receipt {
            background: #ffffff;
            width: 380px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        }

        .receipt-top {
            background: #0f172a;
            padding: 28px 28px 24px;
            text-align: center;
        }

        .hotel-name  { color: #fbbf24; font-weight: 700; font-size: 16px; }
        .hotel-sub   { color: #64748b; font-size: 11px; margin-top: 2px; }
        .receipt-title {
            margin-top: 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #94a3b8;
        }
        .paid-amount {
            margin-top: 8px;
            font-size: 36px;
            font-weight: 700;
            color: #ffffff;
        }

        .paid-badge {
            display: inline-block;
            margin-top: 12px;
            background: #dcfce7;
            color: #166534;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 4px 14px;
            border-radius: 20px;
        }

        .receipt-body { padding: 24px 28px; }

        .divider {
            border: none;
            border-top: 1px dashed #e2e8f0;
            margin: 16px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .row .label  { color: #94a3b8; }
        .row .value  { font-weight: 600; color: #1e293b; text-align: right; max-width: 60%; }

        .guest-name { font-size: 15px; font-weight: 700; text-align: center; color: #1e293b; margin-bottom: 4px; }
        .guest-sub  { font-size: 12px; color: #64748b; text-align: center; }

        .items-section { margin: 12px 0; }
        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #64748b;
            padding: 3px 0;
        }

        .thank-you {
            background: #f0fdf4;
            border-top: 1px solid #bbf7d0;
            padding: 16px 28px;
            text-align: center;
        }

        .thank-title  { font-weight: 700; color: #166534; margin-bottom: 4px; }
        .thank-sub    { font-size: 11px; color: #4ade80; }

        .print-btn {
            position: fixed;
            bottom: 32px;
            right: 32px;
            background: #0f172a;
            color: #ffffff;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        @media print {
            body { background: #fff; padding: 0; justify-content: flex-start; }
            .receipt { box-shadow: none; border-radius: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="receipt">

    {{-- Top --}}
    <div class="receipt-top">
        <div class="hotel-name">{{ $payment->invoice->hotel->name }}</div>
        <div class="hotel-sub">{{ $payment->invoice->hotel->address }}</div>
        <div class="receipt-title">Payment Receipt</div>
        <div class="paid-amount">
            {{ number_format($payment->amount, 2) }}
        </div>
        <div class="paid-badge">Paid</div>
    </div>

    {{-- Body --}}
    <div class="receipt-body">

        {{-- Guest --}}
        <div class="guest-name">{{ $payment->invoice->guest->full_name }}</div>
        <div class="guest-sub">{{ $payment->invoice->guest->phone }}</div>

        <hr class="divider">

        {{-- Payment details --}}
        <div class="row">
            <span class="label">Receipt No.</span>
            <span class="value" style="font-family:monospace">
                {{ $payment->reference_number }}
            </span>
        </div>
        <div class="row">
            <span class="label">Invoice</span>
            <span class="value" style="font-family:monospace">
                {{ $payment->invoice->invoice_number }}
            </span>
        </div>
        <div class="row">
            <span class="label">Payment Method</span>
            <span class="value">{{ $payment->method_display }}</span>
        </div>
        <div class="row">
            <span class="label">Date & Time</span>
            <span class="value">{{ $payment->paid_at->format('d M Y, H:i') }}</span>
        </div>
        @if($payment->processedBy)
        <div class="row">
            <span class="label">Processed By</span>
            <span class="value">{{ $payment->processedBy->name }}</span>
        </div>
        @endif

        <hr class="divider">

        {{-- Booking summary --}}
        <div class="row">
            <span class="label">Booking Ref.</span>
            <span class="value" style="font-family:monospace">
                {{ $payment->invoice->booking->booking_number }}
            </span>
        </div>
        <div class="row">
            <span class="label">Room</span>
            <span class="value">
                {{ $payment->invoice->booking->room->number }}
                ({{ $payment->invoice->booking->room->type_display }})
            </span>
        </div>
        <div class="row">
            <span class="label">Stay</span>
            <span class="value">
                {{ $payment->invoice->booking->check_in->format('d M') }}
                –
                {{ $payment->invoice->booking->check_out->format('d M Y') }}
            </span>
        </div>

        <hr class="divider">

        {{-- Charge breakdown --}}
        <div class="items-section">
            @foreach($payment->invoice->items as $item)
            <div class="item-row">
                <span>{{ $item->description }}</span>
                <span>{{ number_format($item->total, 2) }}</span>
            </div>
            @endforeach
        </div>

        <hr class="divider">

        {{-- Final total --}}
        <div class="row">
            <span class="label">Subtotal</span>
            <span class="value">
                {{ number_format($payment->invoice->subtotal, 2) }}
            </span>
        </div>
        @if((float)$payment->invoice->discount_amount > 0)
        <div class="row">
            <span class="label">Discount</span>
            <span class="value" style="color:#dc2626">
                − {{ number_format($payment->invoice->discount_amount, 2) }}
            </span>
        </div>
        @endif
        <div class="row">
            <span class="label">
                Tax ({{ number_format($payment->invoice->tax_rate, 0) }}%)
            </span>
            <span class="value">
                {{ number_format($payment->invoice->tax_amount, 2) }}
            </span>
        </div>
        <div class="row" style="margin-top:8px; padding-top:8px;
                                border-top:2px solid #1e293b">
            <span style="font-weight:700; font-size:14px">Amount Paid</span>
            <span style="font-weight:700; font-size:16px">
                {{ number_format($payment->amount, 2) }}
            </span>
        </div>
    </div>

    {{-- Thank you footer --}}
    <div class="thank-you">
        <div class="thank-title">Thank you for your payment!</div>
        <div class="thank-sub">
            {{ $payment->invoice->hotel->phone }}
            · {{ $payment->invoice->hotel->email }}
        </div>
    </div>

</div>

<button class="print-btn" onclick="window.print()">Print Receipt</button>

</body>
</html>