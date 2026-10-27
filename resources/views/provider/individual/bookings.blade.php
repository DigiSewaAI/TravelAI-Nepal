@extends('layouts.provider-individual')
@section('title', __('messages.my_bookings'))
@section('page-title', __('messages.my_bookings'))
@section('page-subtitle', __('messages.manage_assignments_subtitle'))

@push('ind-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f3f5fb;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafbff}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div><h3 style="font-size:16px; font-weight:800;">{{ __('messages.my_bookings') }}</h3></div>
        <a href="{{ route('provider.individual.dashboard') }}" class="btn-primary" style="background:var(--emerald); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
    </div>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.assignment_title') }}</th>
                    <th>{{ __('messages.client_name') }}</th>
                    <th>{{ __('messages.service_type') }}</th>
                    <th>{{ __('messages.start_date') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr>
                    <td><strong>{{ $b->title }}</strong></td>
                    <td>{{ $b->client_name }}</td>
                    <td><span class="pill" style="background:var(--emerald-soft); color:var(--emerald-d);">{{ __('messages.' . $b->service_type) ?? ucfirst($b->service_type) }}</span></td>
                    <td>{{ $b->start_date->format('M d, Y') }}</td>
                    <td style="font-weight:800">Rs {{ number_format($b->amount) }}</td>
                    <td><span class="pill" style="background:var(--amber-soft); color:#b45309;">{{ __('messages.' . $b->status) ?? ucfirst($b->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_bookings_yet') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection