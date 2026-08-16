<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; padding: 40px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { max-height: 60px; }
        .company { font-size: 24px; font-weight: bold; color: #1e293b; }
        .title { font-size: 28px; font-weight: bold; color: #2563eb; margin-top: 10px; }
        .invoice-info { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .invoice-info .left { font-size: 14px; color: #475569; }
        .invoice-info .right { text-align: right; font-size: 14px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th { background: #f1f5f9; padding: 10px; text-align: left; font-weight: 600; color: #1e293b; }
        table td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .total-row { font-weight: bold; font-size: 16px; }
        .grand-total { font-size: 20px; color: #2563eb; font-weight: bold; }
        .footer { text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #94a3b8; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef08a; color: #854d0e; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="TravelAI Nepal" class="logo">
        <div class="company">TravelAI Nepal</div>
        <div class="title">INVOICE</div>
    </div>

    <div class="invoice-info">
        <div class="left">
            <strong>Bill To:</strong><br>
            {{ $provider->name }}<br>
            {{ $provider->address ?? 'N/A' }}<br>
            {{ $provider->user->email ?? $provider->email ?? '' }}
        </div>
        <div class="right">
            <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
            <strong>Date:</strong> {{ $invoice->created_at->format('M d, Y') }}<br>
            <strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}<br>
            <strong>Status:</strong> <span class="status status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $invoice->metadata['plan_name'] ?? $invoice->subscription->plan->name ?? 'Subscription' }}</strong><br>
                    <small>{{ $invoice->metadata['billing_interval'] ?? $invoice->subscription->billing_interval ?? 'Monthly' }}</small>
                </td>
                <td>{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
            </tr>
            @if($invoice->tax > 0)
            <tr>
                <td>Tax ({{ $invoice->tax_rate ?? 13 }}%)</td>
                <td>{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>Total</strong></td>
                <td class="grand-total">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p style="font-size: 14px; color: #475569; margin-top: 20px;">
        <strong>Payment Method:</strong> {{ ucfirst($invoice->payment_method) }}<br>
        <strong>Paid At:</strong> {{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y H:i') : 'N/A' }}
    </p>

    <div class="footer">
        <p>TravelAI Nepal — AI + data-driven trekking ecosystem. Built for Nepal, by passion.</p>
        <p>© {{ date('Y') }} TravelAI Nepal. All rights reserved.</p>
        <p>This is a system-generated invoice. No signature required.</p>
    </div>
</body>
</html>