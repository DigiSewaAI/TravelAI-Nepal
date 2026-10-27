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
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div><h3 style="font-size:16px; font-weight:800;">{{ __('messages.menu_management') }}</h3></div>
        <div style="display:flex; gap:10px;">
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
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_menu_items_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection