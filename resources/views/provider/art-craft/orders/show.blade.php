@extends($provider_layout ?? 'layouts.provider-art-craft')
@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)
@section('page-subtitle', local_time($order->created_at, 'M d, Y H:i'))
@section('content')
@php
    $sc = ['pending'=>['#fbf1dc','#b45309'],'in_progress'=>['#fbf1dc','#b45309'],'completed'=>['#e8f0e2','#4d6b3f'],'cancelled'=>['#fbe3df','#b54434']];
    [$sBg,$sFg] = $sc[$order->status] ?? ['#f3f4f6','#6b7280'];

    // Timeline steps depend on request_type
    $steps = $order->request_type === 'ready_made'
        ? ['pending', 'accepted', 'ready_for_delivery', 'completed']
        : ['pending', 'under_review', 'quoted', 'accepted', 'in_progress', 'ready_for_delivery', 'completed'];

    $currentIdx = array_search($order->status, $steps);
    if ($currentIdx === false) { $currentIdx = -1; } // rejected/cancelled/revision
    $isTerminated = in_array($order->status, ['rejected', 'cancelled'], true);

    // Production action buttons (only for provider-moving forward)
    $nextAction = null;
    if ($order->request_type === 'ready_made') {
        if ($order->status === 'pending') {
            $nextAction = ['label' => __('messages.artcraft_confirm_availability'), 'endpoint' => 'confirm-availability', 'color' => '#7c3aed'];
        } elseif ($order->status === 'accepted') {
            $nextAction = ['label' => __('messages.artcraft_mark_ready_for_delivery'), 'status' => 'ready_for_delivery', 'color' => '#2563eb'];
        } elseif ($order->status === 'ready_for_delivery') {
            $nextAction = ['label' => __('messages.artcraft_mark_completed'), 'status' => 'completed', 'color' => '#059669'];
        }
    } else {
        if ($order->status === 'accepted') {
            $nextAction = ['label' => __('messages.artcraft_start_production'), 'status' => 'in_progress', 'color' => '#2563eb'];
        } elseif ($order->status === 'in_progress') {
            $nextAction = ['label' => __('messages.artcraft_mark_ready_for_delivery'), 'status' => 'ready_for_delivery', 'color' => '#7c3aed'];
        } elseif ($order->status === 'ready_for_delivery') {
            $nextAction = ['label' => __('messages.artcraft_mark_completed'), 'status' => 'completed', 'color' => '#059669'];
        }
    }
