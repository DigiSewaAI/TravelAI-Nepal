<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Invoice</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #333; margin: 0; padding: 0; }
        .invoice-box { max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; border: 1px solid #e5e7eb; }

        /* ===== HEADER (table-based — DOMPDF safe) ===== */
        .header { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header td { vertical-align: middle; padding: 0 0 20px 0; border-bottom: 3px solid #2563eb; }
        .header .col-logo { width: 140px; text-align: left; }
        .header .col-logo img { height: 80px; max-width: 130px; width: auto; }
        .header .col-center { text-align: center; }
        .header .col-center .brand-text { font-size: 22px; font-weight: bold; color: #2563eb; margin-bottom: 6px; }
        .header .col-center h1 { font-size: 28px; color: #2563eb; margin: 0; letter-spacing: 2px; }
        .header .col-right { width: 200px; text-align: right; font-size: 14px; color: #666; }
        .header .col-right strong { font-size: 18px; color: #333; display: block; margin-bottom: 4px; }

        /* ===== BILL TO / FROM (table-based) ===== */
        .bill-row { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .bill-row td { vertical-align: top; width: 50%; padding: 0; }
        .bill-row td.bill-from-cell { text-align: right; }
        .bill-to h3, .bill-from h3 { margin: 0 0 5px 0; font-size: 16px; color: #555; }
        .bill-to p, .bill-from p { margin: 2px 0; color: #666; }

        /* ===== ITEMS TABLE ===== */
        .items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items th { background: #f3f4f6; text-align: left; padding: 10px; font-weight: 600; }
        .items td { padding: 10px; border-bottom: 1px solid #eee; }
        .items .amount { text-align: right; }
        .total-row { font-weight: bold; font-size: 16px; }
        .total-row td { border-top: 2px solid #333; padding-top: 10px; }

        /* ===== MISC ===== */
        .service-info { margin: 10px 0; padding: 10px; background: #f9fafb; border-radius: 5px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="invoice-box">

        {{-- ===== HEADER: Logo | Brand + Title | Invoice# ===== --}}
        <table class="header">
            <tr>
                <td class="col-logo">
                    @if(!empty($brand['logo_base64']))
                        <img src="data:{{ $brand['logo_mime'] ?? 'image/png' }};base64,{{ $brand['logo_base64'] }}" alt="{{ $brand['name'] ?? 'TravelAI Nepal' }}">
                    @else
                        <img src="{{ public_path('images/logo.png') }}" alt="TravelAI Nepal">
                    @endif
                </td>
                <td class="col-center">
                    <div class="brand-text">{{ $brand['name'] ?? 'TravelAI Nepal' }}</div>
                    <h1>BOOKING INVOICE</h1>
                </td>
                <td class="col-right">
                    <strong>{{ $booking->booking_number ?? '#' . $booking->id }}</strong>
                    Date: {{ $booking->created_at->format('M d, Y') }}
                </td>
            </tr>
        </table>

        {{-- ===== BILL TO | BILL FROM ===== --}}
        <table class="bill-row">
            <tr>
                <td>
                    <div class="bill-to">
                        <h3>Bill To (Traveler):</h3>
                        <p><strong>{{ $traveler->name ?? 'N/A' }}</strong></p>
                        <p>{{ $traveler->email ?? 'N/A' }}</p>
                        <p>{{ $traveler->phone ?? 'N/A' }}</p>
                    </div>
                </td>
                <td class="bill-from-cell">
                    <div class="bill-from">
                        <h3>Bill From (Provider):</h3>
                        <p><strong>{{ $provider->name ?? 'N/A' }}</strong></p>
                        <p>{{ $provider->address ?? 'Kathmandu, Nepal' }}</p>
                        <p>{{ $provider->contact_email ?? 'N/A' }}</p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- ===== SERVICE INFO ===== --}}
        <div class="service-info">
            <p><strong>Service:</strong> {{ $service->name ?? 'N/A' }}</p>
            <p><strong>Start Date:</strong> {{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') : 'N/A' }}</p>
            <p><strong>Status:</strong> <span style="color: {{ $booking->status == 'confirmed' ? 'green' : 'orange' }};">{{ ucfirst($booking->status) }}</span></p>
        </div>

        {{-- ===== ITEMS ===== --}}
        <table class="items">
            <thead>
                <tr><th>Description</th><th class="amount">Amount</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $service->name ?? 'Service Booking' }}</td>
                    <td class="amount">{{ $service->currency ?? 'NPR' }} {{ number_format($service->price ?? 0, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total</td>
                    <td class="amount">{{ $service->currency ?? 'NPR' }} {{ number_format($service->price ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ===== FOOTER ===== --}}
        <div class="footer">
            <p>{{ $brand['footer_tagline'] ?? 'TravelAI Nepal — AI + data-driven trekking ecosystem. Built for Nepal, by passion.' }}</p>
            <p>{{ $brand['copyright'] ?? '© ' . date('Y') . ' TravelAI Nepal. All rights reserved.' }}</p>
            <p>This is a system-generated invoice. No signature required.</p>
        </div>
    </div>
</body>
</html>