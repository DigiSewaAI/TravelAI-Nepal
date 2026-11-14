<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Invoice {{ $booking->booking_number }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; padding: 30px; }
    .header { display: table; width: 100%; margin-bottom: 30px; border-bottom: 2px solid #0ea5e9; padding-bottom: 15px; }
    .header .left, .header .right { display: table-cell; vertical-align: top; }
    .header .left h1 { color: #0ea5e9; font-size: 24px; margin: 0 0 5px; }
    .header .right { text-align: right; }
    .header .right h2 { font-size: 18px; color: #0ea5e9; margin: 0 0 4px; }
    .info { display: table; width: 100%; margin-bottom: 20px; }
    .info > div { display: table-cell; width: 50%; padding: 10px; background: #f9fafb; border-radius: 8px; }
    .info h3 { font-size: 11px; text-transform: uppercase; color: #6b7280; margin: 0 0 5px; }
    .info p { margin: 0; font-size: 13px; color: #111; font-weight: 700; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    table th { background: #f0f9ff; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; color: #0c4a6e; }
    table td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
    .total-row { background: #f0f9ff; font-weight: 900; font-size: 14px; color: #0ea5e9; }
    .footer { margin-top: 30px; text-align: center; color: #6b7280; font-size: 10px; }
</style>
</head>
<body>
    <div class="header">
        <div class="left">
            @php
                $logoData = null;
                if ($provider->logo_url) {
                    $logoPath = storage_path('app/public/' . $provider->logo_url);
                    if (file_exists($logoPath)) {
                        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                        $mime = ($ext === 'jpg') ? 'jpeg' : $ext;
                        $logoData = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                    }
                }
            @endphp
            @if($logoData)
                <img src="{{ $logoData }}" alt="{{ $provider->name }}" style="max-height: 60px; max-width: 180px; margin-bottom: 10px;">
            @endif
            <h1>{{ $provider->name }}</h1>
            <p>{{ $provider->address ?? 'Kathmandu, Nepal' }}</p>
            <p>{{ $provider->contact_phone ?? '' }} · {{ $provider->contact_email ?? '' }}</p>
        </div>
        <div class="right">
            <h2>INVOICE</h2>
            <p><strong>#{{ $booking->booking_number }}</strong></p>
            <p>{{ $booking->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    <div class="info">
        <div>
            <h3>Guest</h3>
            <p>{{ $booking->guest_name }}</p>
            <p style="font-size:11px; color:#6b7280; font-weight:400;">{{ $booking->guest_email }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr><th>Description</th><th>Check-in</th><th>Check-out</th><th>Nights</th><th style="text-align:right;">Amount</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Room {{ $booking->room->room_number ?? '' }} ({{ $booking->room->room_type ?? '' }})</td>
                <td>{{ $booking->check_in_date->format('M d, Y') }}</td>
                <td>{{ $booking->check_out_date->format('M d, Y') }}</td>
                <td>{{ $booking->nights }}</td>
                <td style="text-align:right;">Rs. {{ number_format($booking->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">TOTAL</td>
                <td style="text-align:right;">Rs. {{ number_format($booking->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Payment Status: {{ ucfirst($booking->payment_status) }}</p>
        <p>Thank you for choosing {{ $provider->name }}!</p>
    </div>
</body>
</html>