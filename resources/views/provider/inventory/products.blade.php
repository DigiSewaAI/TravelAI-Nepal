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
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div>
            <h3 style="font-size:16px; font-weight:800;">{{ __('messages.product_list') }}</h3>
            <p style="font-size:12px; color:var(--muted); margin-top:4px;">{{ __('messages.manage_products_subtitle') }}</p>
        </div>
        <a href="{{ route('provider.inventory.dashboard') }}" class="btn-primary" style="background:var(--indigo); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">Back to Dashboard</a>
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
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr>
                    <td>
                        <div style="font-weight:800; color:var(--text);">{{ $p->name }}</div>
                    </td>
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
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--muted);">{{ __('messages.no_products_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection