@extends('layouts.provider-hotel')

@section('title', __('messages.all_bookings'))
@section('page-title', __('messages.all_bookings'))
@section('page-subtitle', __('messages.all_bookings_subtitle'))

@push('hotel-head')
<style>
  .page-card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px}
  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:800px}
  .tbl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#9aa8bc;font-weight:800;padding:0 12px 12px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:13px 12px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f3f7fc;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}
  .tbl tbody tr:hover{background:#fbfdff}
  .guest{display:flex;align-items:center;gap:10px}
  .g-av{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:12px;flex-shrink:0;background:linear-gradient(135deg,#3b82f6,#6366f1)}
  .g-name{font-weight:800;font-size:12.5px;line-height:1.25}
  .g-mail{font-size:10.5px;color:var(--muted);font-weight:600}
  .pill{font-size:10.5px;font-weight:800;padding:4px 10px;border-radius:20px;display:inline-block}
  .p-paid{background:var(--green-soft);color:#047857}
  .p-pending{background:var(--amber-soft);color:#b45309}
  .p-refund{background:var(--red-soft);color:#b91c1c}
  .p-muted{background:#f1f5f9;color:#64748b}
  .empty{text-align:center;padding:44px 20px;font-size:13px;font-weight:600;color:var(--muted)}
  .empty div{font-size:38px;margin-bottom:10px;opacity:.5}
  .act-btn{padding:6px 10px;border-radius:7px;border:none;cursor:pointer;font-family:inherit;font-size:11px;font-weight:800;margin-right:4px;transition:.15s}
  .act-in{background:var(--green-soft);color:#047857}
  .act-in:hover{background:#10b981;color:#fff}
  .act-out{background:var(--blue-soft);color:#1d4ed8}
  .act-out:hover{background:#3b82f6;color:#fff}
  .act-del{background:var(--red-soft);color:#b91c1c}
  .act-del:hover{background:#dc2626;color:#fff}
  .alert-box{padding:11px 15px;border-radius:10px;font-size:12.5px;font-weight:700;margin-bottom:14px;display:flex;gap:10px;align-items:center;border-left:4px solid}
  .alert-ok{background:var(--green-soft);color:#047857;border-color:#10b981}
  .alert-err{background:var(--red-soft);color:#b91c1c;border-color:#dc2626}
</style>
@endpush

@section('content')
@if(session('success'))
  <div class="alert-box alert-ok">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert-box alert-err">⚠ {{ session('error') }}</div>
@endif

<div class="page-card">
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>{{ __('messages.guest') }}</th>
          <th>{{ __('messages.room') }}</th>
          <th>{{ __('messages.checkin_col') }}</th>
          <th>{{ __('messages.checkout_col') }}</th>
          <th>{{ __('messages.nights') }}</th>
          <th>{{ __('messages.amount') }}</th>
          <th>{{ __('messages.status') }}</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($bookings as $b)
          @php
            $statusMap = [
              'checked_in'  => ['label' => __('messages.status_checked_in'),  'cls' => 'p-paid'],
              'checked_out' => ['label' => __('messages.status_checked_out'), 'cls' => 'p-muted'],
              'pending'     => ['label' => __('messages.status_pending'),     'cls' => 'p-pending'],
              'cancelled'   => ['label' => __('messages.status_cancelled'),   'cls' => 'p-refund'],
            ];
            $st = $statusMap[$b->status] ?? ['label' => ucfirst($b->status), 'cls' => 'p-muted'];
            $inits = collect(explode(' ', $b->guest_name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
          @endphp
          <tr>
            <td>
              <div class="guest">
                <div class="g-av">{{ $inits }}</div>
                <div>
                  <div class="g-name">{{ $b->guest_name }}</div>
                  <div class="g-mail">{{ $b->guest_email ?? '—' }}</div>
                </div>
              </div>
            </td>
            <td style="font-weight:800">{{ __('messages.room') }} {{ $b->room->room_number ?? '—' }}</td>
            <td>{{ $b->check_in_date->format('M d, Y') }}</td>
            <td>{{ $b->check_out_date->format('M d, Y') }}</td>
            <td style="font-weight:800">{{ $b->nights }}</td>
            <td style="font-weight:800">Rs {{ number_format($b->amount) }}</td>
            <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
            <td style="text-align:right;white-space:nowrap;">
              @if($b->status === 'pending')
                <form action="{{ route('provider.hotel.bookings.action', $b) }}" method="POST" style="display:inline;margin:0;">
                  @csrf @method('PATCH')
                  <input type="hidden" name="action" value="check_in">
                  <button type="submit" class="act-btn act-in">Check In</button>
                </form>
              @elseif($b->status === 'checked_in')
                <form action="{{ route('provider.hotel.bookings.action', $b) }}" method="POST" style="display:inline;margin:0;">
                  @csrf @method('PATCH')
                  <input type="hidden" name="action" value="check_out">
                  <button type="submit" class="act-btn act-out">Check Out</button>
                </form>
              @endif
              <form action="{{ route('provider.hotel.bookings.destroy', $b) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('Delete this booking?')">
                @csrf @method('DELETE')
                <button type="submit" class="act-btn act-del"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8">
              <div class="empty"><div>📅</div>{{ __('messages.no_bookings_found') }}</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@php
    $__occR = $occupiedRooms ?? null;
    $__totR = $totalRooms ?? null;
    if ($__occR === null || $__totR === null) {
        $__p = $provider ?? (auth()->check() ? auth()->user()->ownProvider() : null);
        if ($__p) {
            $__totR = \App\Models\HotelRoom::where('provider_id', $__p->id)->count();
            $__occR = \App\Models\HotelRoom::where('provider_id', $__p->id)->where('status', 'occupied')->count();
        } else { $__totR = 0; $__occR = 0; }
    }
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const occ = {{ (int) $__occR }};
    const total = {{ (int) $__totR }};
    const pct = total > 0 ? Math.round((occ / total) * 100) : 0;
    const el = document.getElementById('occVal');
    const bar = document.getElementById('occBar');
    if (el) el.innerHTML = pct + '% <small>' + occ + ' / ' + total + '</small>';
    if (bar) bar.style.width = pct + '%';
});
</script>
@endsection