@extends('layouts.provider-restaurant')
@section('title', __('messages.order_history'))
@section('page-title', __('messages.order_history'))
@section('page-subtitle', __('messages.manage_orders_desc'))

@push('restaurant-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f3f5fb;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafbff}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div><h3 style="font-size:16px; font-weight:800;">{{ __('messages.order_history') }}</h3></div>
        <button type="button" onclick="openOrderModal()" style="background:var(--accent);color:#fff;padding:8px 15px;border-radius:8px;border:none;cursor:pointer;font-family:inherit;font-size:12px;font-weight:700;margin-right:8px;">+ {{ __('messages.new_order') }}</button>
        <a href="{{ route('provider.restaurant.dashboard') }}" class="btn-primary" style="background:var(--accent); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
    </div>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.order_id') }}</th>
                    <th>{{ __('messages.table_col') }}</th>
                    <th>{{ __('messages.items_col') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status_col') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                <tr>
                    <td><strong style="color:var(--accent-d);">#{{ $o->order_number }}</strong></td>
                    <td>{{ $o->table ? $o->table->table_number : 'Takeaway' }}</td>
                    <td>
                        @foreach($o->items as $item)
                            <div style="font-size:12px;color:var(--muted);">{{ $item->item_name }} ×{{ $item->quantity }}</div>
                        @endforeach
                    </td>
                    <td style="font-weight:800;">Rs {{ number_format($o->total) }}</td>
                    <td>
                        @php
                            $colors = [
                                'pending' => 'var(--amber-soft); color:#b45309',
                                'preparing' => 'var(--blue-soft); color:#1d4ed8',
                                'ready' => 'var(--purple-soft); color:#6d28d9',
                                'served' => 'var(--green-soft); color:#047857',
                            ];
                            $c = $colors[$o->status] ?? 'var(--line); color:var(--muted)';
                        @endphp
                        <span class="pill" style="background:{{ $c }};">{{ ucfirst($o->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_orders_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@php
if (!isset($tablesData)) {
    $tablesData = \App\Models\RestaurantTable::where('provider_id', $provider->id)->orderBy('table_number')->get()->map(fn($t) => ['db_id' => $t->id, 'n' => $t->table_number, 'seats' => $t->seats])->all();
}
if (!isset($allProducts)) {
    $allProducts = \App\Models\MenuItem::where('provider_id', $provider->id)->where('is_available', true)->orderBy('name')->get()->map(fn($i) => ['db_id' => $i->id, 'emoji' => $i->emoji ?? 'ðŸ½ï¸', 'name' => $i->name, 'price' => (float) $i->price])->all();
}
@endphp

{{-- New Order Modal --}}
<div id="orderModal" style="display:none;position:fixed;inset:0;background:rgba(28,25,23,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:600px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">{{ __('messages.new_order') }}</h3>
            <button type="button" onclick="closeOrderModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.restaurant.orders.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:20px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.select_table') }}</label>
                <select name="table_id" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                    <option value="">{{ __('messages.select_table') }}...</option>
                    @foreach($tablesData as $t)
                        <option value="{{ $t['db_id'] }}">{{ $t['label'] ?? ('T-'.str_pad($t['n'],2,'0',STR_PAD_LEFT)) }} ({{ $t['seats'] }} {{ __('messages.seats') }})</option>
                    @endforeach
                </select>
            </div>
            <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;display:block;margin-bottom:8px">{{ __('messages.select_menu_items') }}</label>
            <div style="max-height:250px;overflow-y:auto;border:1px solid var(--line);border-radius:12px;padding:10px;margin-bottom:20px">
                @forelse($allProducts as $item)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px;border-bottom:1px solid #f0f0f0;gap:8px">
                    <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:0">
                        <input type="checkbox" class="order-item-check" data-price="{{ $item['price'] ?? 0 }}" name="items[{{ $loop->index }}][id]" value="{{ $item['db_id'] ?? '' }}">
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px">{{ $item['emoji'] ?? 'ðŸ½ï¸' }} {{ $item['name'] ?? '' }} (Rs {{ number_format($item['price'] ?? 0) }})</span>
                    </div>
                    <input type="number" name="items[{{ $loop->index }}][qty]" class="item-qty" data-price="{{ $item['price'] ?? 0 }}" value="1" min="1" style="width:60px;padding:4px;text-align:center;border:1px solid var(--line);border-radius:6px;font-family:inherit;font-size:12px" disabled>
                </div>
                @empty
                <p style="text-align:center;color:var(--muted);padding:20px">{{ __('messages.no_menu_items_yet') }}</p>
                @endforelse
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding:15px;background:var(--bg,#faf6f1);border-radius:12px">
                <span style="font-size:14px;font-weight:700">{{ __('messages.total_amount') }}</span>
                <span style="font-size:18px;font-weight:800" id="orderTotalDisplay2">Rs 0</span>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent,#f97316),var(--accent-d,#ea580c));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">{{ __('messages.create_order') }}</button>
        </form>
    </div>
</div>

<script>
function openOrderModal() { document.getElementById('orderModal').style.display = 'flex'; }
function closeOrderModal() { document.getElementById('orderModal').style.display = 'none'; }
document.getElementById('orderModal').addEventListener('click', function(e) { if (e.target === this) closeOrderModal(); });
function updateOrderTotal() {
    var total = 0;
    document.querySelectorAll('#orderModal .order-item-check:checked').forEach(function(chk) {
        var parent = chk.closest('div').parentElement;
        var qty = parent.querySelector('.item-qty');
        total += parseFloat(chk.dataset.price || 0) * (parseInt(qty.value) || 1);
    });
    var el = document.getElementById('orderTotalDisplay2');
    if (el) el.textContent = 'Rs ' + total.toLocaleString();
}
document.querySelectorAll('#orderModal .order-item-check').forEach(function(chk) {
    chk.addEventListener('change', function() {
        var parent = this.closest('div').parentElement;
        var qty = parent.querySelector('.item-qty');
        if (this.checked) { qty.removeAttribute('disabled'); } else { qty.setAttribute('disabled', 'disabled'); qty.value = 1; }
        updateOrderTotal();
    });
});
document.querySelectorAll('#orderModal .item-qty').forEach(function(q) {
    q.addEventListener('input', updateOrderTotal);
});
</script>
@endsection