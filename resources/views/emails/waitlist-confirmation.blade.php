<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>You're on the Waitlist!</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9fafb; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #2563eb; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📩 You're on the Waitlist!</h1>
    </div>

    <p>Hi there,</p>

    <p>Thank you for joining the <strong>TravelAI Nepal</strong> waitlist! 🎉</p>

    <p>We're building something amazing for Nepal's trekking community, and we can't wait to share it with you.</p>

    <p><strong>What's next?</strong></p>
    <ul>
        <li>✅ You'll be among the first to know when we launch.</li>
        <li>✅ Early access users get <strong>6 months free</strong> + lifetime discounted upgrades.</li>
        <li>✅ We'll notify you via email – stay tuned!</li>
    </ul>

    <p>In the meantime, feel free to explore our platform:</p>

    <div style="text-align: center; margin: 20px 0;">
        <a href="{{ route('home') }}" class="btn">Visit TravelAI Nepal</a>
    </div>

    <p>Your email: <strong>{{ $email }}</strong></p>

    <div class="footer">
        <p>This is an automated message from TravelAI Nepal. Please do not reply to this email.</p>
        <p>© {{ date('Y') }} TravelAI Nepal — AI + data-driven trekking ecosystem.</p>
    </div>
</div>
</body>
</html>