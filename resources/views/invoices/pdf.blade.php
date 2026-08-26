<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #333; margin: 0; padding: 0; }
        .invoice-box { max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; border: 1px solid #e5e7eb; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 20px; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left img { height: 50px; }
        .header-left .brand-text { font-size: 20px; font-weight: bold; color: #2563eb; line-height: 1.2; }
        .header-center { flex: 1; text-align: center; }
        .header-center h1 { font-size: 32px; color: #2563eb; margin: 0; letter-spacing: 2px; }
        .invoice-number { text-align: right; font-size: 14px; color: #666; }
        .invoice-number strong { font-size: 18px; color: #333; display: block; }
        .bill-to { margin-bottom: 20px; }
        .bill-to h3 { margin: 0 0 5px 0; font-size: 16px; color: #555; }
        .bill-to p { margin: 2px 0; color: #666; }
        .items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items th { background: #f3f4f6; text-align: left; padding: 10px; font-weight: 600; }
        .items td { padding: 10px; border-bottom: 1px solid #eee; }
        .items .amount { text-align: right; }
        .total-row { font-weight: bold; font-size: 16px; }
        .total-row td { border-top: 2px solid #333; padding-top: 10px; }
        .payment-info { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 13px; color: #666; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header: Logo | INVOICE | Invoice Number -->
        <div class="header">
            <div class="header-left">
                <img src="{{ public_path('images/logo.png') }}" alt="TravelAI Nepal">
                <div class="brand-text">TravelAI Nepal</div>
            </div>
            <div class="header-center">
                <h1>INVOICE</h1>
            </div>
            <div class="invoice-number">
                <strong>{{ $invoice->invoice_number }}</strong>
                Date: {{ $invoice->created_at->format('M d, Y') }}
            </div>
        </div>

        <!-- Bill To -->
        <div class="bill-to">
            <h3>Bill To:</h3>
            <p><strong>{{ $invoice->provider->name ?? 'N/A' }}</strong></p>
            <p>{{ $invoice->provider->address ?? 'Kathmandu, Nepal' }}</p>
            <p>{{ $invoice->provider->user->email ?? $invoice->provider->email ?? 'N/A' }}</p>
        </div>

        <!-- Items Table -->
        <table class="items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->subscription)
                    <tr>
                        <td>Subscription: {{ ucfirst($invoice->subscription->plan ?? 'Professional') }}</td>
                        <td class="amount">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                @elseif($invoice->booking)
                    <tr>
                        <td>Booking: {{ $invoice->booking->service->name ?? 'Booking' }}</td>
                        <td class="amount">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Invoice Amount</td>
                        <td class="amount">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                @endif
                @if(($invoice->tax ?? 0) > 0)
                <tr>
                    <td>Tax</td>
                    <td class="amount">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total</td>
                    <td class="amount">{{ $invoice->currency }} {{ number_format($invoice->total ?? $invoice->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Payment Info -->
        <div class="payment-info">
            <p><strong>Payment Method:</strong> {{ $invoice->payment_method ?? 'N/A' }}</p>
            <p><strong>Paid At:</strong> {{ $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at)->format('M d, Y H:i') : 'N/A' }}</p>
            <p><strong>Status:</strong> <span style="color: {{ $invoice->status == 'paid' ? 'green' : 'red' }};">{{ ucfirst($invoice->status) }}</span></p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>TravelAI Nepal — AI + data-driven trekking ecosystem. Built for Nepal, by passion.</p>
            <p>© {{ date('Y') }} TravelAI Nepal. All rights reserved.</p>
            <p>This is a system-generated invoice. No signature required.</p>
        </div>
    </div>
</body>
</html>