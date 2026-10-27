@extends('layouts.provider-hotel')

@section('title', __('messages.room_management'))
@section('page-title', __('messages.room_management'))
@section('page-subtitle', __('messages.room_management_subtitle'))

@push('hotel-head')
<style>
  .page-card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px}
  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:500px}
  .tbl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#9aa8bc;font-weight:800;padding:0 12px 12px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:13px 12px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f3f7fc;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}
  .tbl tbody tr:hover{background:#fbfdff}
  .room-num{font-size:14px;font-weight:800;color:var(--text);letter-spacing:-.3px}
  .pill{font-size:10.5px;font-weight:800;padding:4px 10px;border-radius:20px;display:inline-block}
  .p-available{background:var(--green-soft);color:#047857}
  .p-occupied{background:#fee2e2;color:#b91c1c}
  .p-reserved{background:#e0e7ff;color:#4338ca}
  .p-dirty{background:var(--amber-soft);color:#b45309}
  .p-maintenance{background:#f1f5f9;color:#64748b}
  .p-muted{background:#f1f5f9;color:#64748b}
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
          <th>{{ __('messages.room_no') }}</th>
          <th>{{ __('messages.type') }}</th>
          <th>{{ __('messages.floor_label') }}</th>
          <th>{{ __('messages.status') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rooms as $room)
          @php
            $statusMap = [
              'available'   => ['label' => __('messages.status_available'),   'cls' => 'p-available'],
              'occupied'    => ['label' => __('messages.status_occupied'),    'cls' => 'p-occupied'],
              'reserved'    => ['label' => __('messages.status_reserved'),    'cls' => 'p-reserved'],
              'dirty'       => ['label' => __('messages.status_dirty'),       'cls' => 'p-dirty'],
              'maintenance' => ['label' => __('messages.status_maintenance'), 'cls' => 'p-maintenance'],
            ];
            $st = $statusMap[$room->status] ?? ['label' => ucfirst($room->status), 'cls' => 'p-muted'];
            $typeMap = [
              'Standard' => __('messages.room_type_standard'),
              'Deluxe'   => __('messages.room_type_deluxe'),
              'Suite'    => __('messages.room_type_suite'),
              'Superior' => __('messages.room_type_superior'),
              'Premium'  => __('messages.room_type_premium'),
            ];
            $typeLabel = $typeMap[$room->room_type] ?? $room->room_type;
          @endphp
          <tr>
            <td><span class="room-num">{{ $room->room_number }}</span></td>
            <td style="font-weight:700">{{ $typeLabel }}</td>
            <td>{{ __('messages.floor_label') }} {{ $room->floor }}</td>
            <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
          </tr>
        @empty
          <tr>
            <td colspan="4">
              <div class="empty"><div>🚪</div>{{ __('messages.no_rooms_found') }}</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection