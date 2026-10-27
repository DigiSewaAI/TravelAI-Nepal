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
        <a href="{{ route('provider.art-craft.dashboard') }}" class="btn-primary" style="background:var(--terracotta); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">Back to Dashboard</a>
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
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_artworks_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection