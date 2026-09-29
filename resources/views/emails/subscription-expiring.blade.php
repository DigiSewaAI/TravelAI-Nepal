<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Subscription Expiring Soon</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9fafb; }
        .header { text-align: center; border-bottom: 2px solid #f59e0b; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #f59e0b; }
        .info-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 15px; margin: 15px 0; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>⏰ Subscription Expiring Soon</h1>
    </div>

    <p>Hi {{ $provider->name ?? 'there' }},</p>

    <p>This is a friendly reminder that your <strong>{{ $plan->name ?? 'subscription' }}</strong> plan will expire in <strong>{{ $daysRemaining }} day(s)</strong>.</p>

    <div class="info-box">
        <p style="margin: 0 0 8px 0;"><strong>Expiry Date:</strong> {{ $subscription->end_date ? $subscription->end_date->format('M d, Y') : 'N/A' }}</p>
        <p style="margin: 0;"><strong>Plan:</strong> {{ $plan->name ?? 'N/A' }}</p>
    </div>

    <p>To continue using all features without interruption, please renew your subscription before the expiry date.</p>

    <div style="text-align: center; margin: 20px 0;">
        <a href="{{ route('provider.subscriptions.index') }}" class="btn">Renew Subscription</a>
    </div>

    <div class="footer">
        <p>This is an automated message from TravelAI Nepal. Please do not reply to this email.</p>
        <p>© {{ date('Y') }} TravelAI Nepal — AI + data-driven trekking ecosystem.</p>
    </div>
</div>
</body>
</html>