@endphp
<div style="display:flex;flex-direction:column;gap:18px">
    <a href="{{ route('provider.art-craft.orders') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--accent,#c65d3b);font-weight:700;text-decoration:none;font-size:13px;width:max-content">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        All Orders
    </a>

    {{-- Hero --}}
    <div style="background:linear-gradient(135deg,var(--accent,#c65d3b) 0%,var(--accent-d,#a8492b) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <div>
            <div style="font-size:22px;font-weight:800">Order #{{ $order->id }}</div>
            <div style="font-size:13px;opacity:.88;margin-top:4px">{{ ucfirst($order->type) }} · {{ local_time($order->created_at, 'M d, Y H:i') }}</div>
            <div style="font-size:11px;opacity:.85;margin-top:6px;text-transform:uppercase;letter-spacing:.5px;">{{ $order->request_type === 'ready_made' ? __('messages.artcraft_ready_made') : __('messages.artcraft_custom_commission') }} · {{ $order->booking_number }}</div>
        </div>
        <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ str_replace('_', ' ', $order->status) }}</span>
    </div>

    {{-- Timeline --}}
    @if(!$isTerminated && $currentIdx >= 0)
    <div style="background:#fff;border:1px solid #ede1d3;border-radius:18px;padding:22px;">
        <h3 style="font-size:14px;font-weight:800;margin:0 0 16px;color:#2b1e16;">{{ __('messages.artcraft_order_timeline') }}</h3>
        <div style="display:flex;gap:0;overflow-x:auto;padding-bottom:6px;">
            @foreach($steps as $idx => $step)
            @php
                $isDone = $idx <= $currentIdx;
                $isCurrent = $idx === $currentIdx;
                $bg = $isDone ? '#7c3aed' : '#e5e7eb';
                $fg = $isDone ? '#fff' : '#9ca3af';
                $label = ucwords(str_replace('_', ' ', $step));
            @endphp
            <div style="flex:1;min-width:100px;text-align:center;position:relative;">
                <div style="width:32px;height:32px;border-radius:50%;background:{{ $bg }};color:{{ $fg }};display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;{{ $isCurrent ? 'box-shadow:0 0 0 4px rgba(124,58,237,.2);' : '' }}">{{ $idx + 1 }}</div>
                <div style="font-size:11px;font-weight:{{ $isCurrent ? '800' : '600' }};color:{{ $isCurrent ? '#7c3aed' : ($isDone ? '#374151' : '#9ca3af') }};margin-top:6px;line-height:1.3;">{{ $label }}</div>
                @if($idx < count($steps) - 1)
                <div style="position:absolute;top:16px;left:calc(50% + 20px);right:calc(-50% + 20px);height:2px;background:{{ $idx < $currentIdx ? '#7c3aed' : '#e5e7eb' }};"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Client + Details --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <div style="background:#fff;border:1px solid #ede1d3;border-radius:18px;padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:#2b1e16;">{{ __('messages.client') }}</h3>
            <div style="font-size:14px;font-weight:700">{{ $order->client_name }}</div>
            @if($order->client_email)<div style="font-size:13px;color:#8a7565;margin-top:4px">{{ $order->client_email }}</div>@endif
        </div>
        <div style="background:#fff;border:1px solid #ede1d3;border-radius:18px;padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:#2b1e16;">{{ __('messages.details') }}</h3>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:#8a7565">{{ __('messages.type') }}</span><span style="font-weight:700">{{ ucfirst($order->type) }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:#8a7565">{{ __('messages.deadline') }}</span><span style="font-weight:700">{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('M d, Y') : 'N/A' }}</span></div>
            @if($order->description)<div style="font-size:12px;color:#8a7565;margin-top:8px;padding-top:8px;border-top:1px solid #ede1d3;">{{ $order->description }}</div>@endif
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;margin-top:10px;padding-top:10px;border-top:1px solid #ede1d3"><span>{{ __('messages.budget') }}</span><span style="color:var(--accent,#c65d3b)">NPR {{ number_format($order->budget, 2) }}</span></div>
        </div>
    </div>

    {{-- Reference Image (customer upload) --}}
    @if($order->reference_image_path)
    <div style="background:#fff;border:1px solid #ede1d3;border-radius:18px;padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:#2b1e16;">{{ __('messages.artcraft_reference_image') ?? 'Reference Image' }}</h3>
        <img src="{{ route('provider.art-craft.orders.reference-image', $order) }}" alt="Reference" style="max-width:100%;max-height:400px;border-radius:10px;border:1px solid #ede1d3;">
    </div>
    @endif

    {{-- Quote Section --}}
    @include('provider.art-craft._quote-section', ['order' => $order])

    {{-- Production Action Button --}}
    @if($nextAction)
    <div style="background:#fff;border:1px solid #ede1d3;border-radius:18px;padding:22px;">
        @if($nextAction['endpoint'] ?? false)
            {{-- Ready-made confirm availability --}}
            <form method="POST" action="{{ route('provider.art-craft.orders.' . $nextAction['endpoint'], $order) }}">
                @csrf
                <button type="submit" style="padding:14px 28px;background:{{ $nextAction['color'] }};color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:800;cursor:pointer;">
                    ✓ {{ $nextAction['label'] }}
                </button>
            </form>
        @else
            {{-- Standard status transition --}}
            <form method="POST" action="{{ route('provider.art-craft.orders.status', $order) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $nextAction['status'] }}">
                <button type="submit" style="padding:14px 28px;background:{{ $nextAction['color'] }};color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:800;cursor:pointer;">
                    → {{ $nextAction['label'] }}
                </button>
            </form>
        @endif
    </div>
    @endif
</div>
@endsection