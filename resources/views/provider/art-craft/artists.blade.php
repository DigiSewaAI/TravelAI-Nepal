@extends($provider_layout ?? 'layouts.provider-art-craft')
@section('title', 'Artists')
@section('page-title', 'Artists')
@section('page-subtitle', 'All artists contributing to your gallery')

@section('content')
<style>
  .art-hero{background:linear-gradient(135deg,var(--accent,#c65d3b),var(--accent-d,#a8492b));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .art-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .art-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .art-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .art-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px;}
  @media(max-width:768px){.art-stats{grid-template-columns:1fr;}}
  .art-stat{background:var(--card,#fff);border:1px solid var(--line,#ede1d3);border-radius:14px;padding:18px;display:flex;align-items:center;gap:12px;}
  .art-ic{width:42px;height:42px;border-radius:12px;background:var(--accent-soft,#f8e5da);color:var(--accent,#c65d3b);display:grid;place-items:center;font-size:18px;flex-shrink:0;}
  .art-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#8a7565);font-weight:800;}
  .art-val{font-size:20px;font-weight:800;color:var(--text,#2b1e16);margin-top:2px;}
  .art-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
  .artist-card{background:var(--card,#fff);border:1px solid var(--line,#ede1d3);border-radius:16px;padding:22px;transition:.2s;}
  .artist-card:hover{box-shadow:0 12px 28px -12px rgba(198,93,59,.25);border-color:var(--accent,#c65d3b);transform:translateY(-2px);}
  .artist-av{width:54px;height:54px;border-radius:14px;background:linear-gradient(135deg,var(--accent,#c65d3b),var(--accent-d,#a8492b));color:#fff;display:grid;place-items:center;font-size:22px;font-weight:900;margin-bottom:14px;box-shadow:0 6px 16px -6px rgba(198,93,59,.45);}
  .artist-name{font-size:16px;font-weight:800;color:var(--text,#2b1e16);margin:0 0 4px;letter-spacing:-.2px;}
  .artist-meta{font-size:12px;color:var(--muted,#8a7565);font-weight:600;margin-bottom:14px;}
  .artist-rows{display:grid;gap:8px;padding-top:12px;border-top:1px dashed var(--line,#ede1d3);}
  .artist-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;}
  .artist-row .k{color:var(--muted,#8a7565);font-weight:600;}
  .artist-row .v{font-weight:800;color:var(--text,#2b1e16);}
  .artist-row .v.accent{color:var(--accent,#c65d3b);}
  .art-empty{text-align:center;padding:48px 20px;color:var(--muted,#8a7565);}
  .art-empty svg{width:56px;height:56px;margin:0 auto 12px;opacity:.4;stroke-width:1.5;}
  .art-empty h3{font-size:15px;font-weight:800;color:var(--text,#2b1e16);margin:0 0 6px;}
</style>

<div class="art-hero">
  <h1>🎨 Artists</h1>
  <p>All artists contributing to your gallery</p>
</div>

<div class="art-stats">
  <div class="art-stat"><div class="art-ic">🎨</div><div><div class="art-lbl">Total Artists</div><div class="art-val">{{ $totalArtists }}</div></div></div>
  <div class="art-stat"><div class="art-ic">🖼️</div><div><div class="art-lbl">Total Artworks</div><div class="art-val">{{ $totalArtworks }}</div></div></div>
  <div class="art-stat"><div class="art-ic">👥</div><div><div class="art-lbl">Total Clients</div><div class="art-val">{{ $clients }}</div></div></div>
</div>

@if($artists->count())
  <div class="art-grid">
    @foreach($artists as $a)
      <div class="artist-card">
        <div class="artist-av">{{ strtoupper(substr($a->artist_name, 0, 1)) }}</div>
        <h3 class="artist-name">{{ $a->artist_name }}</h3>
        <div class="artist-meta">Contributing artist</div>
        <div class="artist-rows">
          <div class="artist-row"><span class="k">Artworks</span><span class="v">{{ $a->artwork_count }}</span></div>
          <div class="artist-row"><span class="k">Sold</span><span class="v">{{ $a->sold_count }}</span></div>
          <div class="artist-row"><span class="k">Revenue</span><span class="v accent">Rs {{ number_format($a->revenue) }}</span></div>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="art-empty">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    <h3>No artists yet</h3>
    <p>Add artworks with artist names to see them here.</p>
  </div>
@endif
@endsection