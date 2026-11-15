@extends('layouts.traveler-v2')
@section('title', 'Booking ' . ($booking->booking_ref ?? ''))
@section('content')
<div style="max-width:800px;margin:0 auto;padding:20px">
    <a href="{{ route('traveler.transport.bookings.index') }}" style="color:#0284c7;font-weight:700;font-size:13px;text-decoration:none">← My Bookings</a>

    @if(session('success'))
        <div style="background:#dcfce7;color:#166534;padding:14px 18px;border-radius:12px;margin:16px 0;font-weight:700;border:1px solid #86efac">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:12px;margin:16px 0;font-weight:700;border:1px solid #fca5a5">
            @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
        </div>
    @endif

    <div style="background:linear-gradient(135deg,#0284c7,#0369a1);border-radius:16px;padding:24px;color:#fff;margin-top:16px">
        <div style="font-size:22px;font-weight:800">{{ $booking->booking_ref }}</div>
        <div style="opacity:.9;margin-top:4px;font-size:13px">{{ $booking->route_name }}</div>
        <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
            <span style="background:rgba(255,255,255,0.2);padding:5px 10px;border-radius:8px;font-size:11px;font-weight:800;text-transform:uppercase">{{ $booking->status }}</span>
            <span style="background:rgba(255,255,255,0.2);padding:5px 10px;border-radius:8px;font-size:11px;font-weight:800;text-transform:uppercase">Payment: {{ $booking->payment_status ?? 'pending' }}</span>
        </div>
    </div>

    {{-- Trip details --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;margin-top:16px">
        <h3 style="font-size:14px;font-weight:800;margin:0 0 14px">📋 Trip Details</h3>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0"><span style="color:#64748b">Route</span><span style="font-weight:700">{{ $booking->route_name }}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0"><span style="color:#64748b">Date</span><span style="font-weight:700">{{ $booking->travel_date?->format('M d, Y') }}</span></div>
        @if($booking->travel_time)
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0"><span style="color:#64748b">Time</span><span style="font-weight:700">{{ substr($booking->travel_time, 0, 5) }}</span></div>
        @endif
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0"><span style="color:#64748b">Vehicle</span><span style="font-weight:700">{{ $booking->vehicle->vehicle_number ?? 'N/A' }}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0"><span style="color:#64748b">Passengers</span><span style="font-weight:700">{{ $booking->passenger_count }}</span></div>
        @if($booking->pickup_point)
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:6px 0"><span style="color:#64748b">Pickup</span><span style="font-weight:700">{{ $booking->pickup_point }}</span></div>
        @endif
    </div>

    {{-- Amount --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;margin-top:16px">
        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800">
            <span>Total</span>
            <span style="color:#0284c7">Rs {{ number_format((float)($booking->total_amount ?? $booking->amount ?? 0), 2) }}</span>
        </div>
    </div>

    {{-- Payment Section --}}
    @if(($booking->payment_model ?? 'pay_on_arrival') === 'pay_on_arrival')
        <div style="background:#f0f9ff;border:1px solid #bae6fd;padding:16px;border-radius:14px;margin-top:16px;color:#0369a1;font-size:13px;font-weight:600">
            💵 <strong>Pay at Departure</strong> — Bring exact cash amount. You'll pay the driver/operator at boarding.
        </div>
    @elseif(($booking->payment_status ?? 'pending') === 'pending')
        {{-- Advance payment — show methods + upload form --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;margin-top:16px">
            <h3 style="font-size:14px;font-weight:800;margin:0 0 14px">💳 Complete Payment</h3>
            <p style="font-size:12px;color:#64748b;margin:0 0 14px">Select a payment method, send the amount, then upload the proof below.</p>

            @forelse($paymentMethods as $m)
                <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:10px;{{ $m->is_primary ? 'border-color:#fbbf24;background:#fffbeb' : '' }}">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap">
                        <div>
                            <div style="font-weight:800;font-size:14px">
                                {{ $m->label ?: ucfirst($m->type) }}
                                @if($m->is_primary)<span style="font-size:10px;background:#fef4e2;color:#b45309;padding:2px 6px;border-radius:4px;margin-left:6px">⭐ Primary</span>@endif
                            </div>
                            @if($m->identifier)<div style="font-size:12px;color:#0f172a;margin-top:4px"><strong>ID:</strong> {{ $m->identifier }}</div>@endif
                            @if($m->account_name)<div style="font-size:12px;color:#0f172a"><strong>Name:</strong> {{ $m->account_name }}</div>@endif
                            @if($m->account_number)<div style="font-size:12px;color:#0f172a"><strong>Account:</strong> {{ $m->account_number }}</div>@endif
                            @if($m->bank_name)<div style="font-size:12px;color:#0f172a"><strong>Bank:</strong> {{ $m->bank_name }}</div>@endif
                            @if($m->instructions)<div style="font-size:12px;color:#64748b;margin-top:6px">{{ $m->instructions }}</div>@endif
                        </div>
                        @if($m->qr_image_path)
                            <a href="{{ \Storage::url($m->qr_image_path) }}" target="_blank">
                                <img src="{{ \Storage::url($m->qr_image_path) }}" alt="QR" style="width:80px;height:80px;border-radius:6px;border:1px solid #e2e8f0">
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div style="color:#b45309;font-size:13px;padding:12px;background:#fef4e2;border-radius:10px">⚠️ No payment methods available. Please contact the provider.</div>
            @endforelse

            @if($paymentMethods->isNotEmpty())
            <form method="POST" action="{{ route('traveler.transport.bookings.payment-proof', $booking->id) }}" enctype="multipart/form-data" style="margin-top:16px;border-top:1px solid #e2e8f0;padding-top:16px">
                @csrf
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:4px">Payment Method Used</label>
                    <select name="payment_method_id" required style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px">
                        @foreach($paymentMethods as $m)
                            <option value="{{ $m->id }}">{{ $m->label ?: ucfirst($m->type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:4px">Transaction Reference / ID</label>
                    <input type="text" name="payment_ref" required maxlength="100" placeholder="e.g. TXN-12345" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px">
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:4px">Payment Screenshot</label>
                    <input type="file" name="payment_proof" accept="image/*" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px">
                    <p style="font-size:11px;color:#64748b;margin:4px 0 0">Max 5MB · JPG, PNG, WEBP</p>
                </div>
                <button type="submit" style="width:100%;padding:12px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;border-radius:10px;font-weight:800;font-size:14px;cursor:pointer">
                    Submit Payment Proof
                </button>
            </form>
            @endif
        </div>
    @elseif(($booking->payment_status ?? 'pending') === 'submitted')
        <div style="background:#f0f9ff;border:1px solid #bae6fd;padding:16px;border-radius:14px;margin-top:16px;color:#0369a1;font-size:13px;font-weight:600">
            ⏳ <strong>Payment Proof Submitted</strong> — The provider will verify your payment shortly.
        </div>
    @elseif(($booking->payment_status ?? 'pending') === 'paid')
        <div style="background:#dcfce7;border:1px solid #86efac;padding:16px;border-radius:14px;margin-top:16px;color:#166534;font-size:13px;font-weight:600">
            ✅ <strong>Payment Verified</strong> — Thank you! Your booking is confirmed.
            <div style="margin-top:12px">
                <a href="{{ route('traveler.transport.bookings.receipt', $booking->id) }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;background:#059669;color:#fff;border-radius:8px;font-weight:700;font-size:12px;text-decoration:none">
                    📄 Download Receipt (PDF)
                </a>
            </div>
        </div>
    @endif
</div>
@endsection