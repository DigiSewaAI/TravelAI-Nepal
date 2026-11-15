@extends('layouts.traveler-v2')
@section('title', 'My Transport Bookings')
@section('content')
<div style="max-width:900px;margin:0 auto;padding:20px">
    <h1 style="font-size:22px;font-weight:800;margin-bottom:16px">🚌 My Transport Bookings</h1>

    @if($bookings->isEmpty())
        <div style="background:#fff;border:2px dashed #cbd5e1;border-radius:16px;padding:48px 20px;text-align:center">
            <div style="font-size:56px;margin-bottom:12px">🚌</div>
            <h3 style="font-size:16px;margin:0 0 8px">No bookings yet</h3>
            <p style="color:#64748b;font-size:13px;margin:0">Your transport bookings will appear here.</p>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:12px">
            @foreach($bookings as $b)
                @php
                    $sc = ['pending'=>['#fef4e2','#b45309'],'confirmed'=>['#e6f8f1','#059669'],'cancelled'=>['#fdecec','#dc2626'],'completed'=>['#e6f8f1','#059669']];
                    [$bg, $fg] = $sc[$b->status] ?? ['#f3f4f6','#6b7280'];
                @endphp
                <a href="{{ route('traveler.transport.bookings.show', $b->id) }}"
                   style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;text-decoration:none;color:inherit;display:block;transition:.2s">
                    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
                        <div>
                            <div style="font-weight:800;color:#0284c7;font-family:monospace;font-size:13px">{{ $b->booking_ref }}</div>
                            <div style="font-weight:800;margin-top:6px;font-size:15px">{{ $b->route_name }}</div>
                            <div style="color:#64748b;font-size:13px;margin-top:4px">
                                📅 {{ $b->travel_date?->format('M d, Y') }}
                                @if($b->travel_time) at {{ substr($b->travel_time, 0, 5) }} @endif
                            </div>
                        </div>
                        <div style="text-align:right">
                            <span style="padding:5px 10px;border-radius:8px;background:{{ $bg }};color:{{ $fg }};font-size:11px;font-weight:800;text-transform:uppercase">{{ $b->status }}</span>
                            <div style="font-size:16px;font-weight:800;margin-top:8px">Rs {{ number_format((float)($b->total_amount ?? $b->amount ?? 0), 0) }}</div>
                            @if(($b->payment_status ?? 'pending') === 'pending' && ($b->payment_model ?? 'pay_on_arrival') === 'advance')
                                <div style="font-size:11px;color:#b45309;font-weight:800;margin-top:4px">💳 Payment required</div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div style="margin-top:20px">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection