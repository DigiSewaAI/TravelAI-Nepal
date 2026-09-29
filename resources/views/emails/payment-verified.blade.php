<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Verified</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9fafb; }
        .header { text-align: center; border-bottom: 2px solid #16a34a; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #16a34a; }
        .info-box { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin: 15px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
        .info-row:last-child { border-bottom: none; }
        .label { color: #6b7280; font-size: 14px; }
        .value { font-weight: bold; color: #111827; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>✅ Payment Verified</h1>
    </div>

    <p>Hi {{ $provider->name ?? 'there' }},</p>

    <p>Great news! Your payment has been verified by our team. Your <strong>{{ $plan->name ?? 'subscription' }}</strong> plan is now active.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="label">Payment Reference</span>
            <span class="value">{{ $payment->reference_number ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Amount</span>
            <span class="value">{{ $payment->currency ?? 'NPR' }} {{ number_format($payment->amount ?? 0, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Plan</span>
            <span class="value">{{ $plan->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Start Date</span>
            <span class="value">{{ $subscription->start_date ? $subscription->start_date->format('M d, Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="label">End Date</span>
            <span class="value">{{ $subscription->end_date ? $subscription->end_date->format('M d, Y') : 'N/A' }}</span>
        </div>
        @if(!empty($invoice))
        <div class="info-row">
            <span class="label">Invoice Number</span>
            <span class="value">{{ $invoice->invoice_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Total (incl. 13% VAT)</span>
            <span class="value">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
        </div>
        @endif
    </div>

    <p>You can view your invoice and subscription details in your provider dashboard.</p>

    <div style="text-align: center; margin: 20px 0;">
        <a href="{{ route('provider.subscriptions.index') }}" class="btn">View Subscription</a>
    </div>

    <div class="footer">
        <p>This is an automated message from TravelAI Nepal. Please do not reply to this email.</p>
        <p>© {{ date('Y') }} TravelAI Nepal — AI + data-driven trekking ecosystem.</p>
    </div>
</div>
</body>
</html>