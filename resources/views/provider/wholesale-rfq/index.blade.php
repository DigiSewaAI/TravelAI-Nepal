@extends($provider_layout ?? 'layouts.provider-inventory')

@section('title', __('messages.provider_rfq_title'))
@section('page-title', __('messages.provider_rfq_title'))
@section('page-subtitle', 'Wholesale quote requests from buyers')

@section('content')
@php
    $statusColors = [
        'pending'   => ['#fef4e2', '#b45309'],
        'quoted'    => ['#e9f1fe', '#2563eb'],
        'accepted'  => ['#e7f8f1', '#059669'],
        'rejected'  => ['#fdecec', '#dc2626'],
        'expired'   => ['#f3f4f6', '#6b7280'],
        'cancelled' => ['#f3f4f6', '#6b7280'],
    ];
@endphp

<div style="display:flex;flex-direction:column;gap:18px">

    {{-- Hero --}}
    <div style="background:linear-gradient(135deg,var(--accent,#6366f1) 0%,var(--accent-d,#4f46e5) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <div style="min-width:0">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <h2 style="font-size:22px;font-weight:800;letter-spacing:-.5px;margin:0;color:#fff">{{ __('messages.provider_rfq_title') }}</h2>
                <span style="background:rgba(255,255,255,.22);padding:4px 10px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:.4px">{{ $rfqs->total() }} TOTAL</span>
            </div>
            <p style="font-size:13px;opacity:.88;margin-top:6px;font-weight:500">Wholesale quote requests from buyers</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'quoted' => 'Quoted', 'accepted' => 'Accepted'] as $key => $label)
            @php $active = $status === $key; @endphp
            <a href="{{ route('provider.wholesale-rfq.index', ['status' => $key]) }}"
               style="padding:9px 16px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;transition:.2s;{{ $active ? 'background:var(--accent,#6366f1);color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.12)' : 'background:var(--card,#fff);color:var(--muted,#6b7280);border:1px solid var(--line,#e7eaf3)' }}">
                {{ $label }} ({{ $counts[$key] ?? 0 }})
            </a>
        @endforeach
    </div>

    {{-- Table card --}}
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
        @if($rfqs->count() > 0)
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="border-bottom:2px solid var(--line,#e7eaf3)">
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280)">RFQ</th>
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280)">Buyer</th>
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280)">Product</th>
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280)">Qty</th>
                            <th style="text-align:left;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280)">Status</th>
                            <th style="text-align:right;padding:12px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280)">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rfqs as $rfq)
                            @php [$bg, $fg] = $statusColors[$rfq->status] ?? ['#f3f4f6', '#6b7280']; @endphp
                            <tr style="border-bottom:1px solid var(--line,#e7eaf3)">
                                <td style="padding:14px 8px;font-size:13px;font-weight:800;color:var(--accent-d,#4f46e5);font-family:monospace">{{ $rfq->rfq_number }}</td>
                                <td style="padding:14px 8px;font-size:13px;color:var(--text,#111827)">{{ $rfq->buyer->name ?? 'N/A' }}</td>
                                <td style="padding:14px 8px;font-size:13px;color:var(--text,#111827)">{{ $rfq->product->name ?? 'N/A' }}</td>
                                <td style="padding:14px 8px;font-size:13px;font-weight:700;color:var(--text,#111827)">{{ $rfq->quantity }}</td>
                                <td style="padding:14px 8px">
                                    <span style="padding:5px 10px;border-radius:8px;background:{{ $bg }};color:{{ $fg }};font-size:11px;font-weight:800;text-transform:uppercase">{{ $rfq->status }}</span>
                                </td>
                                <td style="padding:14px 8px;text-align:right">
                                    <a href="{{ route('provider.wholesale-rfq.show', $rfq) }}"
                                       style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;background:var(--accent-soft,#eef0fe);color:var(--accent-d,#4f46e5);font-size:12px;font-weight:800;text-decoration:none">
                                        View
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px">{{ $rfqs->links() }}</div>
        @else
            <div style="text-align:center;padding:60px 20px;color:var(--muted,#6b7280)">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.35;margin:0 auto 14px;display:block"><path d="M4 4h16v16H4z"/><path d="M4 9h16"/><path d="M9 21V9"/></svg>
                <h3 style="font-size:16px;font-weight:800;color:var(--text,#111827);margin:0 0 6px">{{ __('messages.provider_rfq_empty') }}</h3>
                <p style="font-size:13px;margin:0">New wholesale requests will appear here.</p>
            </div>
        @endif
    </div>

</div>
@endsection