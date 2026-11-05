@php
    $w = $weatherData ?? null;
    $temp = null;
    $condition = 'Partly Cloudy';
    if (is_array($w) && isset($w['temperature'])) $temp = $w['temperature'];
    elseif (is_object($w) && isset($w->temperature)) $temp = $w->temperature;
@endphp

@if($temp !== null)
<div style="background: #fff; border-radius: 14px; padding: 16px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; min-width: 180px;">
    <div style="font-size: 32px;">☀️</div>
    <div>
        <div style="font-size: 13px; font-weight: 700; color: #111827;">Kathmandu</div>
        <div style="font-size: 22px; font-weight: 800; color: #2563eb; line-height: 1.1;">{{ round($temp) }}°C</div>
        <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">{{ __('messages.weather_partly_cloudy') ?? 'Partly Cloudy' }}</div>
    </div>
</div>
@endif