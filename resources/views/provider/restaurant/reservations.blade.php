@extends('layouts.provider-restaurant')

@section('title', 'Reservations')
@section('page-title', 'Table Reservations')
@section('page-subtitle', 'Manage incoming table bookings')

@section('content')
<div style="padding: 0 0 24px;">
    @if(session('success'))
        <div style="background: var(--green-soft); color: var(--green); padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; border: 1px solid var(--green);">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div style="background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--text);">All Reservations</h3>
            <span class="pill" style="background: var(--accent-soft); color: var(--accent); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 800;">{{ $reservations->count() }} Total</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="tbl" style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--bg);">
                    <tr>
                        <th style="text-align: left; font-size: 11px; font-weight: 800; color: var(--muted); padding: 14px 24px; border-bottom: 1px solid var(--line); text-transform: uppercase;">Customer</th>
                        <th style="text-align: left; font-size: 11px; font-weight: 800; color: var(--muted); padding: 14px 24px; border-bottom: 1px solid var(--line); text-transform: uppercase;">Date & Time</th>
                        <th style="text-align: left; font-size: 11px; font-weight: 800; color: var(--muted); padding: 14px 24px; border-bottom: 1px solid var(--line); text-transform: uppercase;">Guests & Table</th>
                        <th style="text-align: left; font-size: 11px; font-weight: 800; color: var(--muted); padding: 14px 24px; border-bottom: 1px solid var(--line); text-transform: uppercase;">Status</th>
                        <th style="text-align: right; font-size: 11px; font-weight: 800; color: var(--muted); padding: 14px 24px; border-bottom: 1px solid var(--line); text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $res)
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 16px 24px;">
                            <div style="font-size: 14px; font-weight: 800; color: var(--text);">{{ $res->customer_name }}</div>
                            <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">{{ $res->customer_phone }}</div>
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="font-size: 13px; font-weight: 700; color: var(--text);">{{ \Carbon\Carbon::parse($res->reservation_date)->format('M d, Y') }}</div>
                            <div style="font-size: 12px; color: var(--accent); font-weight: 700; margin-top: 2px;">{{ \Carbon\Carbon::parse($res->reservation_time)->format('h:i A') }}</div>
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="font-size: 13px; color: var(--text);"><strong style="color: var(--accent);">{{ $res->guests }}</strong> Guests</div>
                            <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">Table: {{ $res->table->table_number ?? 'Any' }}</div>
                        </td>
                        <td style="padding: 16px 24px;">
                            @if($res->status === 'confirmed')
                                <span class="pill" style="background: var(--green-soft); color: var(--green); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800;">Confirmed</span>
                            @elseif($res->status === 'cancelled')
                                <span class="pill" style="background: var(--red-soft); color: var(--red); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800;">Cancelled</span>
                            @else
                                <span class="pill" style="background: var(--amber-soft); color: var(--amber); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800;">Pending</span>
                            @endif
                        </td>
                        <td style="padding: 16px 24px; text-align: right;">
    @if($res->status === 'pending')
        <a href="{{ route('provider.restaurant.reservations.show', $res->id) }}" style="background: var(--accent); color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; text-decoration: none; margin-right: 4px; display: inline-block;">View</a>
        <form action="{{ route('provider.restaurant.reservations.status', $res->id) }}" method="POST" style="display:inline;">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="confirmed">
            <button type="submit" style="background: var(--green); color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; margin-right: 4px;">Confirm</button>
        </form>
        <form action="{{ route('provider.restaurant.reservations.status', $res->id) }}" method="POST" style="display:inline;">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" style="background: var(--red-soft); color: var(--red); border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">Cancel</button>
        </form>
    @else
        <a href="{{ route('provider.restaurant.reservations.show', $res->id) }}" style="background: var(--bg); color: var(--text); padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid var(--line); display: inline-block;">View</a>
    @endif
</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 40px; text-align: center; color: var(--muted);">
                            <div style="font-size: 32px; margin-bottom: 10px; opacity: 0.5;">📅</div>
                            <p style="font-size: 14px; font-weight: 600;">No reservations yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection