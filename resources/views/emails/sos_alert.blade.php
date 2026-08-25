<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.sos_email_title') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .alert-box { background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; }
        .button { background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2 style="color: #dc2626;">🚨 {{ __('messages.sos_email_heading') }}</h2>
    <p>{{ __('messages.sos_email_dear', ['name' => $agency_name]) }},</p>
    <p>{{ __('messages.sos_email_intro') }}</p>

    <div class="alert-box">
        <p><strong>{{ __('messages.sos_email_trekker') }}:</strong> {{ $trekker_name }}</p>
        <p><strong>{{ __('messages.sos_email_trek') }}:</strong> {{ $trek_name }}</p>
        <p><strong>{{ __('messages.sos_email_message') }}:</strong> {{ $message }}</p>
        <p><strong>{{ __('messages.sos_email_location') }}:</strong><br>
            {{ __('messages.sos_email_latitude') }}: {{ $latitude }}<br>
            {{ __('messages.sos_email_longitude') }}: {{ $longitude }}<br>
            <a href="{{ $google_maps_link }}" target="_blank" class="button">{{ __('messages.sos_email_view_map') }}</a>
        </p>
        <p><strong>{{ __('messages.sos_email_time') }}:</strong> {{ $alert_time }}</p>
    </div>

    <p><strong>{{ __('messages.sos_email_action_required') }}</strong></p>
    <hr>
    <p style="font-size: 12px; color: #666;">{{ __('messages.sos_email_auto_message') }}</p>
</div>
</body>
</html>