<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Invoice</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #333; margin: 0; padding: 0; }
        .invoice-box { max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; border: 1px solid #e5e7eb; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 20px; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left img { height: 50px; }
        .header-left .brand-text { font-size: 20px; font-weight: bold; color: #2563eb; }
        .header-center { flex: 1; text-align: center; }
        .header-center h1 { font-size: 32px; color: #2563eb; margin: 0; letter-spacing: 2px; }
        .invoice-number { text-align: right; font-size: 14px; color: #666; }
        .invoice-number strong { font-size: 18px; color: #333; display: block; }
        .bill-to, .bill-from { margin-bottom: 20px; }
        .bill-to h3, .bill-from h3 { margin: 0 0 5px 0; font-size: 16px; color: #555; }
        .bill-to p, .bill-from p { margin: 2px 0; color: #666; }
        .items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items th { background: #f3f4f6; text-align: left; padding: 10px; font-weight: 600; }
        .items td { padding: 10px; border-bottom: 1px solid #eee; }
        .items .amount { text-align: right; }
        .total-row { font-weight: bold; font-size: 16px; }
        .total-row td { border-top: 2px solid #333; padding-top: 10px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="header-left">
                <img src="{{ public_path('images/logo.png') }}" alt="TravelAI Nepal">
                <div class="brand-text">TravelAI Nepal</div>
            </div>
            <div class="header-center">
                <h1>BOOKING INVOICE</h1>
            </div>
            <div class="invoice-number">
                <strong>#{{ $booking->id }}</strong>
                Date: {{ $booking->created_at->format('M d, Y') }}
            </div>
        </div>

        <div style="display: flex; justify-content: space-between;">
            <div class="bill-to">
                <h3>Bill To (Traveler):</h3>
                <p><strong>{{ $traveler->name ?? 'N/A' }}</strong></p>
                <p>{{ $traveler->email ?? 'N/A' }}</p>
                <p>{{ $traveler->phone ?? 'N/A' }}</p>
            </div>
            <div class="bill-from" style="text-align: right;">
                <h3>Bill From (Provider):</h3>
                <p><strong>{{ $provider->name ?? 'N/A' }}</strong></p>
                <p>{{ $provider->address ?? 'Kathmandu, Nepal' }}</p>
                <p>{{ $provider->email ?? 'N/A' }}</p>
            </div>
        </div>

        <div style="margin: 10px 0; padding: 10px; background: #f9fafb; border-radius: 5px;">
            <p><strong>Service:</strong> {{ $service->name ?? 'N/A' }}</p>
            <p><strong>Start Date:</strong> {{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') : 'N/A' }}</p>
            <p><strong>Status:</strong> <span style="color: {{ $booking->status == 'confirmed' ? 'green' : 'orange' }};">{{ ucfirst($booking->status) }}</span></p>
        </div>

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

        <div class="footer">
            <p>TravelAI Nepal — AI + data-driven trekking ecosystem. Built for Nepal, by passion.</p>
            <p>© {{ date('Y') }} TravelAI Nepal. All rights reserved.</p>
            <p>This is a system-generated invoice. No signature required.</p>
        </div>
    </div>
</body>
</html>