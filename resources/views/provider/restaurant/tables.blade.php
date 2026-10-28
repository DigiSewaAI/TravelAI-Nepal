@extends('layouts.provider-restaurant')
@section('title', __('messages.table_management'))
@section('page-title', __('messages.table_management'))
@section('page-subtitle', __('messages.manage_tables_desc'))

@push('restaurant-head')
<style>
  .tbl-grid{display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:15px;}
  .tbl-card{background:var(--card); border:1px solid var(--line); border-radius:14px; padding:18px 16px; text-align:center; transition:0.2s; position:relative;}
  .tbl-card:hover{transform:translateY(-3px); box-shadow:0 10px 25px -5px rgba(0,0,0,0.1); border-color:var(--accent);}
  .tbl-num{font-size:22px; font-weight:800; color:var(--text); margin-bottom:4px;}
  .tbl-seats{font-size:12px; color:var(--muted); font-weight:600; margin-bottom:10px;}
  .tbl-actions{position:absolute; top:8px; right:8px; display:flex; gap:4px; opacity:0; transition:.15s;}
  .tbl-card:hover .tbl-actions{opacity:1;}
  .tbl-act{width:26px; height:26px; border-radius:6px; border:none; cursor:pointer; display:grid; place-items:center; font-size:11px; transition:.15s; font-family:inherit;}
  .tbl-edit{background:var(--accent-soft); color:var(--accent-d);}
  .tbl-edit:hover{background:var(--accent); color:#fff;}
  .tbl-del{background:var(--red-soft); color:#b91c1c;}
  .tbl-del:hover{background:#dc2626; color:#fff;}
  .pill{display:inline-block; padding:4px 11px; border-radius:20px; font-size:11px; font-weight:800;}
  .tbl-alert{padding:11px 15px; border-radius:10px; font-size:13px; font-weight:600; margin-bottom:14px; display:flex; gap:10px; align-items:flex-start; border-left:4px solid;}
  .tbl-ok{background:var(--green-soft); color:#047857; border-color:#10b981;}
  .tbl-err{background:var(--red-soft); color:#b91c1c; border-color:#dc2626;}
</style>
@endpush

@section('content')

@if(session('success'))
  <div class="tbl-alert tbl-ok">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="tbl-alert tbl-err">⚠ {{ session('error') }}</div>
@endif
@if($errors->any())
  <div class="tbl-alert tbl-err">⚠ {{ $errors->first() }}</div>
@endif

<div style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
    <h3 style="font-size:16px; font-weight:800; margin:0;">{{ __('messages.table_management') }} ({{ $tables->count() }})</h3>
    <div style="display:flex; gap:10px;">
        <button type="button" onclick="openAddTableModal()" style="background:var(--accent);color:#fff;padding:8px 15px;border-radius:8px;border:none;cursor:pointer;font-family:inherit;font-size:12px;font-weight:700;">+ Add Table</button>
        <a href="{{ route('provider.restaurant.dashboard') }}" class="btn-primary" style="background:var(--card); color:var(--text); border:1px solid var(--line); padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700; box-shadow:none;">{{ __('messages.back_to_dashboard') }}</a>
    </div>
</div>

<div class="tbl-grid">
    @forelse($tables as $t)
    <div class="tbl-card">
        <div class="tbl-actions">
            <button type="button" class="tbl-act tbl-edit" onclick="openEditTableModal({{ $t->id }}, '{{ addslashes($t->table_number) }}', {{ $t->seats ?? 0 }}, '{{ $t->status }}')" title="Edit"><i class="fas fa-edit"></i></button>
            <form action="{{ route('provider.restaurant.tables.destroy', $t) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('Delete table {{ $t->table_number }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="tbl-act tbl-del" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        <div class="tbl-num">{{ $t->table_number }}</div>
        <div class="tbl-seats">{{ $t->seats }} {{ __('messages.seats') }}</div>
        @php
            $c = match($t->status) {
                'free', 'available' => 'background:var(--green-soft); color:#047857',
                'occupied' => 'background:var(--amber-soft); color:#b45309',
                'reserved' => 'background:var(--blue-soft); color:#1d4ed8',
                'cleaning' => 'background:var(--line); color:var(--muted)',
                default => 'background:var(--line); color:var(--muted)',
            };
            $label = ($t->status === 'available') ? 'Free' : ucfirst($t->status);
        @endphp
        <span class="pill" style="{{ $c }};">{{ $label }}</span>
    </div>
    @empty
    <p style="color:var(--muted); grid-column:1/-1; text-align:center; padding:30px;">{{ __('messages.no_tables_found') }}</p>
    @endforelse
</div>

{{-- Add Table Modal --}}
<div id="addTableModal" style="display:none;position:fixed;inset:0;background:rgba(28,25,23,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:420px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">Add New Table</h3>
            <button type="button" onclick="closeAddTableModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.restaurant.tables.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Table Number</label>
                <input type="text" name="table_number" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="T-15">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Seats</label>
                <input type="number" name="seats" required min="1" value="4" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Status</label>
                <select name="status" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                    <option value="free">Free</option>
                    <option value="occupied">Occupied</option>
                    <option value="reserved">Reserved</option>
                    <option value="cleaning">Cleaning</option>
                </select>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent,#f97316),var(--accent-d,#ea580c));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">Add Table</button>
        </form>
    </div>
</div>

{{-- Edit Table Modal --}}
<div id="editTableModal" style="display:none;position:fixed;inset:0;background:rgba(28,25,23,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:420px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">Edit Table</h3>
            <button type="button" onclick="closeEditTableModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form id="editTableForm" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Table Number</label>
                <input type="text" name="table_number" id="edit_tbl_number" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Seats</label>
                <input type="number" name="seats" id="edit_tbl_seats" required min="1" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Status</label>
                <select name="status" id="edit_tbl_status" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                    <option value="free">Free</option>
                    <option value="occupied">Occupied</option>
                    <option value="reserved">Reserved</option>
                    <option value="cleaning">Cleaning</option>
                </select>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent,#f97316),var(--accent-d,#ea580c));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">Update Table</button>
        </form>
    </div>
</div>

<script>
function openAddTableModal() { document.getElementById('addTableModal').style.display = 'flex'; }
function closeAddTableModal() { document.getElementById('addTableModal').style.display = 'none'; }
document.getElementById('addTableModal').addEventListener('click', function(e) { if (e.target === this) closeAddTableModal(); });

function openEditTableModal(id, number, seats, status) {
    var form = document.getElementById('editTableForm');
    form.action = '/provider/restaurant/tables/' + id;
    document.getElementById('edit_tbl_number').value = number;
    document.getElementById('edit_tbl_seats').value = seats;
    document.getElementById('edit_tbl_status').value = (status === 'available') ? 'free' : status;
    document.getElementById('editTableModal').style.display = 'flex';
}
function closeEditTableModal() { document.getElementById('editTableModal').style.display = 'none'; }
document.getElementById('editTableModal').addEventListener('click', function(e) { if (e.target === this) closeEditTableModal(); });
</script>
@endsection