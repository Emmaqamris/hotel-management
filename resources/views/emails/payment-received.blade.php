<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Received</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="background-color:#f8fafc;padding:40px 20px;">
<tr><td align="center">

<table width="600" cellpadding="0" cellspacing="0" border="0"
       style="background:#ffffff;border-radius:12px;overflow:hidden;
              box-shadow:0 4px 24px rgba(0,0,0,0.06);max-width:600px;">

    {{-- Header --}}
    <tr>
        <td style="background:#0f172a;padding:32px 40px;text-align:center;">
            <div style="color:#fbbf24;font-size:22px;font-weight:700;">
                {{ $payment->invoice->hotel->name }}
            </div>
        </td>
    </tr>

    {{-- Green payment banner --}}
    <tr>
        <td style="background:#10b981;padding:14px 40px;text-align:center;">
            <div style="color:#ffffff;font-size:13px;font-weight:700;
                        letter-spacing:1.5px;text-transform:uppercase;">
                ✓  Payment Received
            </div>
        </td>
    </tr>

    {{-- Body --}}
    <tr>
        <td style="padding:32px 40px;">

            <p style="color:#1e293b;font-size:15px;margin:0 0 8px;">
                Dear <strong>{{ $payment->invoice->guest->full_name }}</strong>,
            </p>
            <p style="color:#475569;font-size:14px;line-height:1.7;margin:0 0 24px;">
                We confirm receipt of your payment. Your invoice is now settled.
            </p>

            {{-- Amount --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#f0fdf4;border-radius:8px;margin-bottom:24px;
                          border:1px solid #bbf7d0;">
            <tr>
                <td style="padding:24px;text-align:center;">
                    <div style="color:#166534;font-size:12px;text-transform:uppercase;
                                letter-spacing:2px;margin-bottom:8px;">
                        Amount Paid
                    </div>
                    <div style="color:#15803d;font-size:36px;font-weight:700;">
                        {{ number_format($payment->amount, 2) }}
                    </div>
                    <div style="color:#4ade80;font-size:12px;margin-top:6px;">
                        via {{ $payment->method_display }}
                        &nbsp;·&nbsp;
                        {{ $payment->paid_at->format('d M Y, H:i') }}
                    </div>
                </td>
            </tr>
            </table>

            {{-- Payment details --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="border:1px solid #e2e8f0;border-radius:8px;
                          overflow:hidden;margin-bottom:24px;">
            <tr style="background:#f8fafc;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;width:40%;">
                    Reference No.
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#1e293b;
                           font-family:monospace;font-weight:600;">
                    {{ $payment->reference_number }}
                </td>
            </tr>
            <tr style="border-top:1px solid #e2e8f0;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;">
                    Invoice No.
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#475569;
                           font-family:monospace;">
                    {{ $payment->invoice->invoice_number }}
                </td>
            </tr>
            <tr style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;">
                    Booking
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#475569;
                           font-family:monospace;">
                    {{ $payment->invoice->booking->booking_number }}
                </td>
            </tr>
            <tr style="border-top:1px solid #e2e8f0;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;">
                    Processed By
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#475569;">
                    {{ $payment->processedBy?->name ?? 'System' }}
                </td>
            </tr>
            </table>

            <p style="color:#475569;font-size:13px;line-height:1.7;margin:0;">
                Please keep this email as your payment confirmation. If you
                have any questions, contact us at
                <a href="mailto:{{ $payment->invoice->hotel->email }}"
                   style="color:#d97706;text-decoration:none;">
                    {{ $payment->invoice->hotel->email }}
                </a>.
            </p>

        </td>
    </tr>

    <tr>
        <td style="background:#f8fafc;border-top:1px solid #e2e8f0;
                   padding:20px 40px;text-align:center;">
            <p style="color:#94a3b8;font-size:12px;margin:0;">
                Thank you for staying with us.<br>
                <strong>{{ $payment->invoice->hotel->name }}</strong>
            </p>
        </td>
    </tr>

</table>
</td></tr>
</table>

</body>
</html>