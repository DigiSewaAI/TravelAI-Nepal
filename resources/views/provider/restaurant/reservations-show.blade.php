@extends('layouts.provider-restaurant')

@section('title', 'Reservation Details')
@section('page-title', 'Reservation #' . $reservation->id)
@section('page-subtitle', 'Booked on ' . $reservation->created_at->format('M d, Y h:i A'))

@section('content')
<div style="max-width: 800px;">
    <a href="{{ route('provider.restaurant.reservations') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--accent);font-weight:700;text-decoration:none;font-size:13px;margin-bottom:20px;">← Back to Reservations</a>

    <div style="background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <h2 style="margin: 0 0 4px; font-size: 22px; font-weight: 800; color: var(--text);">{{ $reservation->customer_name }}</h2>
                <p style="margin: 0; color: var(--muted); font-size: 14px;">{{ $reservation->customer_phone }}</p>
                @if($reservation->customer_email)
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 13px;">{{ $reservation->customer_email }}</p>
                @endif
            </div>
            <div>
                @if($reservation->status === 'confirmed')
                    <span style="background: var(--green-soft); color: var(--green); padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 800;">Confirmed</span>
                @elseif($reservation->status === 'cancelled')
                    <span style="background: var(--red-soft); color: var(--red); padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 800;">Cancelled</span>
                @else
                    <span style="background: var(--amber-soft); color: var(--amber); padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 800;">Pending</span>
                @endif
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid var(--line); padding-top: 20px;">
            <div>
                <p style="font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin: 0 0 4px;">Date</p>
                <p style="font-size: 15px; font-weight: 800; color: var(--text); margin: 0;">{{ $reservation->reservation_date->format('l, M d, Y') }}</p>
            </div>
            <div>
                <p style="font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin: 0 0 4px;">Time</p>
                <p style="font-size: 15px; font-weight: 800; color: var(--accent); margin: 0;">{{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}</p>
            </div>
            <div>
                <p style="font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin: 0 0 4px;">Guests</p>
                <p style="font-size: 15px; font-weight: 800; color: var(--text); margin: 0;">{{ $reservation->guests }} People</p>
            </div>
            <div>
                <p style="font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin: 0 0 4px;">Table</p>
                <p style="font-size: 15px; font-weight: 800; color: var(--text); margin: 0;">Table {{ $reservation->table->table_number ?? 'Not Assigned' }}</p>
            </div>
        </div>

        @if($reservation->special_requests)
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--line);">
            <p style="font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin: 0 0 4px;">Special Requests</p>
            <p style="font-size: 14px; color: var(--text); margin: 0;">{{ $reservation->special_requests }}</p>
        </div>
        @endif
    </div>

    @if($reservation->status === 'pending')
    <div style="background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 24px;">
        <h3 style="margin: 0 0 15px; font-size: 15px; font-weight: 800; color: var(--text);">Actions</h3>
        <div style="display: flex; gap: 10px;">
            <form action="{{ route('provider.restaurant.reservations.status', $reservation->id) }}" method="POST">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" style="background: var(--green); color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer;">Confirm Reservation</button>
            </form>
            <form action="{{ route('provider.restaurant.reservations.status', $reservation->id) }}" method="POST">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" style="background: var(--red-soft); color: var(--red); border: 1px solid var(--red); padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer;">Cancel</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection