<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.invoice_pdf_title', ['number' => $invoice->invoice_number]) }}</title>
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
        .brand-logo {
            max-height: 50px;
            max-width: 120px;
            object-fit: contain;
        }
        .brand-name {
            font-size: 20px;
            font-weight: 700;
            color: #2563eb;
            line-height: 1.2;
        }
        .brand-sub {
            font-size: 12px;
            color: #64748b;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            text-align: right;
        }
        .meta {
            font-size: 14px;
            color: #64748b;
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

    {{-- ====== HEADER: TravelAI Nepal Branding (Issuer) ====== --}}
    <div class="header">
        <div class="header-left">
            {{-- TravelAI Logo --}}
            @php
                $logoData = null;
                $logoPath = public_path('images/logo.png');
                if (file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                }
            @endphp
            @if($logoData)
                <img src="data:image/png;base64,{{ $logoData }}" alt="TravelAI Nepal" class="brand-logo">
            @else
                <div style="font-size: 28px; font-weight: 700; color: #2563eb;">🏔️</div>
            @endif

            <div>
                <div class="brand-name">TravelAI Nepal</div>
                
            </div>
        </div>
        <div>
            <div class="invoice-title">{{ __('messages.invoice') }}</div>
            <div class="meta">#{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    {{-- ====== Date & Status ====== --}}
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div>
            <strong style="color: #0f172a;">{{ __('messages.date_label') }}:</strong> {{ $invoice->created_at->format('M d, Y') }}<br>
            <strong style="color: #0f172a;">{{ __('messages.due_date_label') }}:</strong> {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : __('messages.na') }}
        </div>
        <div>
            <span class="status-badge status-{{ $invoice->status }}">
                @if($invoice->status === 'paid') {{ __('messages.paid') }}
                @elseif($invoice->status === 'pending') {{ __('messages.pending') }}
                @elseif($invoice->status === 'overdue') {{ __('messages.overdue') }}
                @else {{ ucfirst($invoice->status) }} @endif
            </span>
        </div>
    </div>

    {{-- ====== BILL TO ====== --}}
    <div class="bill-to">
        <strong style="color: #0f172a; display: block; margin-bottom: 5px;">{{ __('messages.bill_to') }}:</strong>

        @if($invoice->booking)
            {{-- 🔥 Booking Invoice भए Traveler देखाउनुहोस् --}}
            <div class="name">{{ $invoice->booking->traveler->name ?? __('messages.traveler') }}</div>
            <div class="detail">{{ $invoice->booking->traveler->email ?? __('messages.na') }}</div>
            <div class="detail">{{ $invoice->booking->traveler->phone ?? __('messages.na') }}</div>
            <div class="meta-info">
                {{ __('messages.booking_hash_short', ['id' => $invoice->booking->id]) }} • {{ $invoice->booking->service->name ?? __('messages.service') }}
            </div>
        @else
            {{-- 🔥 Provider Subscription Invoice भए Provider देखाउनुहोस् --}}
            <div class="name">{{ $invoice->provider->name ?? 'N/A' }}</div>
            <div class="detail">{{ $invoice->provider->address ?? __('messages.na') }}</div>
            <div class="detail">{{ $invoice->provider->contact_email ?? __('messages.na') }}</div>
        @endif
    </div>

    {{-- ====== INVOICE TABLE ====== --}}
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.description') }}</th>
                <th style="text-align: right;">{{ __('messages.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->booking)
                <tr>
                    <td>{{ __('messages.booking_service') }}: {{ $invoice->booking->service->name ?? __('messages.service') }}</td>
                    <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @elseif($invoice->subscription)
                <tr>
                    <td>{{ __('messages.subscription_plan') }}: {{ $invoice->subscription->plan->name ?? __('messages.plan') }}
                        ({{ $invoice->subscription->billing_interval ?? __('messages.monthly') }})
                    </td>
                    <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td>{{ __('messages.payment') }}</td>
                    <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @endif

            @if($invoice->tax > 0)
            <tr>
                <td>{{ __('messages.tax') }} ({{ $invoice->tax_rate ?? 0 }}%)</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td>{{ __('messages.total') }}</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->total ?? $invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ====== PAYMENT DETAILS ====== --}}
    <div class="payment-details">
        <strong>{{ __('messages.payment_method_label') }}:</strong> {{ $invoice->payment_method ?? __('messages.na') }}<br>
        <strong>{{ __('messages.paid_at_label') }}:</strong> {{ $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at)->format('M d, Y H:i') : __('messages.na') }}
        
        @if($invoice->metadata)
            <br><strong>{{ __('messages.reference') }}:</strong>
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
        {{ __('messages.footer_text') }}<br>
        © {{ date('Y') }} TravelAI Nepal. {{ __('messages.all_rights_reserved') }}<br>
        <span style="font-size: 10px; color: #94a3b8;">{{ __('messages.system_generated_notice') }}</span>
    </div>

</body>
</html>