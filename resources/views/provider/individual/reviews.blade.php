@extends('layouts.provider-individual')
@section('title', __('messages.client_reviews'))
@section('page-title', __('messages.client_reviews'))
@section('page-subtitle', __('messages.reviews_subtitle'))

@push('ind-head')
<style>
  .review-card{background:var(--card); border:1px solid var(--line); border-radius:var(--radius); padding:20px; margin-bottom:15px;}
  .stars{color:#fbbf24; font-size:14px; margin-bottom:10px;}
</style>
@endpush

@section('content')
<div style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
    <h3 style="font-size:16px; font-weight:800;">{{ __('messages.client_reviews') }}</h3>
    <a href="{{ route('provider.individual.dashboard') }}" class="btn-primary" style="background:var(--emerald); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
</div>
@forelse($reviews as $r)
<div class="review-card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <strong style="font-size:15px;">{{ $r->client_name ?? 'Guest' }}</strong>
        <span style="color:var(--muted); font-size:12px;">{{ $r->created_at->diffForHumans() }}</span>
    </div>
    <div class="stars">{!! str_repeat('★', $r->rating) !!}{!! str_repeat('☆', 5 - $r->rating) !!}</div>
    <p style="font-size:13px; color:var(--muted);">{{ $r->comment }}</p>
</div>
@empty
<div style="text-align:center; padding:40px; color:var(--muted);">
    <div style="font-size:40px; margin-bottom:10px;">⭐</div>
    <p>{{ __('messages.no_reviews_yet') }}</p>
</div>
@endforelse
@endsection