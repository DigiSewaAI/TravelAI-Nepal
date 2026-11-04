@extends($provider_layout ?? 'layouts.provider-transport')
@section('title', ($booking->booking_ref ?? '#' . $booking->id))
@section('page-title', $booking->booking_ref ?? '#' . $booking->id)
@section('page-subtitle', $booking->created_at->format('M d, Y H:i'))
@section('content')
@php
    $sc = ['pending'=>['#fef4e2','#b45309'],'confirmed'=>['#e6f8f1','#059669'],'completed'=>['#e6f8f1','#059669'],'cancelled'=>['#fdecec','#dc2626'],'in_progress'=>['#e0f2fe','#0284c7']];
    [$sBg,$sFg] = $sc[$booking->status] ?? ['#f3f4f6','#6b7280'];
@endphp
<div style="display:flex;flex-direction:column;gap:18px">
    <a href="{{ route('provider.transport.bookings.index') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--accent,#0ea5e9);font-weight:700;text-decoration:none;font-size:13px;width:max-content">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        All Bookings
    </a>
    <div style="background:linear-gradient(135deg,var(--accent,#0ea5e9) 0%,var(--accent-d,#0284c7) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <div>
            <div style="font-size:22px;font-weight:800">{{ $booking->booking_ref ?? '#' . $booking->id }}</div>
            <div style="font-size:13px;opacity:.88;margin-top:4px">{{ $booking->created_at->format('M d, Y H:i') }}</div>
        </div>
        <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ __('messages.st_' . $booking->status) }}</span>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">{{ __('messages.passenger') }}</h3>
            <div style="font-size:14px;font-weight:700">{{ $booking->passenger_name ?? 'N/A' }}</div>
            @if($booking->passenger_email)<div style="font-size:13px;color:var(--muted,#6b7f96);margin-top:4px">{{ $booking->passenger_email }}</div>@endif
        </div>
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">{{ __('messages.trip_details') }}</h3>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">{{ __('messages.route') }}</span><span style="font-weight:700">{{ $booking->route_name ?? 'N/A' }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">{{ __('messages.travel_date') }}</span><span style="font-weight:700">{{ $booking->travel_date ? \Carbon\Carbon::parse($booking->travel_date)->format('M d, Y') : 'N/A' }}</span></div>
            @if($booking->vehicle)
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">{{ __('messages.vehicle') }}</span><span style="font-weight:700">{{ $booking->vehicle->vehicle_number ?? $booking->vehicle->name ?? 'N/A' }}</span></div>
            @endif
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;margin-top:10px;padding-top:10px;border-top:1px solid var(--line,#e4ecf6)"><span>{{ __('messages.amount') }}</span><span style="color:var(--accent,#0ea5e9)">Rs {{ number_format($booking->amount, 2) }}</span></div>
        </div>
    </div>
</div>
@endsection