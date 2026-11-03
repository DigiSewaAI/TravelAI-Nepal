@extends($provider_layout ?? 'layouts.provider-hotel')

@section('title', 'Booking #' . $booking->id)
@section('page-title', 'Booking #' . $booking->id)
@section('page-subtitle', $booking->created_at->format('M d, Y H:i'))

@section('content')
@php
    $statusColors = [
        'confirmed' => ['#e7f8f1', '#059669'],
        'pending'   => ['#fef4e2', '#b45309'],
        'checked_in'=> ['#e9f1fe', '#2563eb'],
        'checked_out'=>['#f3f4f6', '#6b7280'],
        'cancelled' => ['#fdecec', '#dc2626'],
    ];
    [$sBg, $sFg] = $statusColors[$booking->status] ?? ['#f3f4f6', '#6b7280'];
    $pColors = ['paid' => ['#e7f8f1', '#059669'], 'pending' => ['#fef4e2', '#b45309'], 'refunded' => ['#fdecec', '#dc2626']];
    [$pBg, $pFg] = $pColors[$booking->payment_status] ?? ['#f3f4f6', '#6b7280'];
@endphp

<div style="display:flex;flex-direction:column;gap:18px">

    <a href="{{ route('provider.hotel.bookings') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--accent,#c9a227);font-weight:700;text-decoration:none;font-size:13px;width:max-content">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        {{ __('messages.all_bookings') ?? 'All Bookings' }}
    </a>

    {{-- Hero --}}
    <div style="background:linear-gradient(135deg,var(--accent,#c9a227) 0%,var(--accent-d,#a8861c) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <div>
            <div style="font-size:22px;font-weight:800;letter-spacing:-.5px">Booking #{{ $booking->id }}</div>
            <div style="font-size:13px;opacity:.88;margin-top:4px">{{ $booking->created_at->format('M d, Y H:i') }}</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ $booking->status }}</span>
            <span style="padding:7px 14px;border-radius:10px;background:{{ $pBg }};color:{{ $pFg }};font-size:12px;font-weight:800;text-transform:uppercase">Payment: {{ $booking->payment_status }}</span>
        </div>
    </div>

    {{-- Guest + Room --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
            <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#111827)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {{ __('messages.guest') ?? 'Guest' }}
            </h3>
            <div style="font-size:14px;font-weight:700;color:var(--text,#111827)">{{ $booking->guest_name }}</div>
            <div style="font-size:13px;color:var(--muted,#6b7280);margin-top:4px">{{ $booking->guest_email }}</div>
        </div>
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
            <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#111827)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16h20V4"/><path d="M2 10h20"/></svg>
                {{ __('messages.room') ?? 'Room' }}
            </h3>
            <div style="font-size:14px;font-weight:700;color:var(--text,#111827)">
                Room {{ $booking->room->room_number ?? 'N/A' }}
            </div>
            <div style="font-size:13px;color:var(--muted,#6b7280);margin-top:4px">
                {{ ucfirst($booking->room->room_type ?? '') }}
                @if($booking->room?->floor) · Floor {{ $booking->room->floor }} @endif
            </div>
        </div>
    </div>

    {{-- Dates & Amount --}}
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:16px;color:var(--text,#111827)">Booking Details</h3>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px">
            <div>
                <div style="font-size:11px;color:var(--muted,#6b7280);text-transform:uppercase;font-weight:700;letter-spacing:.5px">Check-in</div>
                <div style="font-size:14px;font-weight:700;margin-top:4px">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--muted,#6b7280);text-transform:uppercase;font-weight:700;letter-spacing:.5px">Check-out</div>
                <div style="font-size:14px;font-weight:700;margin-top:4px">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--muted,#6b7280);text-transform:uppercase;font-weight:700;letter-spacing:.5px">Nights</div>
                <div style="font-size:14px;font-weight:700;margin-top:4px">{{ $booking->nights }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--muted,#6b7280);text-transform:uppercase;font-weight:700;letter-spacing:.5px">Amount</div>
                <div style="font-size:18px;font-weight:800;margin-top:4px;color:var(--accent,#c9a227)">NPR {{ number_format($booking->amount, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#111827)">Actions</h3>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            @if($booking->status === 'confirmed')
                <form method="POST" action="{{ route('provider.hotel.bookings.action', $booking) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="check_in">
                    <button type="submit" style="padding:10px 18px;border-radius:10px;background:#2563eb;color:#fff;font-size:13px;font-weight:800;border:0;cursor:pointer">Check In</button>
                </form>
            @endif
            @if($booking->status === 'checked_in')
                <form method="POST" action="{{ route('provider.hotel.bookings.action', $booking) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="check_out">
                    <button type="submit" style="padding:10px 18px;border-radius:10px;background:#059669;color:#fff;font-size:13px;font-weight:800;border:0;cursor:pointer">Check Out</button>
                </form>
            @endif
            <form method="POST" action="{{ route('provider.hotel.bookings.destroy', $booking) }}" onsubmit="return confirm('Delete this booking?')">
                @csrf @method('DELETE')
                <button type="submit" style="padding:10px 18px;border-radius:10px;background:#fdecec;color:#dc2626;font-size:13px;font-weight:800;border:1px solid #fecaca;cursor:pointer">Delete Booking</button>
            </form>
        </div>
    </div>

</div>

    {{-- ═══ FOLIO CHARGES ═══ --}}
    <div id="folio-charges" style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
            <h3 style="font-size:14px;font-weight:800;color:var(--text,#111827);margin:0;display:flex;align-items:center;gap:8px">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/></svg>
                Folio Charges
            </h3>
            <button type="button" onclick="document.getElementById('folioForm').style.display = (document.getElementById('folioForm').style.display === 'none' ? 'block' : 'none')"
                style="padding:8px 16px;background:var(--accent,#c9a227);color:#fff;border:0;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer">
                + Add Charge
            </button>
        </div>

        @if(session('success'))
            <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:10px 14px;border-radius:10px;margin-bottom:12px;font-size:13px">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:10px;margin-bottom:12px;font-size:13px">{{ $errors->first() }}</div>
        @endif

        {{-- Add form (hidden by default) --}}
        <form id="folioForm" method="POST" action="{{ route('provider.hotel.bookings.folio.add', $booking) }}"
              style="display:none;background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:16px">
            @csrf
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:10px;margin-bottom:10px">
                <input type="text" name="description" placeholder="Description (e.g. Breakfast)" required maxlength="255"
                       style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px">
                <select name="category" required style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px">
                    <option value="room">Room</option>
                    <option value="food">Food</option>
                    <option value="laundry">Laundry</option>
                    <option value="extra">Extra</option>
                    <option value="other" selected>Other</option>
                </select>
                <input type="number" name="amount" placeholder="Amount" required min="0" step="0.01"
                       style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px">
                <input type="number" name="quantity" value="1" min="1" max="999"
                       style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px">
            </div>
            <div style="display:flex;gap:10px">
                <button type="submit" style="padding:8px 18px;background:#059669;color:#fff;border:0;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer">Save Charge</button>
            </div>
        </form>

        @if($booking->folioCharges->count() > 0)
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                        <th style="text-align:left;padding:10px 12px;font-weight:700;color:#374151">Description</th>
                        <th style="text-align:left;padding:10px 12px;font-weight:700;color:#374151">Category</th>
                        <th style="text-align:right;padding:10px 12px;font-weight:700;color:#374151">Amount</th>
                        <th style="text-align:right;padding:10px 12px;font-weight:700;color:#374151">Qty</th>
                        <th style="text-align:right;padding:10px 12px;font-weight:700;color:#374151">Total</th>
                        <th style="text-align:right;padding:10px 12px;font-weight:700;color:#374151"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->folioCharges as $fc)
                    <tr style="border-bottom:1px solid #f3f4f6">
                        <td style="padding:10px 12px;color:#111827;font-weight:600">{{ $fc->description }}</td>
                        <td style="padding:10px 12px;color:#6b7280;text-transform:capitalize">{{ $fc->category }}</td>
                        <td style="padding:10px 12px;color:#374151;text-align:right">Rs {{ number_format($fc->amount, 2) }}</td>
                        <td style="padding:10px 12px;color:#374151;text-align:right">{{ $fc->quantity }}</td>
                        <td style="padding:10px 12px;color:#111827;font-weight:700;text-align:right">Rs {{ number_format($fc->total, 2) }}</td>
                        <td style="padding:10px 12px;text-align:right">
                            <form method="POST" action="{{ route('provider.hotel.bookings.folio.remove', [$booking, $fc]) }}" style="display:inline" onsubmit="return confirm('Remove this charge?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:0;color:#dc2626;font-size:12px;font-weight:600;cursor:pointer">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f9fafb">
                        <td colspan="4" style="padding:12px;text-align:right;font-weight:700;color:#374151">Folio Total:</td>
                        <td style="padding:12px;text-align:right;font-size:15px;font-weight:800;color:var(--accent,#c9a227)">Rs {{ number_format($booking->folioCharges->sum('total'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @else
            <p style="font-size:13px;color:#9ca3af;margin:0;padding:16px 0;text-align:center">No charges yet. Click "+ Add Charge" to start the folio.</p>
        @endif
    </div>
@endsection