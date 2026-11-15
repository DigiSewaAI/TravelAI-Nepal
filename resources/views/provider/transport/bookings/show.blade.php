@extends($provider_layout ?? 'layouts.provider-transport')
@section('title', ($booking->booking_ref ?? '#' . $booking->id))
@section('page-title', $booking->booking_ref ?? '#' . $booking->id)
@section('page-subtitle', $booking->created_at->format('M d, Y H:i'))
@section('content')
@php
    $sc = ['pending'=>['#fef4e2','#b45309'],'confirmed'=>['#e6f8f1','#059669'],'completed'=>['#e6f8f1','#059669'],'cancelled'=>['#fdecec','#dc2626'],'in_progress'=>['#e0f2fe','#0284c7']];
    [$sBg,$sFg] = $sc[$booking->status] ?? ['#f3f4f6','#6b7280'];
    $pc = ['pending'=>['#fef4e2','#b45309'],'paid'=>['#e6f8f1','#059669'],'refunded'=>['#e0f2fe','#0284c7']];
    [$pBg,$pFg] = $pc[$booking->payment_status ?? 'pending'] ?? ['#f3f4f6','#6b7280'];
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
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ __('messages.st_' . $booking->status) }}</span>
            <span style="padding:7px 14px;border-radius:10px;background:{{ $pBg }};color:{{ $pFg }};font-size:12px;font-weight:800;text-transform:uppercase">Payment: {{ $booking->payment_status ?? 'pending' }}</span>
        </div>
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
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;margin-top:10px;padding-top:10px;border-top:1px solid var(--line,#e4ecf6)"><span>{{ __('messages.amount') }}</span><span style="color:var(--accent,#0ea5e9)">
                @if((float)$booking->amount > 0)
                    Rs {{ number_format($booking->amount, 2) }}
                @else
                    {{ __('messages.price_on_request') ?? 'Price on request' }}
                @endif
            </span></div>
        </div>
    </div>

    {{-- Payment Section --}}
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">Payment</h3>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#6b7f96)">Status</span>
            <span style="font-weight:700;text-transform:capitalize">{{ $booking->payment_status ?? 'pending' }}</span>
        </div>
        @if($booking->payment_method)
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#6b7f96)">Method</span>
            <span style="font-weight:700">{{ $booking->payment_method }}</span>
        </div>
        @endif
        @if($booking->payment_ref)
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#6b7f96)">Reference</span>
            <span style="font-weight:700">{{ $booking->payment_ref }}</span>
        </div>
        @endif
        @if($booking->paid_at)
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#7b8a9c)">Paid At</span>
            <span style="font-weight:700">{{ $booking->paid_at->format('M d, Y H:i') }}</span>
        </div>
        @endif
        @if(($booking->payment_status ?? 'pending') === 'pending')
        <form action="{{ route('provider.transport.bookings.verify-payment', $booking) }}" method="POST" style="margin-top:14px">
            @csrf @method('PATCH')
            <button type="submit" style="padding:10px 20px;background:#059669;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer">
                ✅ Mark as Paid
            </button>
        </form>
        @endif
    </div>

    {{-- Status Update Actions --}}
    @if(in_array($booking->status, ['pending', 'confirmed']))
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">{{ __('messages.update_status') ?? 'Update Status' }}</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            @if($booking->status === 'pending')
            <button type="button" onclick="updateTransportStatus('confirmed')"
                    style="padding:10px 20px;background:#059669;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                ✅ {{ __('messages.confirm_booking') ?? 'Confirm Booking' }}
            </button>
            @endif
            <button type="button" onclick="updateTransportStatus('cancelled')"
                    style="padding:10px 20px;background:#dc2626;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                ✕ {{ __('messages.cancel_booking') ?? 'Cancel Booking' }}
            </button>
        </div>
    </div>

    <script>
    function updateTransportStatus(newStatus) {
        if (!confirm('Are you sure you want to ' + newStatus + ' this booking?')) return;

        fetch('{{ route("provider.transport.bookings.status", $booking->id) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to update status');
            }
        })
        .catch(err => alert('Network error: ' + err.message));
    }
    </script>
    @endif
</div>
@endsection