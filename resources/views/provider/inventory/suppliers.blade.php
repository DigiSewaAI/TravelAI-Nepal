@extends($provider_layout ?? 'layouts.provider-inventory')
@section('title', __('messages.suppliers_management'))
@section('page-title', __('messages.suppliers_management'))
@section('page-subtitle', __('messages.manage_suppliers_desc'))

@section('content')
<style>
  .sup-hero{background:linear-gradient(135deg,var(--accent,#6366f1),var(--accent-d,#4f46e5));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .sup-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .sup-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .sup-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .sup-btn{display:inline-flex;align-items:center;gap:7px;padding:11px 18px;border-radius:11px;background:rgba(255,255,255,.96);color:var(--accent-d,#4f46e5);font-weight:800;font-size:13px;text-decoration:none;position:relative;z-index:1;box-shadow:0 4px 14px rgba(0,0,0,.15);}
  .sup-card{background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:16px;overflow:hidden;}
  .sup-empty{padding:60px 24px;text-align:center;color:var(--muted,#6b7280);}
  .sup-empty svg{width:56px;height:56px;margin:0 auto 14px;opacity:.35;stroke-width:1.5;}
  .sup-empty h3{font-size:16px;font-weight:800;color:var(--text,#111827);margin:0 0 6px;}
  .sup-empty p{font-size:13px;margin:0 0 18px;}
  .sup-empty a{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:10px;background:var(--accent,#6366f1);color:#fff;font-weight:800;font-size:13px;text-decoration:none;}
</style>

<div class="sup-hero">
  <div>
    <h1>🚚 {{ __('messages.suppliers_management') }}</h1>
    <p>{{ __('messages.manage_suppliers_desc') }}</p>
  </div>
  <a href="#" class="sup-btn" onclick="alert('Supplier form coming soon — DB migration required');return false;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M12 5v14M5 12h14"/></svg>
    {{ __('messages.add_supplier') }}
  </a>
</div>

<div class="sup-card">
  @if($suppliers->count())
    <div style="padding:20px;">
      @foreach($suppliers as $s)
        <div style="padding:14px 0;border-bottom:1px solid var(--line,#e7eaf3);">{{ $s->name ?? 'Supplier' }}</div>
      @endforeach
    </div>
  @else
    <div class="sup-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17h16"/><path d="M6 17V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg>
      <h3>{{ __('messages.no_suppliers_found') }}</h3>
      <p>Supplier management will be available after DB migration.</p>
      <a href="#" onclick="alert('Supplier form coming soon — DB migration required');return false;">+ {{ __('messages.add_supplier') }}</a>
    </div>
  @endif
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