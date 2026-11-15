@extends('layouts.provider-transport')
@section('title', 'Cancellation Requests')
@section('page-title', '🚫 Cancellation Requests')
@section('page-subtitle', 'Review and respond to traveler cancellation requests')

@section('content')

@if(session('success'))
<div style="background:var(--green-soft);color:#047857;padding:14px 18px;border-radius:12px;margin-bottom:18px;font-size:13px;font-weight:700;border:1px solid #86efac">✅ {{ session('success') }}</div>
@endif
@if($errors->any())
<div style="background:var(--red-soft);color:#b91c1c;padding:14px 18px;border-radius:12px;margin-bottom:18px;font-size:13px;font-weight:700;border:1px solid #fca5a5">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif

@if($requests->isEmpty())
<div style="text-align:center;padding:60px 20px;background:var(--card);border:2px dashed var(--line);border-radius:16px">
    <div style="font-size:56px;margin-bottom:12px">✅</div>
    <h3 style="margin:0 0 6px;color:var(--text)">No cancellation requests</h3>
    <p style="color:var(--muted);font-size:13px;margin:0">All clear!</p>
</div>
@else
<div style="display:flex;flex-direction:column;gap:14px">
    @foreach($requests as $req)
        @php
            $booking = $req->booking;
            $sc = ['pending'=>['#fef4e2','#b45309'], 'approved'=>['#e6f8f1','#059669'], 'rejected'=>['#fdecec','#dc2626']];
            [$bg, $fg] = $sc[$req->status] ?? ['#f3f4f6','#6b7280'];
        @endphp
        <div style="background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px">
                <div>
                    <div style="font-size:13px;font-weight:800;color:var(--accent);font-family:monospace">{{ $booking->booking_ref }}</div>
                    <div style="font-size:14px;font-weight:800;margin-top:4px">{{ $booking->route_name }}</div>
                    <div style="font-size:12px;color:var(--muted);margin-top:2px">
                        {{ $booking->travel_date?->format('M d, Y') }}
                        @if($booking->travel_time) · {{ substr($booking->travel_time, 0, 5) }} @endif
                    </div>
                    <div style="font-size:12px;color:var(--muted);margin-top:6px">
                        👤 {{ $booking->passenger_name }} · {{ $booking->passenger_count }} pax
                    </div>
                </div>
                <div style="text-align:right">
                    <span style="padding:5px 10px;border-radius:8px;background:{{ $bg }};color:{{ $fg }};font-size:11px;font-weight:800;text-transform:uppercase">{{ $req->status }}</span>
                    @if($req->refund_status === 'pending')
                        <div style="margin-top:6px;font-size:11px;color:#b45309;font-weight:800">💰 Refund pending</div>
                    @elseif($req->refund_status === 'refunded')
                        <div style="margin-top:6px;font-size:11px;color:#059669;font-weight:800">✅ Refunded</div>
                    @endif
                </div>
            </div>

            <div style="background:var(--bg);padding:12px;border-radius:10px;font-size:13px;margin-bottom:12px">
                <strong style="color:var(--text)">Reason:</strong> {{ $req->reason }}
            </div>

            @if((float) $req->estimated_refund > 0)
            <div style="background:#f0f9ff;border:1px solid #bae6fd;padding:10px 14px;border-radius:10px;font-size:12px;color:#0369a1;margin-bottom:12px">
                💰 Estimated Refund: <strong>Rs {{ number_format((float) $req->estimated_refund, 2) }}</strong>
            </div>
            @endif

            @if($req->status === 'pending')
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <form action="{{ route('provider.transport.cancellations.approve', $req) }}" method="POST" onsubmit="return confirm('Approve cancellation? Seat inventory will be restored.');" style="flex:1;min-width:140px;margin:0">
                    @csrf @method('PATCH')
                    <button type="submit" style="width:100%;padding:10px;background:#059669;color:#fff;border:none;border-radius:9px;font-weight:800;font-size:12px;cursor:pointer">✅ Approve</button>
                </form>
                <button type="button" onclick="document.getElementById('rejectForm{{ $req->id }}').style.display='block'" style="flex:1;min-width:140px;padding:10px;background:#dc2626;color:#fff;border:none;border-radius:9px;font-weight:800;font-size:12px;cursor:pointer">❌ Reject</button>
            </div>
            <form id="rejectForm{{ $req->id }}" action="{{ route('provider.transport.cancellations.reject', $req) }}" method="POST" style="display:none;margin-top:10px">
                @csrf @method('PATCH')
                <textarea name="provider_response" required maxlength="500" rows="2" placeholder="Reason for rejection..." style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;font-family:inherit;margin-bottom:8px"></textarea>
                <button type="submit" style="padding:8px 16px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-weight:800;font-size:12px;cursor:pointer">Confirm Reject</button>
            </form>
            @elseif($req->refund_status === 'pending')
            <form action="{{ route('provider.transport.cancellations.refund', $req) }}" method="POST" onsubmit="return confirm('Mark this refund as completed?');" style="margin:0">
                @csrf @method('PATCH')
                <button type="submit" style="padding:10px 20px;background:#0284c7;color:#fff;border:none;border-radius:9px;font-weight:800;font-size:12px;cursor:pointer">💰 Mark as Refunded</button>
            </form>
            @endif
        </div>
    @endforeach
</div>
<div style="margin-top:16px">{{ $requests->links() }}</div>
@endif

@endsection