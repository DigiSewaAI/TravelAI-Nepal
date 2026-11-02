@extends($provider_layout ?? 'layouts.provider-individual')
@section('title', $booking->title ?? 'Booking #' . $booking->id)
@section('page-title', $booking->title ?? 'Booking #' . $booking->id)
@section('page-subtitle', $booking->created_at->format('M d, Y H:i'))
@section('content')
@php
    $sc = ['pending'=>['#fef4e2','#b45309'],'confirmed'=>['#e7f8f1','#059669'],'completed'=>['#e7f8f1','#059669'],'cancelled'=>['#fdecec','#dc2626']];
    [$sBg,$sFg] = $sc[$booking->status] ?? ['#f3f4f6','#6b7280'];
@endphp
<div style="display:flex;flex-direction:column;gap:18px">
    <a href="{{ route('provider.individual.bookings.list') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--accent,#10b981);font-weight:700;text-decoration:none;font-size:13px;width:max-content">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        All Bookings
    </a>
    <div style="background:linear-gradient(135deg,var(--accent,#10b981) 0%,var(--accent-d,#059669) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <div>
            <div style="font-size:22px;font-weight:800">{{ $booking->title ?? 'Booking #' . $booking->id }}</div>
            <div style="font-size:13px;opacity:.88;margin-top:4px">{{ ucfirst($booking->service_type) }} · {{ $booking->created_at->format('M d, Y H:i') }}</div>
        </div>
        <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ $booking->status }}</span>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e2ebe5);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0d1f17)">Client</h3>
            <div style="font-size:14px;font-weight:700">{{ $booking->client_name }}</div>
        </div>
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e2ebe5);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0d1f17)">Details</h3>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7d73)">Service Type</span><span style="font-weight:700">{{ ucfirst($booking->service_type) }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7d73)">Start Date</span><span style="font-weight:700">{{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') : 'N/A' }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7d73)">Duration</span><span style="font-weight:700">{{ $booking->duration }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7d73)">Pax</span><span style="font-weight:700">{{ $booking->pax }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;margin-top:10px;padding-top:10px;border-top:1px solid var(--line,#e2ebe5)"><span>Amount</span><span style="color:var(--accent,#10b981)">NPR {{ number_format($booking->amount, 2) }}</span></div>
        </div>
    </div>
</div>
@endsection