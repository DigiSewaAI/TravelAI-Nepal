@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.products_title'))
@section('header', __('messages.products_title'))

@section('content')
<style>
  .pix-wrap{max-width:1200px;margin:0 auto;}
  .pix-hero{background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));border-radius:18px;padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);margin-bottom:20px;}
  .pix-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .pix-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;letter-spacing:-.3px;position:relative;z-index:1;}
  .pix-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .pix-btn-add{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.96);color:var(--accent-d,#1d4ed8);padding:11px 18px;border-radius:11px;font-size:13px;font-weight:800;text-decoration:none;box-shadow:0 4px 14px rgba(0,0,0,.15);position:relative;z-index:1;transition:.15s;}
  .pix-btn-add:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.22);}

  .pix-tabs{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;}
  .pix-tab{padding:9px 18px;border-radius:10px;font-size:13px;font-weight:800;text-decoration:none;border:1.5px solid var(--line,#e5e7eb);color:var(--muted,#6b7280);background:var(--card,#fff);transition:.15s;}
  .pix-tab:hover{border-color:var(--accent,#2563eb);color:var(--accent-d,#1d4ed8);}
  .pix-tab.on{background:var(--accent,#2563eb);color:#fff;border-color:var(--accent-d,#1d4ed8);box-shadow:0 4px 12px -4px var(--accent,#2563eb);}

  .pix-card{background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.02);}
  .pix-tbl{width:100%;border-collapse:collapse;min-width:750px;}
  .pix-tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#6b7280);font-weight:800;padding:14px 18px;background:var(--bg,#f9fafb);border-bottom:1px solid var(--line,#e5e7eb);}
  .pix-tbl td{padding:15px 18px;font-size:13px;font-weight:600;color:var(--text,#1f2937);border-bottom:1px solid var(--line,#e5e7eb);vertical-align:middle;}
  .pix-tbl tbody tr:hover{background:var(--accent-soft,#eff6ff);}
  .pix-tbl tbody tr:last-child td{border-bottom:none;}
  .pix-name{font-weight:800;color:var(--text,#1f2937);font-size:14px;}
  .pix-type{display:inline-block;padding:3px 11px;border-radius:20px;font-size:11px;font-weight:800;}
  .pix-type.shop{background:var(--purple-soft,#ede9fe);color:var(--purple,#8b5cf6);}
  .pix-type.rental{background:var(--blue-soft,#dbeafe);color:var(--blue,#3b82f6);}
  .pix-type.wholesale{background:var(--amber-soft,#fef3c7);color:var(--amber,#b45309);}
  .pix-pill{display:inline-block;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:800;}
  .pix-pill.on{background:var(--green-soft,#dcfce7);color:var(--green,#16a34a);}
  .pix-pill.off{background:var(--line,#e5e7eb);color:var(--muted,#6b7280);}
  .pix-act{display:inline-flex;align-items:center;gap:5px;padding:6px 11px;border-radius:7px;font-size:11px;font-weight:800;text-decoration:none;border:none;cursor:pointer;transition:.15s;font-family:inherit;margin-right:4px;}
  .pix-act-edit{background:var(--accent-soft,#eff6ff);color:var(--accent-d,#1d4ed8);}
  .pix-act-edit:hover{background:var(--accent,#2563eb);color:#fff;}
  .pix-act-del{background:var(--red-soft,#fee2e2);color:var(--red,#dc2626);}
  .pix-act-del:hover{background:var(--red,#dc2626);color:#fff;}
  .pix-empty{padding:56px 24px;text-align:center;color:var(--muted,#6b7280);}
  .pix-empty p{font-size:14px;margin:0;}
  .pix-pager{padding:18px 22px;border-top:1px solid var(--line,#e5e7eb);}
</style>

<div class="pix-wrap">

  <div class="pix-hero">
    <div>
      <h1>📦 {{ __('messages.products_title') }}</h1>
      <p>
        @if($maxProducts == -1)
          {{ __('messages.products_limit_unlimited') }}
        @else
          {{ __('messages.products_limit_label', ['used' => $counts['all'], 'max' => $maxProducts]) }}
        @endif
      </p>
    </div>
    <a href="{{ route('provider.products.create') }}" class="pix-btn-add">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M12 5v14M5 12h14"/></svg>
      {{ __('messages.products_create_btn') }}
    </a>
  </div>

  <div class="pix-tabs">
    <a href="{{ route('provider.products.index') }}" class="pix-tab {{ !in_array($type, ['shop','rental','wholesale']) ? 'on' : '' }}">
      {{ __('messages.products_filter_all') }} ({{ $counts['all'] }})
    </a>
    <a href="{{ route('provider.products.index', ['type' => 'shop']) }}" class="pix-tab {{ $type === 'shop' ? 'on' : '' }}">
      {{ __('messages.products_filter_shop') }} ({{ $counts['shop'] }})
    </a>
    <a href="{{ route('provider.products.index', ['type' => 'rental']) }}" class="pix-tab {{ $type === 'rental' ? 'on' : '' }}">
      {{ __('messages.products_filter_rental') }} ({{ $counts['rental'] }})
    </a>
    <a href="{{ route('provider.products.index', ['type' => 'wholesale']) }}" class="pix-tab {{ $type === 'wholesale' ? 'on' : '' }}">
      {{ __('messages.products_filter_wholesale') }} ({{ $counts['wholesale'] }})
    </a>
  </div>

  <div class="pix-card">
    @if($products->count() > 0)
      <div style="overflow-x:auto;">
        <table class="pix-tbl">
          <thead>
            <tr>
              <th>{{ __('messages.product_name') }}</th>
              <th>{{ __('messages.product_type') }}</th>
              <th>{{ __('messages.product_price') }}</th>
              <th>{{ __('messages.product_status') }}</th>
              <th style="text-align:right;">{{ __('messages.actions') ?? 'Actions' }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($products as $product)
              <tr>
                <td><div class="pix-name">{{ $product->name }}</div></td>
                <td><span class="pix-type {{ $product->product_type }}">{{ ucfirst($product->product_type) }}</span></td>
                <td style="font-weight:800;">{{ $product->currency ?? 'NPR' }} {{ number_format($product->price) }}</td>
                <td>
                  <span class="pix-pill {{ $product->status === 'active' ? 'on' : 'off' }}">
                    {{ __('messages.product_status_' . $product->status) }}
                  </span>
                </td>
                <td style="text-align:right;">
                  <a href="{{ route('provider.products.edit', $product) }}" class="pix-act pix-act-edit">
                    <i class="fas fa-edit"></i> {{ __('messages.products_edit_btn') }}
                  </a>
                  <form action="{{ route('provider.products.destroy', $product) }}" method="POST" style="display:inline;margin:0;"
                        onsubmit="return confirm('{{ __('messages.products_delete_confirm') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="pix-act pix-act-del">
                      <i class="fas fa-trash"></i> {{ __('messages.products_delete_btn') }}
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="pix-pager">
        {{ $products->links() }}
      </div>
    @else
      <div class="pix-empty">
        <p>{{ __('messages.products_no_items') }}</p>
      </div>
    @endif
  </div>
</div>

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