<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background: #f8fafc;">
    <div style="max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <img src="{{ asset('images/logo.png') }}" alt="TravelAI Nepal" style="max-height: 50px; margin-bottom: 20px;">
        <h2 style="color: #1e293b;">Invoice #{{ $invoice->invoice_number }}</h2>
        <p style="color: #475569;">Dear {{ $provider->name }},</p>
        <p style="color: #475569;">Thank you for your payment. Please find your invoice attached.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 8px 0; color: #475569;"><strong>Invoice #:</strong></td>
                <td style="padding: 8px 0; text-align: right;">{{ $invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #475569;"><strong>Date:</strong></td>
                <td style="padding: 8px 0; text-align: right;">{{ $invoice->created_at->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #475569;"><strong>Amount:</strong></td>
                <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #2563eb;">
                    {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #475569;"><strong>Status:</strong></td>
                <td style="padding: 8px 0; text-align: right;">
                    <span style="background: {{ $invoice->status === 'paid' ? '#dcfce7' : '#fef08a' }}; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </td>
            </tr>
        </table>

        <p style="color: #475569;">You can view and download all your invoices from your <a href="{{ url('/provider/payments') }}" style="color: #2563eb;">Provider Dashboard</a>.</p>
        <p style="color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
            TravelAI Nepal — AI + data-driven trekking ecosystem. Built for Nepal, by passion.
        </p>
    </div>
</body>
</html>