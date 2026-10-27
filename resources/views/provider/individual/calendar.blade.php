@extends('layouts.provider-individual')
@section('title', __('messages.full_calendar'))
@section('page-title', __('messages.full_calendar'))
@section('page-subtitle', __('messages.calendar_subtitle'))

@section('content')
<div style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
    <h3 style="font-size:16px; font-weight:800;">{{ __('messages.full_calendar') }}</h3>
    <a href="{{ route('provider.individual.dashboard') }}" class="btn-primary" style="background:var(--emerald); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
</div>
<div class="card" style="text-align:center; padding:50px;">
    <h3 style="font-size:20px; font-weight:800; margin-bottom:10px;">{{ __('messages.full_calendar') }}</h3>
    <p style="color:var(--muted);">(Interactive Calendar View - Coming in Phase 2)</p>
</div>
@endsection