@extends($provider_layout ?? 'layouts.provider-transport')
@section('title', __('messages.bookings') ?? 'Bookings')
@section('page-title', __('messages.bookings') ?? 'Bookings')
@section('page-subtitle', 'All passenger bookings')
@section('content')
@php
    $sc = ['pending'=>['#fef4e2','#b45309'],'confirmed'=>['#e6f8f1','#059669'],'completed'=>['#e6f8f1','#059669'],'cancelled'=>['#fdecec','#dc2626'],'in_progress'=>['#e0f2fe','#0284c7']];
@endphp
<div style="display:flex;flex-direction:column;gap:18px">
    <div style="background:linear-gradient(135deg,var(--accent,#0ea5e9) 0%,var(--accent-d,#0284c7) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <div>
            <h2 style="font-size:22px;font-weight:800;margin:0;color:#fff">{{ __('messages.bookings') ?? 'Bookings' }}</h2>
            <p style="font-size:13px;opacity:.88;margin-top:6px">{{ $bookings->total() }} bookings</p>
        </div>
    </div>
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:var(--radius,18px);padding:22px">
        @if($bookings->count() > 0)
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="border-bottom:2px solid var(--line,#e4ecf6)">
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;color:var(--muted,#6b7f96)">Booking</th>
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;color:var(--muted,#6b7f96)">Passenger</th>
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;color:var(--muted,#6b7f96)">Route</th>
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;color:var(--muted,#6b7f96)">Travel Date</th>
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;color:var(--muted,#6b7f96)">Amount</th>
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;color:var(--muted,#6b7f96)">Status</th>
                            <th style="text-align:right;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;color:var(--muted,#6b7f96)">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $b)
                            @php [$bg,$fg] = $sc[$b->status] ?? ['#f3f4f6','#6b7280']; @endphp
                            <tr style="border-bottom:1px solid var(--line,#e4ecf6)">
                                <td style="padding:14px 8px;font-size:13px;font-weight:800;color:var(--accent-d,#0284c7);font-family:monospace">{{ $b->booking_ref ?? '#' . $b->id }}</td>
                                <td style="padding:14px 8px;font-size:13px;color:var(--text,#0c1e33)">
                                    <div style="font-weight:700">{{ $b->passenger_name ?? 'N/A' }}</div>
                                    @if($b->passenger_email)<div style="font-size:11px;color:var(--muted,#6b7f96)">{{ $b->passenger_email }}</div>@endif
                                </td>
                                <td style="padding:14px 8px;font-size:13px;color:var(--text,#0c1e33)">{{ $b->route_name ?? 'N/A' }}</td>
                                <td style="padding:14px 8px;font-size:13px;color:var(--muted,#6b7f96)">{{ $b->travel_date ? \Carbon\Carbon::parse($b->travel_date)->format('M d, Y') : 'N/A' }}</td>
                                <td style="padding:14px 8px;font-size:13px;font-weight:700;color:var(--text,#0c1e33)">Rs {{ number_format($b->amount, 2) }}</td>
                                <td style="padding:14px 8px">
                                    <span style="padding:5px 10px;border-radius:8px;background:{{ $bg }};color:{{ $fg }};font-size:11px;font-weight:800;text-transform:uppercase">{{ $b->status }}</span>
                                </td>
                                <td style="padding:14px 8px;text-align:right">
                                    <a href="{{ route('provider.transport.bookings.show', $b) }}" style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;background:var(--sky-soft,#e0f2fe);color:var(--accent-d,#0284c7);font-size:12px;font-weight:800;text-decoration:none">
                                        View
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px">{{ $bookings->links() }}</div>
        @else
            <div style="text-align:center;padding:60px 20px;color:var(--muted,#6b7f96)">
                <h3 style="font-size:16px;font-weight:800;color:var(--text,#0c1e33);margin:0 0 6px">No bookings yet</h3>
                <p style="font-size:13px;margin:0">New bookings will appear here.</p>
            </div>
        @endif
    </div>
</div>
@endsection