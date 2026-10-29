@extends('layouts.provider-art-craft')
@section('title', __('messages.gallery_management'))
@section('page-title', __('messages.gallery_management'))
@section('page-subtitle', __('messages.manage_gallery_subtitle'))

@push('art-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#b3a090;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f8f2ea;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fdf9f5}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
    .pill.p-paid{background:var(--green-soft);color:var(--green)}
  .pill.p-pending{background:var(--amber-soft);color:#a8741a}
  .pill.p-refund{background:var(--red-soft);color:var(--red)}
  .type-badge{font-size:10px;font-weight:800;padding:4px 9px;border-radius:6px;letter-spacing:.3px;display:inline-block}
  .t-painting{background:var(--terracotta-soft);color:var(--terracotta-d)}
  .t-sculpture{background:var(--burgundy-soft);color:var(--burgundy)}
  .t-handicraft{background:var(--sand-soft);color:#a8741a}
  .t-mural{background:var(--green-soft);color:var(--green)}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div>
            <h3 style="font-size:16px; font-weight:800;">{{ __('messages.gallery_management') }}</h3>
            <p style="font-size:12px; color:var(--muted); margin-top:4px;">{{ __('messages.manage_gallery_subtitle') }}</p>
        </div>
        <a href="{{ route('provider.art-craft.dashboard') }}" class="btn-primary" style="background:var(--terracotta); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
    </div>
    
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.artwork_col') }}</th>
                    <th>{{ __('messages.artist_col') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th>{{ __('messages.price_col') }}</th>
                    <th>{{ __('messages.status_col') }}</th>
                    <th style="text-align:right;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($artworks as $a)
                <tr>
                    <td><strong style="color:var(--text);">{{ $a->name }}</strong></td>
                    <td>{{ $a->artist_name }}</td>
                    <td><span class="type-badge t-{{ $a->category }}">{{ __('messages.' . $a->category) ?? ucfirst($a->category) }}</span></td>
                    <td style="font-weight:800">Rs {{ number_format($a->price) }}</td>
                    <td>
                        <span class="pill {{ $a->status === 'available' ? 'p-paid' : ($a->status === 'sold' ? 'p-pending' : 'p-refund') }}">
                            {{ __('messages.' . $a->status) ?? ucfirst($a->status) }}
                        </span>
                    </td>
                    <td style="text-align:right;white-space:nowrap;">
                        <button type="button" onclick="openEditArtworkModal({{ $a->id }}, '{{ addslashes($a->name) }}', '{{ addslashes($a->artist_name ?? '') }}', '{{ $a->category }}', {{ (float) $a->price }}, '{{ $a->status }}', '{{ addslashes($a->emoji ?? '') }}')" style="padding:6px 10px;border-radius:7px;border:none;background:var(--terracotta-soft);color:var(--terracotta-d);font-size:11px;font-weight:800;cursor:pointer;font-family:inherit;margin-right:4px;"><i class="fas fa-edit"></i> {{ __('messages.edit') }}</button>
                        <form action="{{ route('provider.art-craft.artworks.destroy', $a) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('{{ __('messages.delete_artwork_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:6px 10px;border-radius:7px;border:none;background:var(--red-soft);color:var(--red);font-size:11px;font-weight:800;cursor:pointer;font-family:inherit;"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_artworks_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Edit Artwork Modal --}}
<div id="editArtworkModal" style="display:none;position:fixed;inset:0;background:rgba(30,22,18,.65);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
  <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:520px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">{{ __('messages.edit_artwork') }}</h3>
      <button type="button" onclick="closeEditArtworkModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
    </div>
    <form id="editArtworkForm" method="POST">
      @csrf @method('PUT')
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.artwork_name') }}</label>
        <input type="text" name="name" id="ea_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.artist_name') }}</label>
          <input type="text" name="artist_name" id="ea_artist" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
        </div>
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.category') }}</label>
          <select name="category" id="ea_category" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            <option value="painting">Painting</option>
            <option value="sculpture">Sculpture</option>
            <option value="handicraft">Handicraft</option>
            <option value="mural">Mural</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.price') }} (Rs)</label>
          <input type="number" name="price" id="ea_price" step="0.01" min="0" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
        </div>
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.status') }}</label>
          <select name="status" id="ea_status" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            <option value="available">Available</option>
            <option value="sold">Sold</option>
            <option value="custom">Custom</option>
          </select>
        </div>
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.emoji') }}</label>
        <input type="text" name="emoji" id="ea_emoji" maxlength="10" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
      </div>
      <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--terracotta,#c65d3b),var(--terracotta-d,#a8492b));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">{{ __('messages.update_artwork') }}</button>
    </form>
  </div>
</div>

<script>
function openEditArtworkModal(id, name, artist, category, price, status, emoji) {
    document.getElementById('editArtworkForm').action = '/provider/art-craft/artworks/' + id;
    document.getElementById('ea_name').value = name;
    document.getElementById('ea_artist').value = artist;
    document.getElementById('ea_category').value = category;
    document.getElementById('ea_price').value = price;
    document.getElementById('ea_status').value = status;
    document.getElementById('ea_emoji').value = emoji;
    document.getElementById('editArtworkModal').style.display = 'flex';
}
function closeEditArtworkModal() { document.getElementById('editArtworkModal').style.display = 'none'; }
document.getElementById('editArtworkModal').addEventListener('click', function(e) { if (e.target === this) closeEditArtworkModal(); });
</script>
@endsection