<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Subscription Expired</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9fafb; }
        .header { text-align: center; border-bottom: 2px solid #dc2626; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #dc2626; }
        .info-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 15px; margin: 15px 0; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Subscription Expired</h1>
    </div>

    <p>Hi {{ $provider->name ?? 'there' }},</p>

    <p>Your <strong>{{ $plan->name ?? 'subscription' }}</strong> plan has expired on <strong>{{ $subscription->end_date ? $subscription->end_date->format('M d, Y') : 'N/A' }}</strong>.</p>

    <div class="info-box">
        <p style="margin: 0;">Some features may no longer be available until you renew. Don't worry — your data is safe.</p>
    </div>

    <p>You can renew anytime to restore full access to your account.</p>

    <div style="text-align: center; margin: 20px 0;">
        <a href="{{ route('provider.subscriptions.index') }}" class="btn">Renew Now</a>
    </div>

    <div class="footer">
        <p>This is an automated message from TravelAI Nepal. Please do not reply to this email.</p>
        <p>© {{ date('Y') }} TravelAI Nepal — AI + data-driven trekking ecosystem.</p>
    </div>
</div>
</body>
</html>