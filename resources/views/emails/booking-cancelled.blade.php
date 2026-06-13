<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Cancelled</title>
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

    {{-- Red cancelled banner --}}
    <tr>
        <td style="background:#ef4444;padding:14px 40px;text-align:center;">
            <div style="color:#ffffff;font-size:13px;font-weight:700;
                        letter-spacing:1.5px;text-transform:uppercase;">
                ✕  Booking Cancelled
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
                Your booking at <strong>{{ $booking->hotel->name }}</strong>
                has been cancelled. Your booking reference was:
            </p>

            {{-- Reference --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#fef2f2;border-radius:8px;margin-bottom:24px;
                          border:1px solid #fecaca;">
            <tr>
                <td style="padding:20px;text-align:center;">
                    <div style="color:#64748b;font-size:11px;text-transform:uppercase;
                                letter-spacing:2px;margin-bottom:6px;">
                        Cancelled Booking
                    </div>
                    <div style="color:#dc2626;font-size:26px;font-weight:700;
                                font-family:monospace;text-decoration:line-through;
                                opacity:0.7;">
                        {{ $booking->booking_number }}
                    </div>
                    @if($booking->cancelled_at)
                    <div style="color:#94a3b8;font-size:12px;margin-top:6px;">
                        Cancelled on {{ $booking->cancelled_at->format('d M Y, H:i') }}
                    </div>
                    @endif
                </td>
            </tr>
            </table>

            {{-- Cancellation reason --}}
            @if($booking->cancellation_reason)
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#f8fafc;border-radius:8px;margin-bottom:24px;
                          border:1px solid #e2e8f0;">
            <tr>
                <td style="padding:16px 20px;">
                    <div style="color:#64748b;font-size:11px;font-weight:700;
                                text-transform:uppercase;margin-bottom:6px;">
                        Reason
                    </div>
                    <div style="color:#475569;font-size:14px;">
                        {{ $booking->cancellation_reason }}
                    </div>
                </td>
            </tr>
            </table>
            @endif

            {{-- Rebook CTA --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#eff6ff;border-radius:8px;margin-bottom:24px;
                          border:1px solid #bfdbfe;">
            <tr>
                <td style="padding:20px;text-align:center;">
                    <div style="color:#1e40af;font-size:14px;font-weight:600;
                                margin-bottom:4px;">
                        Want to rebook?
                    </div>
                    <div style="color:#3b82f6;font-size:13px;">
                        Contact us at
                        <a href="mailto:{{ $booking->hotel->email }}"
                           style="color:#2563eb;">
                            {{ $booking->hotel->email }}
                        </a>
                        or call {{ $booking->hotel->phone }}
                    </div>
                </td>
            </tr>
            </table>

            <p style="color:#475569;font-size:13px;line-height:1.7;margin:0;">
                We hope to welcome you to {{ $booking->hotel->name }} in the future.
                If you have any questions about this cancellation, please do not
                hesitate to contact us.
            </p>

        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="background:#f8fafc;border-top:1px solid #e2e8f0;
                   padding:20px 40px;text-align:center;">
            <p style="color:#94a3b8;font-size:12px;margin:0;line-height:1.6;">
                {{ $booking->hotel->name }} &nbsp;·&nbsp; {{ $booking->hotel->phone }}
                &nbsp;·&nbsp; {{ $booking->hotel->email }}
            </p>
        </td>
    </tr>

</table>
</td></tr>
</table>

</body>
</html>