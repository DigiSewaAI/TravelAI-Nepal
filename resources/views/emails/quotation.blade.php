<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quotation from {{ $provider->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9fafb; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #2563eb; }
        .content { white-space: pre-wrap; font-family: monospace; background: white; padding: 15px; border-radius: 4px; border: 1px solid #eee; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📄 Quotation</h1>
        <p><strong>Provider:</strong> {{ $provider->name }}</p>
        <p><strong>Traveler:</strong> {{ $travelerName }}</p>
        <p><strong>Date:</strong> {{ now()->format('M d, Y H:i') }}</p>
    </div>

    {{-- Quotation Content --}}
    <div class="content">
        {!! nl2br(e($quotationText)) !!}
    </div>

    {{-- ✅ Button with WHITE text – Inline Styles + <span> --}}
    <div style="margin-top: 20px; text-align: center;">
        <a href="{{ route('home') }}" style="display: inline-block; background: #2563eb; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
            <span style="color: #ffffff; font-weight: bold;">Visit TravelAI Nepal</span>
        </a>
    </div>

    <div class="footer">
        <p>This is an automated message from TravelAI Nepal. Please do not reply to this email.</p>
        <p>© {{ date('Y') }} TravelAI Nepal — AI + data-driven trekking ecosystem.</p>
    </div>
</div>
</body>
</html>