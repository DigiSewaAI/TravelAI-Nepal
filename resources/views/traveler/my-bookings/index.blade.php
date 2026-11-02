@extends((request('ctx') === 'provider' && !empty($provider_layout)) ? $provider_layout : 'layouts.public')
@section('title', 'My Bookings | TravelAI Nepal')
@section('page-title', 'My Bookings')
@section('page-subtitle', 'All your activity across TravelAI Nepal')
@section('content')
<div style="display:flex;flex-direction:column;gap:18px;color:var(--text,#111827)">

    {{-- Hero --}}
    <div style="background:linear-gradient(135deg,var(--accent,#6366f1) 0%,var(--accent-d,#4f46e5) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <h2 style="font-size:22px;font-weight:800;margin:0;color:#fff">My Bookings</h2>
        <p style="font-size:13px;opacity:.88;margin-top:6px">All your activity across TravelAI Nepal</p>
    </div>

    {{-- Tabs --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        @foreach(['all'=>'All','treks'=>'Treks','shop'=>'Shop','rental'=>'Rental','wholesale'=>'Wholesale','rfqs'=>'RFQs'] as $key => $label)
            @php $active = ($tab === $key); @endphp
            <a href="{{ route('traveler.my-bookings', array_merge(['tab'=>$key], request('ctx') ? ['ctx'=>request('ctx')] : [])) }}"
               style="padding:9px 16px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;transition:.2s;{{ $active ? 'background:var(--accent,#6366f1);color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.12)' : 'background:var(--card,#fff);color:var(--muted,#6b7280);border:1px solid var(--line,#e5e7eb)' }}">
                {{ $label }} ({{ $counts[$key] }})
            </a>
        @endforeach
    </div>

    {{-- Content --}}
    <div style="display:flex;flex-direction:column;gap:12px">
        @if($tab === 'all' || $tab === 'treks')
            @foreach($treks as $b)
                <a href="{{ route('traveler.bookings.show', $b->id) }}"
                   style="display:block;background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:var(--radius,18px);padding:18px 20px;text-decoration:none;color:inherit;transition:.2s"
                   onmouseover="this.style.boxShadow='0 10px 24px rgba(0,0,0,.08)'"
                   onmouseout="this.style.boxShadow='none'">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
                        <div style="min-width:0;flex:1">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">
                                <span style="padding:3px 9px;border-radius:6px;background:#e7f8f1;color:#059669;font-size:10px;font-weight:800;letter-spacing:.4px">TREK</span>
                                <span style="font-weight:800;font-size:14px;color:var(--text,#111827)">{{ $b->service->name ?? $b->booking_number }}</span>
                            </div>
                            <div style="font-size:12px;color:var(--muted,#6b7280)">{{ $b->created_at->format('M d, Y') }}</div>
                        </div>
                        @php $st = ['confirmed'=>['#e7f8f1','#059669'],'pending'=>['#fef4e2','#b45309'],'completed'=>['#f3f4f6','#6b7280'],'cancelled'=>['#fdecec','#dc2626']][$b->status] ?? ['#f3f4f6','#6b7280']; @endphp
                        <span style="padding:5px 12px;border-radius:8px;background:{{ $st[0] }};color:{{ $st[1] }};font-size:11px;font-weight:800;text-transform:uppercase;flex-shrink:0">{{ $b->status }}</span>
                    </div>
                </a>
            @endforeach
        @endif

        @php
            $tabOrders = collect();
            if ($tab === 'shop') $tabOrders = $shopOrders;
            elseif ($tab === 'rental') $tabOrders = $rentalOrders;
            elseif ($tab === 'wholesale') $tabOrders = $wholesaleOrders;
            elseif ($tab === 'all') $tabOrders = $shopOrders->merge($rentalOrders)->merge($wholesaleOrders);
        @endphp
        @foreach($tabOrders as $o)
            @php
                $type = $o->items->first()->product_type ?? 'shop';
                $typeColor = ['shop'=>['#f2ecfe','#7c3aed'],'rental'=>['#e9f1fe','#2563eb'],'wholesale'=>['#fef4e2','#b45309']][$type] ?? ['#f3f4f6','#6b7280'];
                $statusColor = ['confirmed'=>['#e9f1fe','#2563eb'],'pending'=>['#fef4e2','#b45309'],'shipped'=>['#f2ecfe','#7c3aed'],'delivered'=>['#e7f8f1','#059669'],'returned'=>['#f3f4f6','#6b7280'],'cancelled'=>['#fdecec','#dc2626']][$o->status] ?? ['#f3f4f6','#6b7280'];
            @endphp
            <a href="{{ route('orders.show', $o) }}"
               style="display:block;background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:var(--radius,18px);padding:18px 20px;text-decoration:none;color:inherit;transition:.2s"
               onmouseover="this.style.boxShadow='0 10px 24px rgba(0,0,0,.08)'"
               onmouseout="this.style.boxShadow='none'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
                    <div style="min-width:0;flex:1">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">
                            <span style="padding:3px 9px;border-radius:6px;background:{{ $typeColor[0] }};color:{{ $typeColor[1] }};font-size:10px;font-weight:800;letter-spacing:.4px">{{ strtoupper($type) }}</span>
                            <span style="font-weight:800;font-size:14px;color:var(--text,#111827);font-family:monospace">{{ $o->order_number }}</span>
                        </div>
                        <div style="font-size:12px;color:var(--muted,#6b7280)">{{ $o->created_at->format('M d, Y') }} · {{ $o->items->count() }} items</div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <span style="padding:5px 12px;border-radius:8px;background:{{ $statusColor[0] }};color:{{ $statusColor[1] }};font-size:11px;font-weight:800;text-transform:uppercase;display:inline-block">{{ $o->status }}</span>
                        <div style="font-weight:800;color:var(--accent,#6366f1);margin-top:6px;font-size:14px">Rs {{ number_format($o->total, 2) }}</div>
                    </div>
                </div>
            </a>
        @endforeach

        @if($tab === 'all' || $tab === 'rfqs')
            @foreach($rfqs as $r)
                @php $rStatus = ['quoted'=>['#e9f1fe','#2563eb'],'pending'=>['#fef4e2','#b45309'],'accepted'=>['#e7f8f1','#059669'],'rejected'=>['#fdecec','#dc2626']][$r->status] ?? ['#f3f4f6','#6b7280']; @endphp
                <a href="{{ route('wholesale.rfq.show', $r) }}"
                   style="display:block;background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:var(--radius,18px);padding:18px 20px;text-decoration:none;color:inherit;transition:.2s"
                   onmouseover="this.style.boxShadow='0 10px 24px rgba(0,0,0,.08)'"
                   onmouseout="this.style.boxShadow='none'">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
                        <div style="min-width:0;flex:1">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">
                                <span style="padding:3px 9px;border-radius:6px;background:#fff2e6;color:#ea580c;font-size:10px;font-weight:800;letter-spacing:.4px">RFQ</span>
                                <span style="font-weight:800;font-size:14px;color:var(--text,#111827);font-family:monospace">{{ $r->rfq_number }}</span>
                            </div>
                            <div style="font-size:12px;color:var(--muted,#6b7280)">{{ $r->product->name ?? 'N/A' }} · Qty {{ $r->quantity }}</div>
                        </div>
                        <span style="padding:5px 12px;border-radius:8px;background:{{ $rStatus[0] }};color:{{ $rStatus[1] }};font-size:11px;font-weight:800;text-transform:uppercase;flex-shrink:0">{{ $r->status }}</span>
                    </div>
                </a>
            @endforeach
        @endif

        @if(
            ($tab === 'all' && $counts['all'] === 0) ||
            ($tab === 'treks' && $counts['treks'] === 0) ||
            ($tab === 'shop' && $counts['shop'] === 0) ||
            ($tab === 'rental' && $counts['rental'] === 0) ||
            ($tab === 'wholesale' && $counts['wholesale'] === 0) ||
            ($tab === 'rfqs' && $counts['rfqs'] === 0)
        )
            <div style="text-align:center;padding:60px 20px;background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:var(--radius,18px);color:var(--muted,#6b7280)">
                <p style="margin:0 0 16px;font-size:14px">No bookings in this category yet.</p>
                <a href="{{ route('public.services.index') }}" style="display:inline-block;background:var(--accent,#6366f1);color:#fff;padding:10px 22px;border-radius:10px;text-decoration:none;font-weight:700;font-size:13px">Explore Services</a>
            </div>
        @endif
    </div>

</div>
@endsection