<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
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
            <div style="color:#fbbf24;font-size:22px;font-weight:700;
                        letter-spacing:-0.5px;">
                {{ $booking->hotel->name }}
            </div>
            <div style="color:#64748b;font-size:12px;margin-top:4px;">
                {{ $booking->hotel->address }}
            </div>
        </td>
    </tr>

    {{-- Green confirmed banner --}}
    <tr>
        <td style="background:#10b981;padding:14px 40px;text-align:center;">
            <div style="color:#ffffff;font-size:13px;font-weight:700;
                        letter-spacing:1.5px;text-transform:uppercase;">
                ✓  Booking Confirmed
            </div>
        </td>
    </tr>

    {{-- Body --}}
    <tr>
        <td style="padding:32px 40px;">

            <p style="color:#1e293b;font-size:15px;margin:0 0 8px;">
                Dear <strong>{{ $booking->guest->full_name }}</strong>,
            </p>
            <p style="color:#475569;font-size:14px;line-height:1.7;margin:0 0 24px;">
                Your reservation at <strong>{{ $booking->hotel->name }}</strong>
                has been confirmed. Here are your details:
            </p>

            {{-- Booking reference --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#f8fafc;border-radius:8px;margin-bottom:24px;
                          border:1px solid #e2e8f0;">
            <tr>
                <td style="padding:20px;text-align:center;">
                    <div style="color:#64748b;font-size:11px;text-transform:uppercase;
                                letter-spacing:2px;margin-bottom:6px;">
                        Booking Reference
                    </div>
                    <div style="color:#d97706;font-size:28px;font-weight:700;
                                font-family:monospace;letter-spacing:1px;">
                        {{ $booking->booking_number }}
                    </div>
                </td>
            </tr>
            </table>

            {{-- Details table --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="border:1px solid #e2e8f0;border-radius:8px;
                          overflow:hidden;margin-bottom:24px;">

            <tr style="background:#f8fafc;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;
                           letter-spacing:0.5px;width:35%;">
                    Check-in
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#1e293b;
                           font-weight:600;">
                    {{ $booking->check_in->format('l, d F Y') }}
                </td>
            </tr>

            <tr style="border-top:1px solid #e2e8f0;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                    Check-out
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#1e293b;
                           font-weight:600;">
                    {{ $booking->check_out->format('l, d F Y') }}
                </td>
            </tr>

            <tr style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                    Duration
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#475569;">
                    {{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }}
                </td>
            </tr>

            <tr style="border-top:1px solid #e2e8f0;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                    Room
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#475569;">
                    Room {{ $booking->room->number }} — {{ $booking->room->type_display }}
                </td>
            </tr>

            <tr style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                    Guests
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#475569;">
                    {{ $booking->adults }} adult{{ $booking->adults !== 1 ? 's' : '' }}
                    @if($booking->children > 0)
                        + {{ $booking->children }}
                        child{{ $booking->children !== 1 ? 'ren' : '' }}
                    @endif
                </td>
            </tr>

            <tr style="border-top:2px solid #1e293b;">
                <td style="padding:16px;font-size:14px;font-weight:700;
                           color:#1e293b;">
                    Total Amount
                </td>
                <td style="padding:16px;font-size:20px;font-weight:700;
                           color:#1e293b;">
                    {{ number_format($booking->total_amount, 2) }}
                </td>
            </tr>

            </table>

            {{-- Special requests --}}
            @if($booking->special_requests)
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#fef3c7;border-radius:8px;margin-bottom:24px;
                          border:1px solid #fde68a;">
            <tr>
                <td style="padding:16px 20px;">
                    <div style="color:#92400e;font-size:11px;font-weight:700;
                                text-transform:uppercase;letter-spacing:0.5px;
                                margin-bottom:6px;">
                        Your Special Requests
                    </div>
                    <div style="color:#78350f;font-size:13px;line-height:1.6;">
                        {{ $booking->special_requests }}
                    </div>
                </td>
            </tr>
            </table>
            @endif

            {{-- What to bring --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#eff6ff;border-radius:8px;margin-bottom:24px;
                          border:1px solid #bfdbfe;">
            <tr>
                <td style="padding:16px 20px;">
                    <div style="color:#1e40af;font-size:11px;font-weight:700;
                                text-transform:uppercase;letter-spacing:0.5px;
                                margin-bottom:10px;">
                        What to Bring on Arrival
                    </div>
                    <div style="color:#1d4ed8;font-size:13px;line-height:1.8;">
                        ✓ A valid photo ID (passport or national ID)<br>
                        ✓ This confirmation email (digital or printed)<br>
                        ✓ Any payment method if balance is outstanding
                    </div>
                </td>
            </tr>
            </table>

            <p style="color:#475569;font-size:13px;line-height:1.7;margin:0;">
                We look forward to welcoming you. For any queries or changes,
                please contact us at
                <a href="mailto:{{ $booking->hotel->email }}"
                   style="color:#d97706;text-decoration:none;">
                    {{ $booking->hotel->email }}
                </a>
                or call
                <strong>{{ $booking->hotel->phone }}</strong>.
            </p>

        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="background:#f8fafc;border-top:1px solid #e2e8f0;
                   padding:20px 40px;text-align:center;">
            <p style="color:#94a3b8;font-size:12px;margin:0;line-height:1.6;">
                {{ $booking->hotel->name }} &nbsp;·&nbsp;
                {{ $booking->hotel->address }}<br>
                {{ $booking->hotel->phone }} &nbsp;·&nbsp;
                {{ $booking->hotel->email }}
            </p>
        </td>
    </tr>

</table>

</td></tr>
</table>

</body>
</html>