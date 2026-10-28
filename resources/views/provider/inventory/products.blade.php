@extends('layouts.provider-inventory')
@section('title', __('messages.product_list'))
@section('page-title', __('messages.product_list'))
@section('page-subtitle', __('messages.manage_products_subtitle'))

@push('inv-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto; margin-top:10px;}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f3f5fb;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafbff;}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
  .type-badge{background:var(--indigo-soft);color:var(--indigo-d);}
  .stock-out{color:var(--red); background:var(--red-soft); padding:2px 8px; border-radius:6px;}
  .stock-in{color:var(--emerald); background:var(--emerald-soft); padding:2px 8px; border-radius:6px;}
  .act-btn{padding:6px 10px;border-radius:7px;border:none;cursor:pointer;font-family:inherit;font-size:11px;font-weight:800;transition:.15s;margin-right:4px;}
  .act-edit{background:var(--indigo-soft);color:var(--indigo-d);}
  .act-edit:hover{background:var(--indigo);color:#fff;}
  .act-del{background:var(--red-soft);color:#b91c1c;}
  .act-del:hover{background:#dc2626;color:#fff;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div>
            <h3 style="font-size:16px; font-weight:800;">{{ __('messages.product_list') }}</h3>
            <p style="font-size:12px; color:var(--muted); margin-top:4px;">{{ __('messages.manage_products_subtitle') }}</p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('provider.products.create') }}" style="background:var(--indigo); color:#fff; padding:8px 15px; border-radius:8px; border:none; cursor:pointer; font-family:inherit; font-size:12px; font-weight:700; text-decoration:none;">+ Add Product</a>
            <a href="{{ route('provider.inventory.dashboard') }}" class="btn-primary" style="background:var(--card); color:var(--text); border:1px solid var(--line); padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">Back to Dashboard</a>
        </div>
    </div>

    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.product_name') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.price_col') }}</th>
                    <th>{{ __('messages.stock_col') }}</th>
                    <th>{{ __('messages.type_col') }}</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr>
                    <td><div style="font-weight:800; color:var(--text);">{{ $p->name }}</div></td>
                    <td style="color:#64748b; font-family:monospace;">{{ $p->sku ?? 'N/A' }}</td>
                    <td style="font-weight:800;">Rs {{ number_format($p->price) }}</td>
                    <td>
                        @if($p->stock_quantity > 0)
                            <span class="stock-in">{{ $p->stock_quantity }} in stock</span>
                        @else
                            <span class="stock-out">Out of Stock</span>
                        @endif
                    </td>
                    <td><span class="pill type-badge">{{ ucfirst($p->product_type) }}</span></td>
                    <td style="text-align:right;">
                    <a href="{{ route('provider.products.edit', $p) }}" class="act-btn act-edit" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                        <form action="{{ route('provider.inventory.products.destroy', $p) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('Delete {{ addslashes($p->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-del" title="Delete"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--muted);">{{ __('messages.no_products_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Product Modal --}}
<div id="addProductModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">Add New Product</h3>
            <button type="button" onclick="closeAddProductModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.inventory.products.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Product Name</label>
                <input type="text" name="name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Down Jacket">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Price (Rs)</label>
                    <input type="number" name="price" step="0.01" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="2500">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Type</label>
                    <select name="product_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="shop">Shop</option>
                        <option value="rental">Rental</option>
                        <option value="wholesale">Wholesale</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Stock Quantity</label>
                    <input type="number" name="stock_quantity" required min="0" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="50">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" required min="0" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="5">
                </div>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--indigo,#6366f1),var(--indigo-d,#4f46e5));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">Add Product</button>
        </form>
    </div>
</div>

{{-- Edit Product Modal --}}
<div id="editProductModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">Edit Product</h3>
            <button type="button" onclick="closeEditProductModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form id="editProductForm" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Product Name</label>
                <input type="text" name="name" id="edit_prod_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Price (Rs)</label>
                    <input type="number" name="price" id="edit_prod_price" step="0.01" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Type</label>
                    <select name="product_type" id="edit_prod_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="shop">Shop</option>
                        <option value="rental">Rental</option>
                        <option value="wholesale">Wholesale</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="edit_prod_stock" required min="0" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" id="edit_prod_low" required min="0" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--indigo,#6366f1),var(--indigo-d,#4f46e5));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">Update Product</button>
        </form>
    </div>
</div>

<script>
function openAddProductModal() { document.getElementById('addProductModal').style.display = 'flex'; }
function closeAddProductModal() { document.getElementById('addProductModal').style.display = 'none'; }
document.getElementById('addProductModal').addEventListener('click', function(e) { if (e.target === this) closeAddProductModal(); });

function openEditProductModal(id, name, price, type, stock, low) {
    var form = document.getElementById('editProductForm');
    form.action = '/provider/inventory/products/' + id;
    document.getElementById('edit_prod_name').value = name;
    document.getElementById('edit_prod_price').value = price;
    document.getElementById('edit_prod_type').value = type;
    document.getElementById('edit_prod_stock').value = stock;
    document.getElementById('edit_prod_low').value = low;
    document.getElementById('editProductModal').style.display = 'flex';
}
function closeEditProductModal() { document.getElementById('editProductModal').style.display = 'none'; }
document.getElementById('editProductModal').addEventListener('click', function(e) { if (e.target === this) closeEditProductModal(); });
</script>

{{-- Stock Health widget sync --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const SH = {{ (int)($stockHealth ?? 0) }};
    const el = document.getElementById('scVal');
    const bar = document.getElementById('scBar');
    if (el) el.innerHTML = SH + '% <small>{{ __('messages.in_stock_label') }}</small>';
    if (bar) bar.style.width = SH + '%';
});
</script>
@endsection