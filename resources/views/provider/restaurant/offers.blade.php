@extends('layouts.provider-restaurant')
@section('title', __('messages.offers_management'))
@section('page-title', __('messages.offers_management'))
@section('page-subtitle', __('messages.manage_offers_desc'))

@push('restaurant-head')
<style>
  .card{background:var(--card); border:1px solid var(--line); border-radius:var(--radius); padding:20px;}
  .tbl{width:100%; border-collapse:collapse;}
  .tbl th{text-align:left; font-size:11px; text-transform:uppercase; color:#9ca3af; padding:0 15px 15px; border-bottom:1px solid var(--line);}
  .tbl td{padding:15px; font-size:13px; border-bottom:1px solid #f3f5fb;}
  .pill{font-size:11px; padding:5px 10px; border-radius:20px;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; margin-bottom:15px;">
        <h3 style="font-size:16px; font-weight:800;">{{ __('messages.offers_management') }}</h3>
        <a href="{{ route('provider.restaurant.dashboard') }}" class="btn-primary" style="background:var(--accent); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
    </div>
    <table class="tbl">
        <thead><tr><th>{{ __('messages.offer_title') }}</th><th>{{ __('messages.discount') }}</th><th>{{ __('messages.valid_until') }}</th><th>{{ __('messages.status') }}</th></tr></thead>
        <tbody>
            @foreach($offers as $o)
            <tr>
                <td><strong>{{ $o->title }}</strong></td>
                <td style="font-weight:800; color:var(--accent);">{{ $o->discount }}</td>
                <td>{{ $o->valid_until->format('M d, Y') }}</td>
                <td>
                    @if($o->status === 'active')
                        <span class="pill" style="background:var(--green-soft); color:var(--green);">{{ __('messages.active') }}</span>
                    @else
                        <span class="pill" style="background:var(--red-soft); color:var(--red);">{{ __('messages.inactive') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection