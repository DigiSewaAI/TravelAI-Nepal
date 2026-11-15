<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; background: #fff; padding: 40px; margin: 0; color: #1e293b; }
        .header { border-bottom: 3px solid #0284c7; padding-bottom: 15px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .brand-logo { max-height: 50px; max-width: 120px; object-fit: contain; }
        .brand-name { font-size: 20px; font-weight: 700; color: #0284c7; line-height: 1.2; }
        .brand-contact { font-size: 11px; color: #64748b; }
        .receipt-title { font-size: 24px; font-weight: 700; color: #0f172a; text-align: right; }
        .receipt-number { font-size: 14px; color: #64748b; text-align: right; margin-top: 4px; font-family: monospace; }
        .info-grid { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 24px; flex-wrap: wrap; }
        .info-box { flex: 1; background: #f8fafc; padding: 14px 18px; border-radius: 8px; min-width: 220px; }
        .info-box .label { font-size: 10px; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
        .info-box .value { font-size: 14px; color: #0f172a; font-weight: 600; }
        .info-box .detail { font-size: 12px; color: #64748b; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f1f5f9; padding: 12px 15px; text-align: left; font-weight: 700; color: #0f172a; border-bottom: 2px solid #e2e8f0; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; }
        td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        .amount-col { text-align: right; }
        .total-row { font-size: 16px; font-weight: 800; }
        .total-row td { border-top: 2px solid #0f172a; padding-top: 15px; background: #f8fafc; }
        .total-amount { color: #0284c7; font-size: 18px; }
        .payment-info { background: #f0f9ff; border-left: 4px solid #0284c7; padding: 14px 18px; border-radius: 8px; margin-top: 20px; font-size: 12px; }
        .payment-info strong { color: #0f172a; font-weight: 700; }
        .legal-notice { background: #fef4e2; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 8px; margin-top: 20px; font-size: 11px; color: #92400e; }
        .legal-notice strong { font-weight: 800; }
        .footer { margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center; font-size: 11px; color: #94a3b8; }
        .footer strong { color: #0284c7; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            @if(!empty($brand['logo_base64']))
                <img src="data:{{ $brand['logo_mime'] }};base64,{{ $brand['logo_base64'] }}" alt="{{ $brand['name'] }}" class="brand-logo">
            @else
                <div style="font-size: 32px;">🚌</div>
            @endif
            <div>
                <div class="brand-name">{{ $brand['name'] }}</div>
                @if(!empty($brand['contact']))
                    <div class="brand-contact">{{ $brand['contact'] }}</div>
                @endif
            </div>
        </div>
        <div>
            <div class="receipt-title">PAYMENT RECEIPT</div>
            <div class="receipt-number">#{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="label">Customer</div>
            <div class="value">{{ $booking->passenger_name }}</div>
            <div class="detail">{{ $booking->passenger_email }}</div>
        </div>
        <div class="info-box">
            <div class="label">Booking Reference</div>
            <div class="value" style="font-family: monospace;">{{ $booking->booking_ref }}</div>
            <div class="detail">Issued: {{ $invoice->created_at->format('M d, Y') }}</div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box" style="flex: 2;">
            <div class="label">Trip Details</div>
            <div class="value">{{ $booking->route_name }}</div>
            <div class="detail">
                📅 {{ $booking->travel_date?->format('M d, Y') }}
                @if($booking->travel_time) · {{ substr($booking->travel_time, 0, 5) }} @endif
                @if($booking->vehicle) · {{ $booking->vehicle->vehicle_number }} ({{ $booking->vehicle->vehicle_type }}) @endif
            </div>
            @if($booking->pickup_point)
                <div class="detail">📍 Pickup: {{ $booking->pickup_point }}</div>
            @endif
            <div class="detail">👥 {{ $booking->passenger_count }} passenger(s) · {{ $booking->booking_type === 'full_vehicle' ? 'Full Vehicle' : 'Per Seat' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount-col">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Transport Service — {{ $booking->route_name }}<br>
                    <span style="font-size:11px;color:#64748b;">
                        {{ $booking->passenger_count }} × Rs {{ number_format((float)($booking->price_per_unit ?? $booking->amount), 2) }}
                    </span>
                </td>
                <td class="amount-col">Rs {{ number_format((float)$invoice->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Paid</td>
                <td class="amount-col total-amount">Rs {{ number_format((float)$invoice->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="payment-info">
        <strong>Payment Method:</strong> {{ ucfirst($invoice->payment_method ?? 'N/A') }}<br>
        @if($booking->payment_ref)
            <strong>Transaction Reference:</strong> {{ $booking->payment_ref }}<br>
        @endif
        <strong>Paid At:</strong> {{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y H:i') : 'N/A' }}
    </div>

    <div class="legal-notice">
        <strong>Legal Notice:</strong> This is a <strong>Payment Receipt</strong>, not a Tax Invoice. A VAT Tax Invoice is issued separately only if the provider is VAT-registered as per Nepal IRD regulations.
    </div>

    <div class="footer">
        {{ $brand['name'] }}<br>
        {{ $brand['copyright'] }} · {{ date('Y') }}<br>
        <span style="font-size: 10px;">System-generated receipt — no signature required.</span>
    </div>

</body>
</html>