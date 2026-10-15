<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background: #f8fafc; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">

        <div style="background: #2563eb; padding: 25px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 22px;">New Contact Message</h1>
        </div>

        <div style="padding: 30px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px; width: 120px;">From:</td>
                    <td style="padding: 10px 0; color: #1e293b; font-size: 14px; font-weight: 600;">{{ $data['name'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px;">Email:</td>
                    <td style="padding: 10px 0; color: #1e293b; font-size: 14px;">{{ $data['email'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px;">Subject:</td>
                    <td style="padding: 10px 0; color: #1e293b; font-size: 14px;">{{ $data['subject'] }}</td>
                </tr>
            </table>

            <div style="margin-top: 20px; padding: 15px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #2563eb;">
                <p style="color: #64748b; font-size: 12px; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 1px;">Message</p>
                <p style="color: #1e293b; font-size: 14px; margin: 0; line-height: 1.6; white-space: pre-wrap;">{{ $data['message'] }}</p>
            </div>
        </div>

        <div style="background: #f1f5f9; padding: 15px; text-align: center;">
            <p style="color: #94a3b8; font-size: 11px; margin: 0;">Sent from TravelAI Nepal contact form</p>
        </div>
    </div>
</body>
</html>