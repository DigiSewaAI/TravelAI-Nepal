@extends('layouts.provider-hotel')

@section('title', __('messages.room_management'))
@section('page-title', __('messages.room_management'))
@section('page-subtitle', __('messages.room_management_subtitle'))

@push('hotel-head')
<style>
  .page-card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px}
  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:600px}
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
  .act-btn{padding:6px 10px;border-radius:7px;border:none;cursor:pointer;font-family:inherit;font-size:11px;font-weight:800;margin-right:4px;transition:.15s}
  .act-edit{background:var(--blue-soft);color:#1d4ed8}
  .act-edit:hover{background:#3b82f6;color:#fff}
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
@if($errors->any())
  <div class="alert-box alert-err">⚠ {{ $errors->first() }}</div>
@endif

<div class="page-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;gap:10px;flex-wrap:wrap;">
    <div style="font-size:15px;font-weight:800;color:var(--text);">{{ __('messages.room_management') }} ({{ $rooms->count() }})</div>
    <button type="button" onclick="openAddRoomModal()" style="background:var(--accent);color:#fff;padding:8px 15px;border-radius:8px;border:none;cursor:pointer;font-family:inherit;font-size:12px;font-weight:700;">+ Add Room</button>
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>{{ __('messages.room_no') }}</th>
          <th>{{ __('messages.type') }}</th>
          <th>{{ __('messages.floor_label') }}</th>
          <th>{{ __('messages.status') }}</th>
          <th style="text-align:right;">Actions</th>
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
            <td style="text-align:right;">
              <button type="button" class="act-btn act-edit" onclick="openEditRoomModal({{ $room->id }}, '{{ addslashes($room->room_number) }}', '{{ addslashes($room->room_type) }}', {{ $room->floor }}, '{{ $room->status }}')"><i class="fas fa-edit"></i> Edit</button>
              <form action="{{ route('provider.hotel.rooms.destroy', $room) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('Delete room {{ $room->room_number }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="act-btn act-del"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5">
              <div class="empty"><div>🚪</div>{{ __('messages.no_rooms_found') }}</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Add Room Modal --}}
<div id="addRoomModal" style="display:none;position:fixed;inset:0;background:rgba(14,23,38,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
  <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:420px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">Add New Room</h3>
      <button type="button" onclick="closeAddRoomModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
    </div>
    <form action="{{ route('provider.hotel.rooms.store') }}" method="POST">
      @csrf
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Room Number</label>
        <input type="text" name="room_number" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="101">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Type</label>
          <select name="room_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            <option value="Standard">Standard</option>
            <option value="Deluxe">Deluxe</option>
            <option value="Suite">Suite</option>
            <option value="Superior">Superior</option>
            <option value="Premium">Premium</option>
          </select>
        </div>
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Floor</label>
          <input type="number" name="floor" required min="1" value="1" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
        </div>
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Status</label>
        <select name="status" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
          <option value="available">Available</option>
          <option value="occupied">Occupied</option>
          <option value="reserved">Reserved</option>
          <option value="dirty">Dirty</option>
          <option value="maintenance">Maintenance</option>
        </select>
      </div>
      <button type="submit" class="btn-primary" style="width:100%;justify-content:center">Add Room</button>
    </form>
  </div>
</div>

{{-- Edit Room Modal --}}
<div id="editRoomModal" style="display:none;position:fixed;inset:0;background:rgba(14,23,38,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
  <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:420px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">Edit Room</h3>
      <button type="button" onclick="closeEditRoomModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
    </div>
    <form id="editRoomForm" method="POST">
      @csrf @method('PUT')
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Room Number</label>
        <input type="text" name="room_number" id="edit_room_number" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Type</label>
          <select name="room_type" id="edit_room_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            <option value="Standard">Standard</option>
            <option value="Deluxe">Deluxe</option>
            <option value="Suite">Suite</option>
            <option value="Superior">Superior</option>
            <option value="Premium">Premium</option>
          </select>
        </div>
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Floor</label>
          <input type="number" name="floor" id="edit_room_floor" required min="1" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
        </div>
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Status</label>
        <select name="status" id="edit_room_status" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
          <option value="available">Available</option>
          <option value="occupied">Occupied</option>
          <option value="reserved">Reserved</option>
          <option value="dirty">Dirty</option>
          <option value="maintenance">Maintenance</option>
        </select>
      </div>
      <button type="submit" class="btn-primary" style="width:100%;justify-content:center">Update Room</button>
    </form>
  </div>
</div>

@php
    if (!isset($occupiedRooms) || !isset($totalRooms)) {
        $__p = $provider ?? (auth()->check() ? auth()->user()->ownProvider() : null);
        if ($__p) {
            $totalRooms = \App\Models\HotelRoom::where('provider_id', $__p->id)->count();
            $occupiedRooms = \App\Models\HotelRoom::where('provider_id', $__p->id)->where('status', 'occupied')->count();
        } else { $totalRooms = 0; $occupiedRooms = 0; }
    }
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const occ = {{ (int) $occupiedRooms }};
    const total = {{ (int) $totalRooms }};
    const pct = total > 0 ? Math.round((occ / total) * 100) : 0;
    const el = document.getElementById('occVal');
    const bar = document.getElementById('occBar');
    if (el) el.innerHTML = pct + '% <small>' + occ + ' / ' + total + '</small>';
    if (bar) bar.style.width = pct + '%';
});

function openAddRoomModal() { document.getElementById('addRoomModal').style.display = 'flex'; }
function closeAddRoomModal() { document.getElementById('addRoomModal').style.display = 'none'; }
document.getElementById('addRoomModal').addEventListener('click', function(e) { if (e.target === this) closeAddRoomModal(); });

function openEditRoomModal(id, number, type, floor, status) {
    document.getElementById('editRoomForm').action = '/provider/hotel/rooms/' + id;
    document.getElementById('edit_room_number').value = number;
    document.getElementById('edit_room_type').value = type;
    document.getElementById('edit_room_floor').value = floor;
    document.getElementById('edit_room_status').value = status;
    document.getElementById('editRoomModal').style.display = 'flex';
}
function closeEditRoomModal() { document.getElementById('editRoomModal').style.display = 'none'; }
document.getElementById('editRoomModal').addEventListener('click', function(e) { if (e.target === this) closeEditRoomModal(); });
</script>
@endsection