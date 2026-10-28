@extends('layouts.provider-restaurant')
@section('title', __('messages.menu_management'))
@section('page-title', __('messages.menu_management'))
@section('page-subtitle', __('messages.manage_menu_desc'))

@push('restaurant-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f3f5fb;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafbff}
  .act-btn{padding:6px 10px;border-radius:7px;border:none;cursor:pointer;font-family:inherit;font-size:11px;font-weight:800;transition:.15s;margin-right:4px;}
  .act-edit{background:var(--accent-soft);color:var(--accent-d);}
  .act-edit:hover{background:var(--accent);color:#fff;}
  .act-del{background:var(--red-soft);color:#b91c1c;}
  .act-del:hover{background:#dc2626;color:#fff;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div><h3 style="font-size:16px; font-weight:800;">{{ __('messages.menu_management') }}</h3></div>
        <div style="display:flex; gap:10px;">
            <button type="button" onclick="openMenuModal()" style="background:var(--accent);color:#fff;padding:8px 15px;border-radius:8px;border:none;cursor:pointer;font-family:inherit;font-size:12px;font-weight:700;margin-right:8px;">+ {{ __('messages.add_menu_item') }}</button>
            <a href="{{ route('provider.restaurant.dashboard') }}" class="btn-primary" style="background:var(--card); color:var(--text); border:1px solid var(--line); padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700; box-shadow:none;">{{ __('messages.back_to_dashboard') }}</a>
        </div>
    </div>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.menu_items') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th>{{ __('messages.price') }}</th>
                    <th>{{ __('messages.status_col') }}</th>
                    <th style="text-align:right;">{{ __('messages.actions') ?? 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menuItems as $item)
                <tr>
                    <td><strong>{{ $item->emoji ?? '🍽️' }} {{ $item->name }}</strong></td>
                    <td><span class="pill" style="background:var(--accent-soft);color:var(--accent-d);">{{ ucfirst($item->category) }}</span></td>
                    <td style="font-weight:800;">Rs {{ number_format($item->price) }}</td>
                    <td>
                        @if($item->is_available)
                            <span class="pill" style="background:var(--green-soft);color:#047857;">Available</span>
                        @else
                            <span class="pill" style="background:var(--red-soft);color:#b91c1c;">Unavailable</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <button type="button" class="act-btn act-edit" onclick="openEditMenuModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }}, '{{ addslashes($item->category) }}', '{{ $item->emoji ?? '' }}')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form action="{{ route('provider.restaurant.menu.destroy', $item) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('Delete this menu item?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-del"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_menu_items_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Menu Item Modal --}}
<div id="menuModal" style="display:none;position:fixed;inset:0;background:rgba(28,25,23,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:420px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">{{ __('messages.add_menu_item') }}</h3>
            <button type="button" onclick="closeMenuModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.restaurant.menu.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.menu_item_name') }}</label>
                <input type="text" name="name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Chicken Momo">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.price') }} (Rs)</label>
                <input type="number" name="price" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="250">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.category') }}</label>
                <input type="text" name="category" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Main Course">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.emoji') }}</label>
                <input type="text" name="emoji" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="🍽️">
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent,#f97316),var(--accent-d,#ea580c));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">{{ __('messages.add_menu_item') }}</button>
        </form>
    </div>
</div>

{{-- Edit Menu Item Modal --}}
<div id="editMenuModal" style="display:none;position:fixed;inset:0;background:rgba(28,25,23,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:420px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">Edit Menu Item</h3>
            <button type="button" onclick="closeEditMenuModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form id="editMenuForm" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.menu_item_name') }}</label>
                <input type="text" name="name" id="edit_menu_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.price') }} (Rs)</label>
                <input type="number" name="price" id="edit_menu_price" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.category') }}</label>
                <input type="text" name="category" id="edit_menu_category" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.emoji') }}</label>
                <input type="text" name="emoji" id="edit_menu_emoji" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent,#f97316),var(--accent-d,#ea580c));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">Update Menu Item</button>
        </form>
    </div>
</div>

<script>
function openMenuModal() { document.getElementById('menuModal').style.display = 'flex'; }
function closeMenuModal() { document.getElementById('menuModal').style.display = 'none'; }
document.getElementById('menuModal').addEventListener('click', function(e) { if (e.target === this) closeMenuModal(); });

function openEditMenuModal(id, name, price, category, emoji) {
    var form = document.getElementById('editMenuForm');
    form.action = '/provider/restaurant/menu-items/' + id;
    document.getElementById('edit_menu_name').value = name;
    document.getElementById('edit_menu_price').value = price;
    document.getElementById('edit_menu_category').value = category;
    document.getElementById('edit_menu_emoji').value = emoji;
    document.getElementById('editMenuModal').style.display = 'flex';
}
function closeEditMenuModal() { document.getElementById('editMenuModal').style.display = 'none'; }
document.getElementById('editMenuModal').addEventListener('click', function(e) { if (e.target === this) closeEditMenuModal(); });
</script>
@endsection