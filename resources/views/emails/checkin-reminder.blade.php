<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check-in Reminder</title>
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
                {{ $booking->hotel->name }}
            </div>
            <div style="color:#64748b;font-size:12px;margin-top:4px;">
                {{ $booking->hotel->address }}
            </div>
        </td>
    </tr>

    {{-- Amber reminder banner --}}
    <tr>
        <td style="background:#f59e0b;padding:14px 40px;text-align:center;">
            <div style="color:#ffffff;font-size:13px;font-weight:700;
                        letter-spacing:1.5px;text-transform:uppercase;">
                🔔  Your Stay Begins Tomorrow
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
                This is a friendly reminder that your stay at
                <strong>{{ $booking->hotel->name }}</strong> begins
                <strong>tomorrow</strong>. We're excited to welcome you!
            </p>

            {{-- Reference --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#fffbeb;border-radius:8px;margin-bottom:24px;
                          border:1px solid #fde68a;">
            <tr>
                <td style="padding:20px;text-align:center;">
                    <div style="color:#64748b;font-size:11px;text-transform:uppercase;
                                letter-spacing:2px;margin-bottom:6px;">
                        Your Booking
                    </div>
                    <div style="color:#d97706;font-size:26px;font-weight:700;
                                font-family:monospace;">
                        {{ $booking->booking_number }}
                    </div>
                </td>
            </tr>
            </table>

            {{-- Summary --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="border:1px solid #e2e8f0;border-radius:8px;
                          overflow:hidden;margin-bottom:24px;">
            <tr style="background:#f8fafc;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;width:35%;">
                    Check-in Date
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#1e293b;
                           font-weight:700;">
                    {{ $booking->check_in->format('l, d F Y') }}
                </td>
            </tr>
            <tr style="border-top:1px solid #e2e8f0;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;">
                    Check-out Date
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#475569;">
                    {{ $booking->check_out->format('l, d F Y') }}
                </td>
            </tr>
            <tr style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                <td style="padding:12px 16px;font-size:11px;color:#94a3b8;
                           font-weight:700;text-transform:uppercase;">
                    Room
                </td>
                <td style="padding:12px 16px;font-size:14px;color:#475569;">
                    Room {{ $booking->room->number }} ({{ $booking->room->type_display }})
                </td>
            </tr>
            </table>

            {{-- Checklist --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#f0fdf4;border-radius:8px;margin-bottom:24px;
                          border:1px solid #bbf7d0;">
            <tr>
                <td style="padding:16px 20px;">
                    <div style="color:#166534;font-size:11px;font-weight:700;
                                text-transform:uppercase;letter-spacing:0.5px;
                                margin-bottom:10px;">
                        Pre-Arrival Checklist
                    </div>
                    <div style="color:#15803d;font-size:13px;line-height:1.9;">
                        ✓ Valid photo ID (passport or national ID card)<br>
                        ✓ This confirmation email<br>
                        ✓ Any special items you requested<br>
                        ✓ Payment method for outstanding charges
                    </div>
                </td>
            </tr>
            </table>

            {{-- Hotel info --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#f8fafc;border-radius:8px;
                          border:1px solid #e2e8f0;">
            <tr>
                <td style="padding:16px 20px;">
                    <div style="color:#64748b;font-size:11px;font-weight:700;
                                text-transform:uppercase;margin-bottom:8px;">
                        Hotel Location & Contact
                    </div>
                    <div style="color:#475569;font-size:13px;line-height:1.8;">
                        📍 {{ $booking->hotel->address }}<br>
                        📞 {{ $booking->hotel->phone }}<br>
                        ✉ {{ $booking->hotel->email }}
                    </div>
                </td>
            </tr>
            </table>

        </td>
    </tr>

    <tr>
        <td style="background:#f8fafc;border-top:1px solid #e2e8f0;
                   padding:20px 40px;text-align:center;">
            <p style="color:#94a3b8;font-size:12px;margin:0;">
                We look forward to seeing you tomorrow!<br>
                <strong>{{ $booking->hotel->name }}</strong>
            </p>
        </td>
    </tr>

</table>
</td></tr>
</table>

</body>
</html>