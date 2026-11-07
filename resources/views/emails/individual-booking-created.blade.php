<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">

    <div style="background: linear-gradient(135deg, #6366f1, #4f46e5); padding: 30px 20px; text-align: center;">
        <h1 style="color: #fff; margin: 0; font-size: 24px;">🧑‍💼 Booking Recorded</h1>
        <p style="color: #e0e7ff; margin: 8px 0 0 0; font-size: 14px;">TravelAI Nepal</p>
    </div>

    <div style="padding: 30px 25px;">
        <p style="font-size: 15px; color: #333; margin: 0 0 20px 0;">
            Namaste <strong>{{ $booking->client_name }}</strong>,
        </p>
        <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 20px 0;">
            Your booking with <strong>{{ $booking->provider->name ?? 'Guide Service' }}</strong> has been successfully recorded.
        </p>

        <div style="background: #e0e7ff; border: 2px solid #6366f1; border-radius: 12px; padding: 20px; margin: 20px 0;">
            <div style="font-size: 12px; color: #3730a3; text-transform: uppercase; font-weight: bold; margin-bottom: 8px;">Booking ID</div>
            <div style="font-size: 24px; font-weight: bold; color: #3730a3; letter-spacing: 1px;">#{{ $booking->id }}</div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr><td style="padding: 8px 0; color: #666; font-size: 13px;">Service</td><td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $booking->title }}</td></tr>
            <tr><td style="padding: 8px 0; color: #666; font-size: 13px;">Type</td><td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ ucfirst($booking->service_type) }}</td></tr>
            <tr><td style="padding: 8px 0; color: #666; font-size: 13px;">Start Date</td><td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') }}</td></tr>
            <tr><td style="padding: 8px 0; color: #666; font-size: 13px;">Duration</td><td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $booking->duration }}</td></tr>
            <tr><td style="padding: 8px 0; color: #666; font-size: 13px;">Total Amount</td><td style="padding: 8px 0; color: #059669; font-weight: bold; text-align: right; font-size: 15px;">NPR {{ number_format($booking->amount, 2) }}</td></tr>
        </table>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">

        <h2 style="font-size: 16px; color: #111; margin: 0 0 15px 0;">📱 What's next?</h2>

        @if($isRegistered)
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 12px 0;">
                ✅ Good news — your email is already registered with TravelAI Nepal!
            </p>
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 15px 0;">
                Login to your dashboard and claim this booking:
            </p>
            <ol style="font-size: 14px; color: #555; line-height: 1.8; padding-left: 20px; margin: 0 0 20px 0;">
                <li>Login: <a href="{{ $loginUrl }}" style="color: #2563eb;">{{ $loginUrl }}</a></li>
                <li>Go to <strong>Traveler Dashboard → "Claim your bookings"</strong></li>
                <li>Click <strong>"Claim Individual Booking"</strong></li>
                <li>Enter Booking ID: <strong>{{ $booking->id }}</strong></li>
                <li>Enter this email address</li>
                <li>You'll receive a 6-digit OTP to verify</li>
            </ol>
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $claimUrl }}" style="display: inline-block; background: #6366f1; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">Claim Now</a>
            </div>
        @else
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 12px 0;">
                📝 You don't have a TravelAI Nepal account yet.
            </p>
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 15px 0;">
                Register (it's free, takes 30 seconds) to claim and manage this booking:
            </p>
            <ol style="font-size: 14px; color: #555; line-height: 1.8; padding-left: 20px; margin: 0 0 20px 0;">
                <li>Register: <a href="{{ $registerUrl }}" style="color: #2563eb;">{{ $registerUrl }}</a></li>
                <li>Use this same email: <strong>{{ $booking->customer_email }}</strong></li>
                <li>Verify your email and login</li>
                <li>Go to <strong>Traveler Dashboard → "Claim your bookings"</strong></li>
                <li>Click <strong>"Claim Individual Booking"</strong></li>
                <li>Enter Booking ID: <strong>{{ $booking->id }}</strong></li>
            </ol>
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $registerUrl }}" style="display: inline-block; background: #2563eb; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">Create Free Account</a>
            </div>
        @endif

        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 15px; margin: 25px 0; border-radius: 4px;">
            <p style="font-size: 13px; color: #1e40af; margin: 0; line-height: 1.5;">
                <strong>Why claim?</strong> Claiming links this booking to your account so you can view it in your dashboard, contact your guide, and keep your trip history organized.
            </p>
        </div>
    </div>

    <div style="background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
        <p style="font-size: 11px; color: #9ca3af; margin: 0;">© {{ date('Y') }} TravelAI Nepal · All rights reserved.</p>
    </div>
</div>
</body>
</html>