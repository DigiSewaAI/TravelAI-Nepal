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
    $isLegacy = is_null($booking->trip_id);
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
            @if($isLegacy)
            <span style="display:inline-block;margin-top:8px;padding:4px 10px;border-radius:6px;background:rgba(255,255,255,0.2);font-size:11px;font-weight:800;text-transform:uppercase">Legacy Booking</span>
            @endif
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ $booking->status }}</span>
            <span style="padding:7px 14px;border-radius:10px;background:{{ $pBg }};color:{{ $pFg }};font-size:12px;font-weight:800;text-transform:uppercase">Payment: {{ $booking->payment_status ?? 'pending' }}</span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">Passenger</h3>
            <div style="font-size:14px;font-weight:700">{{ $booking->passenger_name ?? 'N/A' }}</div>
            @if($booking->passenger_email)<div style="font-size:13px;color:var(--muted,#6b7f96);margin-top:4px">{{ $booking->passenger_email }}</div>@endif
            @if(!$isLegacy)
            <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--line,#e4ecf6)">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px"><span style="color:var(--muted,#6b7f96)">Passengers</span><span style="font-weight:700">{{ $booking->passenger_count }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:var(--muted,#6b7f96)">Booking Type</span><span style="font-weight:700">{{ $booking->booking_type === 'full_vehicle' ? 'Full Vehicle' : 'Per Seat' }}</span></div>
            </div>
            @endif
        </div>

        <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">Trip Details</h3>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">Route</span><span style="font-weight:700">{{ $booking->route_name ?? 'N/A' }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">Travel Date</span><span style="font-weight:700">{{ $booking->travel_date ? \Carbon\Carbon::parse($booking->travel_date)->format('M d, Y') : 'N/A' }}</span></div>
            @if($booking->travel_time)
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">Time</span><span style="font-weight:700">{{ substr($booking->travel_time, 0, 5) }}</span></div>
            @endif
            @if($booking->vehicle)
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">Vehicle</span><span style="font-weight:700">{{ $booking->vehicle->vehicle_number ?? 'N/A' }}</span></div>
            @endif
            @if($booking->pickup_point)
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">Pickup</span><span style="font-weight:700">{{ $booking->pickup_point }}</span></div>
            @endif
        </div>
    </div>

    {{-- PRICING --}}
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">Pricing</h3>
        @if(!$isLegacy && $booking->price_per_unit && $booking->total_amount)
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">Price per unit</span><span style="font-weight:700">Rs {{ number_format($booking->price_per_unit, 2) }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#6b7f96)">{{ $booking->booking_type === 'full_vehicle' ? 'Vehicle (1)' : 'Passengers' }}</span><span style="font-weight:700">{{ $booking->booking_type === 'full_vehicle' ? '1' : $booking->passenger_count }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;margin-top:10px;padding-top:10px;border-top:1px solid var(--line,#e4ecf6)"><span>Total</span><span style="color:var(--accent,#0ea5e9)">Rs {{ number_format($booking->total_amount, 2) }}</span></div>
        @else
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800"><span>{{ __('messages.amount') }}</span><span style="color:var(--accent,#0ea5e9)">
                @if((float)$booking->amount > 0) Rs {{ number_format($booking->amount, 2) }} @else {{ __('messages.price_on_request') ?? 'Price on request' }} @endif
            </span></div>
        @endif
    </div>

    {{-- PAYMENT --}}
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">💳 Payment</h3>

        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#6b7f96)">Model</span>
            <span style="font-weight:700;text-transform:capitalize">{{ str_replace('_', ' ', $booking->payment_model ?? 'pay_on_arrival') }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#6b7f96)">Status</span>
            <span style="font-weight:700;text-transform:capitalize">{{ $booking->payment_status ?? 'pending' }}</span>
        </div>
        @if($booking->payment_method)
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#6b7f96)">Method</span>
            <span style="font-weight:700;text-transform:capitalize">{{ $booking->payment_method }}</span>
        </div>
        @endif
        @if($booking->payment_ref)
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#6b7f96)">Transaction Ref</span>
            <span style="font-weight:700;font-family:monospace">{{ $booking->payment_ref }}</span>
        </div>
        @endif
        @if($booking->payment_submitted_at)
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#6b7f96)">Submitted At</span>
            <span style="font-weight:700">{{ $booking->payment_submitted_at->format('M d, Y H:i') }}</span>
        </div>
        @endif
        @if($booking->paid_at)
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:var(--muted,#6b7f96)">Verified At</span>
            <span style="font-weight:700">{{ $booking->paid_at->format('M d, Y H:i') }}</span>
        </div>
        @endif

        {{-- Proof screenshot --}}
        @if($booking->payment_proof)
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--line,#e4ecf6)">
            <div style="font-size:11px;font-weight:800;color:var(--muted,#6b7f96);text-transform:uppercase;margin-bottom:8px">📸 Payment Proof Screenshot</div>
            <a href="{{ \Storage::url($booking->payment_proof) }}" target="_blank" style="display:inline-block">
                <img src="{{ \Storage::url($booking->payment_proof) }}" alt="Payment Proof" style="max-width:100%;max-height:400px;border:2px solid #e2e8f0;border-radius:12px;background:#fff;padding:4px">
            </a>
            <div style="margin-top:6px">
                <a href="{{ \Storage::url($booking->payment_proof) }}" target="_blank" style="font-size:12px;color:#0284c7;font-weight:700;text-decoration:none">🔍 Open full size</a>
            </div>
        </div>
        @endif

        {{-- Actions --}}
        @if(($booking->payment_status ?? 'pending') === 'submitted')
        <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap">
            <form action="{{ route('provider.transport.bookings.verify-payment-proof', $booking) }}" method="POST" onsubmit="return confirm('Verify this payment? Booking will be auto-confirmed and traveler notified.');" style="flex:1;min-width:160px;margin:0">
                @csrf @method('PATCH')
                <button type="submit" style="width:100%;padding:11px;background:#059669;color:#fff;border:none;border-radius:10px;font-weight:800;font-size:13px;cursor:pointer">
                    ✅ Verify Payment
                </button>
            </form>
            <button type="button" onclick="document.getElementById('rejectForm').style.display='block'" style="flex:1;min-width:160px;padding:11px;background:#dc2626;color:#fff;border:none;border-radius:10px;font-weight:800;font-size:13px;cursor:pointer">
                ❌ Reject Proof
            </button>
        </div>

        <form id="rejectForm" action="{{ route('provider.transport.bookings.reject-payment-proof', $booking) }}" method="POST" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid var(--line,#e4ecf6)">
            @csrf @method('PATCH')
            <div style="margin-bottom:10px">
                <label style="display:block;font-size:11px;font-weight:800;color:var(--muted,#6b7f96);text-transform:uppercase;margin-bottom:4px">Reason for Rejection *</label>
                <textarea name="rejection_reason" required maxlength="500" rows="3" placeholder="e.g. Transaction ID not found in our system, Amount mismatch, Screenshot unclear" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;font-family:inherit;font-size:13px"></textarea>
            </div>
            <div style="display:flex;gap:8px">
                <button type="button" onclick="document.getElementById('rejectForm').style.display='none'" style="flex:1;padding:9px;background:#f1f5f9;color:#0f172a;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:12px">Cancel</button>
                <button type="submit" style="flex:1;padding:9px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-weight:800;cursor:pointer;font-size:12px">Confirm Reject</button>
            </div>
        </form>
        @elseif(($booking->payment_status ?? 'pending') === 'pending' && $booking->status !== 'cancelled' && ($booking->payment_model ?? 'pay_on_arrival') === 'pay_on_arrival')
        <div style="margin-top:14px;padding:12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;font-size:12px;color:#0369a1;font-weight:600">
            💵 Pay on Arrival — Collect payment at departure. Use "Confirm Booking" to accept.
        </div>
        @endif
    </div>

    {{-- STATUS ACTIONS --}}
    @if(in_array($booking->status, ['pending', 'confirmed']))
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">Update Status</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            @if($booking->status === 'pending')
            <button type="button" onclick="updateStatus('confirmed')" style="padding:10px 20px;background:#059669;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer">✅ Confirm Booking</button>
            @endif
            <button type="button" onclick="updateStatus('cancelled')" style="padding:10px 20px;background:#dc2626;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer">✕ Cancel Booking</button>
        </div>
    </div>
    @elseif($booking->status === 'cancelled')
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#0c1e33)">Restore</h3>
        <p style="font-size:13px;color:var(--muted,#6b7f96);margin:0 0 14px">This booking was cancelled. You can restore it back to pending.</p>
        <button type="button" onclick="updateStatus('pending')" style="padding:10px 20px;background:#0284c7;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer">↻ Restore Booking</button>
    </div>
    @endif

    {{-- DELETE --}}
    <div style="background:var(--card,#fff);border:1px solid #fca5a5;border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:#b91c1c">Danger Zone</h3>
        <form action="{{ route('provider.transport.bookings.destroy', $booking) }}" method="POST" onsubmit="return confirm('Permanently delete this booking? This cannot be undone.');" style="margin:0">
            @csrf @method('DELETE')
            <button type="submit" style="padding:10px 20px;background:#b91c1c;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer">🗑 Delete Permanently</button>
        </form>
    </div>
</div>

<script>
function updateStatus(newStatus) {
    if (!confirm('Are you sure you want to ' + newStatus + ' this booking?')) return;
    fetch('{{ route("provider.transport.bookings.status", $booking->id) }}', {
        method: 'PATCH',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
        body: JSON.stringify({ status: newStatus })
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert('Failed'); }).catch(() => alert('Network error'));
}
</script>
@endsection