<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background: #f8fafc; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">

        <div style="background: #d97706; padding: 25px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 22px;">New Enterprise Inquiry</h1>
        </div>

        <div style="padding: 30px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px; width: 140px;">Company:</td>
                    <td style="padding: 10px 0; color: #1e293b; font-size: 14px; font-weight: 600;">{{ $data['company_name'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px;">Contact Person:</td>
                    <td style="padding: 10px 0; color: #1e293b; font-size: 14px;">{{ $data['contact_person'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px;">Email:</td>
                    <td style="padding: 10px 0; color: #1e293b; font-size: 14px;">{{ $data['email'] }}</td>
                </tr>
                @if(!empty($data['phone']))
                <tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px;">Phone:</td>
                    <td style="padding: 10px 0; color: #1e293b; font-size: 14px;">{{ $data['phone'] }}</td>
                </tr>
                @endif
            </table>

            <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #d97706;">
                <p style="color: #92400e; font-size: 12px; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 1px;">Requirements</p>
                <p style="color: #1e293b; font-size: 14px; margin: 0; line-height: 1.6; white-space: pre-wrap;">{{ $data['requirements'] }}</p>
            </div>
        </div>

        <div style="background: #f1f5f9; padding: 15px; text-align: center;">
            <p style="color: #94a3b8; font-size: 11px; margin: 0;">Sent from TravelAI Nepal enterprise inquiry form</p>
        </div>
    </div>
</body>
</html>