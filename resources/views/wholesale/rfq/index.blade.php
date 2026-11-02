@extends((request('ctx') === 'provider' && !empty($provider_layout)) ? $provider_layout : 'layouts.public')
@section('title', 'My Wholesale RFQs | TravelAI Nepal')
@section('page-title', 'My Wholesale RFQs')
@section('page-subtitle', 'All your wholesale quote requests')
@section('content')
@php
    $__total = method_exists($rfqs, 'total') ? $rfqs->total() : $rfqs->count();
    $__statusCounts = [
        'all' => $__total,
        'pending' => $rfqs->where('status','pending')->count(),
        'quoted' => $rfqs->where('status','quoted')->count(),
        'accepted' => $rfqs->where('status','accepted')->count(),
    ];
@endphp

<div style="display:flex;flex-direction:column;gap:18px;color:var(--text,#111827)">

    <div style="background:linear-gradient(135deg,var(--accent,#6366f1) 0%,var(--accent-d,#4f46e5) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <h2 style="font-size:22px;font-weight:800;margin:0;color:#fff">My Wholesale RFQs</h2>
        <p style="font-size:13px;opacity:.88;margin-top:6px">{{ $__total }} quote requests</p>
    </div>

    @if($rfqs->isEmpty())
        <div style="text-align:center;padding:60px 20px;background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:var(--radius,18px);color:var(--muted,#6b7280)">
            <p style="margin:0 0 16px;font-size:14px">No RFQs yet.</p>
            <a href="{{ route('public.wholesale.index') }}" style="display:inline-block;background:var(--accent,#6366f1);color:#fff;padding:10px 22px;border-radius:10px;text-decoration:none;font-weight:700;font-size:13px">Browse Wholesale</a>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:12px">
            @foreach($rfqs as $rfq)
                @php
                    $sc = match($rfq->status) {
                        'pending' => ['#fef4e2','#b45309'],
                        'quoted' => ['#e9f1fe','#2563eb'],
                        'accepted' => ['#e7f8f1','#059669'],
                        'rejected' => ['#fdecec','#dc2626'],
                        'expired','cancelled' => ['#f3f4f6','#6b7280'],
                        default => ['#f3f4f6','#6b7280'],
                    };
                @endphp
                <a href="{{ route('wholesale.rfq.show', $rfq) }}"
                   style="display:block;background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:var(--radius,18px);padding:20px;text-decoration:none;color:inherit;transition:.2s"
                   onmouseover="this.style.boxShadow='0 10px 24px rgba(0,0,0,.08)'"
                   onmouseout="this.style.boxShadow='none'">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
                        <div style="min-width:0;flex:1">
                            <div style="font-weight:800;font-size:15px;color:var(--text,#111827);font-family:monospace">{{ $rfq->rfq_number }}</div>
                            <div style="font-size:12px;color:var(--muted,#6b7280);margin-top:4px">
                                {{ $rfq->product->name ?? 'N/A' }} × {{ $rfq->quantity }}
                                · {{ $rfq->provider->name ?? 'N/A' }}
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0">
                            <span style="display:inline-block;padding:5px 12px;border-radius:8px;background:{{ $sc[0] }};color:{{ $sc[1] }};font-size:11px;font-weight:800;text-transform:uppercase">{{ $rfq->status }}</span>
                            @if($rfq->quoted_total)
                                <div style="font-weight:800;color:var(--accent,#6366f1);margin-top:6px;font-size:14px">NPR {{ number_format($rfq->quoted_total, 2) }}</div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        @if(method_exists($rfqs, 'links'))
            <div style="margin-top:16px">{{ $rfqs->links() }}</div>
        @endif
    @endif
</div>
@endsection