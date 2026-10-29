@extends('layouts.provider-art-craft')
@section('title', __('messages.orders_management'))
@section('page-title', __('messages.orders_management'))
@section('page-subtitle', __('messages.manage_custom_orders_subtitle'))

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
            <h3 style="font-size:16px; font-weight:800;">{{ __('messages.orders_management') }}</h3>
            <p style="font-size:12px; color:var(--muted); margin-top:4px;">{{ __('messages.manage_custom_orders_subtitle') }}</p>
        </div>
        <a href="{{ route('provider.art-craft.dashboard') }}" class="btn-primary" style="background:var(--terracotta); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
    </div>
    
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.client_col') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th>{{ __('messages.budget_col') }}</th>
                    <th>{{ __('messages.deadline_col') }}</th>
                    <th>{{ __('messages.status_col') }}</th>
                    <th style="text-align:right;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                <tr>
                    <td><strong>{{ $o->client_name }}</strong><br><small style="color:var(--muted)">{{ $o->client_email }}</small></td>
                    <td><span class="type-badge t-{{ strtolower($o->type) }}">{{ $o->type }}</span></td>
                    <td style="font-weight:800">Rs {{ number_format($o->budget) }}</td>
                    <td>{{ $o->deadline->format('M d, Y') }}</td>
                    <td>
                        <form action="{{ route('provider.art-craft.orders.status', $o) }}" method="POST" style="display:inline;margin:0;">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()" style="padding:5px 8px;border-radius:6px;border:1px solid var(--line);font-size:11px;font-weight:700;font-family:inherit;cursor:pointer;background:var(--card);color:var(--text);">
                                <option value="pending" {{ $o->status === 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                                <option value="progress" {{ $o->status === 'progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                                <option value="done" {{ $o->status === 'done' ? 'selected' : '' }}>{{ __('messages.done') ?? 'Done' }}</option>
                            </select>
                        </form>
                    </td>
                    <td style="text-align:right;white-space:nowrap;">
                        <form action="{{ route('provider.art-craft.orders.destroy', $o) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('{{ __('messages.delete_order_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:6px 10px;border-radius:7px;border:none;background:var(--red-soft);color:var(--red);font-size:11px;font-weight:800;cursor:pointer;font-family:inherit;"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_custom_orders_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection