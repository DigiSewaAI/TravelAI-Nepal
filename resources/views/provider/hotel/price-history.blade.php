@extends($provider_layout ?? 'layouts.provider-hotel')

@section('title', 'Price History')
@section('page-title', 'Price History')
@section('page-subtitle', 'Audit trail of all room price changes')

@section('content')

@if (session('success'))
<div style="background:#f0fdf4;border:2px solid #10b981;border-radius:12px;padding:14px;margin-bottom:16px;font-size:13px;color:#065f46;font-weight:600;">✅ {{ session('success') }}</div>
@endif

<div style="padding:24px;max-width:1200px;margin:0 auto;">
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e6ecf4);border-radius:16px;padding:22px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1 style="font-size:22px;font-weight:800;margin:0 0 6px;">📊 Price History</h1>
            <p style="font-size:13px;color:var(--muted,#6b7a90);margin:0;">{{ count($history) }} change(s) recorded</p>
        </div>
        <a href="{{ route('provider.hotel.reports') }}" style="background:var(--accent,#c9a227);color:#fff;padding:10px 20px;border-radius:10px;text-decoration:none;font-weight:800;font-size:13px;">← Back to Reports</a>
    </div>

    @if($history->isEmpty())
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e6ecf4);border-radius:16px;padding:40px;text-align:center;">
            <div style="font-size:48px;margin-bottom:12px;">📋</div>
            <h3 style="font-size:16px;font-weight:800;margin:0 0 6px;">{{ __('messages.no_price_changes_yet') }}</h3>
            <p style="font-size:13px;color:var(--muted,#6b7a90);margin:0;">Changes will appear here after applying AI pricing suggestions.</p>
        </div>
    @else
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e6ecf4);border-radius:16px;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--bg,#f5f7fb);">
                        <th style="text-align:left;padding:14px 18px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7a90);font-weight:800;">Room</th>
                        <th style="text-align:left;padding:14px 18px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7a90);font-weight:800;">{{ __('messages.change') }}</th>
                        <th style="text-align:left;padding:14px 18px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7a90);font-weight:800;">{{ __('messages.reason') }}</th>
                        <th style="text-align:left;padding:14px 18px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7a90);font-weight:800;">{{ __('messages.changed_by') }}</th>
                        <th style="text-align:left;padding:14px 18px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7a90);font-weight:800;">{{ __('messages.date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                        @php
                            $old = $h->old_price !== null ? (float) $h->old_price : null;
                            $new = (float) $h->new_price;
                            $delta = $old !== null && $old > 0 ? round((($new - $old) / $old) * 100, 1) : null;
                            $deltaColor = $delta === null ? '#6b7a90' : ($delta > 0 ? '#10b981' : ($delta < 0 ? '#ef4444' : '#6b7a90'));
                        @endphp
                        <tr style="border-top:1px solid var(--line,#e6ecf4);">
                            <td style="padding:14px 18px;">
                                <strong>Room {{ $h->room_number }}</strong>
                                <div style="font-size:11px;color:var(--muted,#6b7a90);">{{ $h->room_type }}</div>
                            </td>
                            <td style="padding:14px 18px;">
                                @if($old !== null)
                                    <span style="color:var(--muted,#6b7a90);">Rs {{ number_format($old) }}</span>
                                    <span style="margin:0 6px;">→</span>
                                @endif
                                <strong style="color:var(--text,#0e1726);">Rs {{ number_format($new) }}</strong>
                                @if($delta !== null)
                                    <div style="font-size:11px;color:{{ $deltaColor }};font-weight:700;">{{ $delta > 0 ? '+' : '' }}{{ $delta }}%</div>
                                @endif
                            </td>
                            <td style="padding:14px 18px;font-size:13px;">{{ $h->reason ?? '—' }}</td>
                            <td style="padding:14px 18px;font-size:13px;">{{ $h->changed_by_name ?? 'System' }}</td>
                            <td style="padding:14px 18px;font-size:13px;color:var(--muted,#6b7a90);">{{ \Carbon\Carbon::parse($h->created_at)->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection