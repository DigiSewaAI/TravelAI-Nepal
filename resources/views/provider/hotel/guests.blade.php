@extends('layouts.provider-hotel')

@section('title', __('messages.active_guests'))
@section('page-title', __('messages.active_guests'))
@section('page-subtitle', __('messages.active_guests_subtitle'))

@push('hotel-head')
<style>
  .page-card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px}
  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:600px}
  .tbl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#9aa8bc;font-weight:800;padding:0 12px 12px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:13px 12px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f3f7fc;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}
  .tbl tbody tr:hover{background:#fbfdff}
  .guest{display:flex;align-items:center;gap:10px}
  .g-av{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:12px;flex-shrink:0;background:linear-gradient(135deg,#3b82f6,#6366f1)}
  .g-name{font-weight:800;font-size:12.5px;line-height:1.25}
  .g-mail{font-size:10.5px;color:var(--muted);font-weight:600}
  .pill{font-size:10.5px;font-weight:800;padding:4px 10px;border-radius:20px;display:inline-block;background:var(--green-soft);color:#047857}
  .empty{text-align:center;padding:44px 20px;font-size:13px;font-weight:600;color:var(--muted)}
  .empty div{font-size:38px;margin-bottom:10px;opacity:.5}
</style>
@endpush

@section('content')
<div class="page-card">
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>{{ __('messages.guest') }}</th>
          <th>{{ __('messages.email') }}</th>
          <th>{{ __('messages.room') }}</th>
          <th>{{ __('messages.nights') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($guests as $guest)
          @php
            $inits = collect(explode(' ', $guest->guest_name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
          @endphp
          <tr>
            <td>
              <div class="guest">
                <div class="g-av">{{ $inits }}</div>
                <div>
                  <div class="g-name">{{ $guest->guest_name }}</div>
                  <div class="g-mail">{{ $guest->guest_email ?? '—' }}</div>
                </div>
              </div>
            </td>
            <td>{{ $guest->guest_email ?? '—' }}</td>
            <td style="font-weight:800">{{ __('messages.room') }} {{ $guest->room->room_number ?? '—' }}</td>
            <td style="font-weight:800">{{ $guest->nights }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="4">
              <div class="empty"><div>👥</div>{{ __('messages.no_active_guests') }}</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection