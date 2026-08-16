<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            background: #ffffff;
            padding: 40px;
            margin: 0;
            color: #1e293b;
        }
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .provider-logo {
            max-height: 50px;
            max-width: 120px;
            object-fit: contain;
        }
        .provider-name {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }
        .travelai-brand {
            font-size: 14px;
            color: #64748b;
        }
        .travelai-brand strong {
            color: #2563eb;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            text-align: right;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .label {
            color: #64748b;
            font-weight: 600;
        }
        .value {
            color: #0f172a;
        }
        .bill-to {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .bill-to .name {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }
        .bill-to .detail {
            font-size: 14px;
            color: #475569;
        }
        .bill-to .meta-info {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #f1f5f9;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .total-row {
            font-size: 18px;
            font-weight: 700;
        }
        .total-row td {
            border-top: 2px solid #0f172a;
            padding-top: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        .status-paid { background: #22c55e; }
        .status-pending { background: #eab308; }
        .status-overdue { background: #ef4444; }
        .status-cancelled { background: #94a3b8; }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
        .footer strong {
            color: #2563eb;
        }
        .meta {
            font-size: 14px;
            color: #64748b;
            margin-top: 5px;
        }
        .payment-details {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }
        .payment-details strong {
            color: #0f172a;
        }
    </style>
</head>
<body>

    {{-- ====== HEADER: Provider Logo + TravelAI Brand ====== --}}
    <div class="header">
        <div class="header-left">
            {{-- Provider Logo (यदि छ भने) --}}
            @php
                $logoPath = null;
                if ($invoice->provider && $invoice->provider->logo_url) {
                    $fullPath = public_path('storage/' . $invoice->provider->logo_url);
                    if (file_exists($fullPath)) {
                        $logoData = base64_encode(file_get_contents($fullPath));
                        $logoPath = 'data:image/png;base64,' . $logoData;
                    }
                }
            @endphp

            @if($logoPath)
                <img src="{{ $logoPath }}" alt="{{ $invoice->provider->name }} logo" class="provider-logo">
            @else
                <div style="font-size: 20px; font-weight: 700; color: #2563eb;">🏔️ {{ $invoice->provider->name ?? 'Provider' }}</div>
            @endif

            <div>
                <div class="provider-name">{{ $invoice->provider->name ?? 'Provider' }}</div>
                <div class="travelai-brand">Powered by <strong>TravelAI Nepal</strong></div>
            </div>
        </div>
        <div>
            <div class="invoice-title">INVOICE</div>
            <div class="meta">#{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    {{-- ====== Date & Status ====== --}}
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div>
            <strong style="color: #0f172a;">Date:</strong> {{ $invoice->created_at->format('M d, Y') }}<br>
            <strong style="color: #0f172a;">Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
        </div>
        <div>
            <span class="status-badge status-{{ $invoice->status }}">
                {{ ucfirst($invoice->status) }}
            </span>
        </div>
    </div>

    {{-- ====== BILL TO ====== --}}
    <div class="bill-to">
        <strong style="color: #0f172a; display: block; margin-bottom: 5px;">Bill To:</strong>

        @if($invoice->booking)
            {{-- 🔥 Booking Invoice भए Traveler देखाउनुहोस् --}}
            <div class="name">{{ $invoice->booking->traveler->name ?? 'Traveler' }}</div>
            <div class="detail">{{ $invoice->booking->traveler->email ?? 'N/A' }}</div>
            <div class="detail">{{ $invoice->booking->traveler->phone ?? 'N/A' }}</div>
            <div class="meta-info">
                Booking #{{ $invoice->booking->id }} • {{ $invoice->booking->service->name ?? 'Service' }}
            </div>
        @else
            {{-- 🔥 Provider Subscription Invoice भए Provider देखाउनुहोस् --}}
            <div class="name">{{ $invoice->provider->name }}</div>
            <div class="detail">{{ $invoice->provider->address ?? 'N/A' }}</div>
            <div class="detail">{{ $invoice->provider->contact_email ?? 'N/A' }}</div>
        @endif
    </div>

    {{-- ====== INVOICE TABLE ====== --}}
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->booking)
                <tr>
                    <td>Booking: {{ $invoice->booking->service->name ?? 'Service' }}</td>
                    <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @elseif($invoice->subscription)
                <tr>
                    <td>Subscription: {{ $invoice->subscription->plan->name ?? 'Plan' }}
                        ({{ $invoice->subscription->billing_interval ?? 'Monthly' }})
                    </td>
                    <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td>Payment</td>
                    <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @endif

            @if($invoice->tax > 0)
            <tr>
                <td>Tax ({{ $invoice->tax_rate ?? 0 }}%)</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td>Total</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ====== PAYMENT DETAILS ====== --}}
    <div class="payment-details">
    <strong>Payment Method:</strong> {{ $invoice->payment_method ?? 'N/A' }}<br>
    <strong>Paid At:</strong> {{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y H:i') : 'N/A' }}
    
    @if($invoice->metadata)
        <br><strong>Reference:</strong>
        @php
            $meta = is_string($invoice->metadata) ? json_decode($invoice->metadata, true) : $invoice->metadata;
        @endphp
        @if(is_array($meta))
            @foreach($meta as $key => $value)
                {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}<br>
            @endforeach
        @else
            {{ $invoice->metadata }}
        @endif
    @endif
</div>

    {{-- ====== FOOTER ====== --}}
    <div class="footer">
        TravelAI Nepal — AI + data-driven trekking ecosystem. Built for Nepal, by passion.<br>
        © {{ date('Y') }} TravelAI Nepal. All rights reserved.<br>
        <span style="font-size: 10px; color: #94a3b8;">This is a system-generated invoice. No signature required.</span>
    </div>

</body>
</html>