@extends($provider_layout ?? 'layouts.provider-inventory')

@section('title', $rfq->rfq_number)
@section('page-title', $rfq->rfq_number)
@section('page-subtitle', $rfq->created_at->format('M d, Y H:i'))

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
    [$sBg, $sFg] = $statusColors[$rfq->status] ?? ['#f3f4f6', '#6b7280'];
@endphp

<div style="display:flex;flex-direction:column;gap:18px">

    <a href="{{ route('provider.wholesale-rfq.index') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--accent,#6366f1);font-weight:700;text-decoration:none;font-size:13px;width:max-content">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        {{ __('messages.provider_rfq_title') }}
    </a>

    @if(session('success'))
        <div style="background:#e7f8f1;border:1px solid #10b981;color:#059669;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="background:#fdecec;border:1px solid #ef4444;color:#dc2626;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:700">{{ session('error') }}</div>
    @endif

    {{-- Hero --}}
    <div style="background:linear-gradient(135deg,var(--accent,#6366f1) 0%,var(--accent-d,#4f46e5) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <div>
            <div style="font-size:22px;font-weight:800;letter-spacing:-.5px">{{ $rfq->rfq_number }}</div>
            <div style="font-size:13px;opacity:.88;margin-top:4px">{{ $rfq->created_at->format('M d, Y H:i') }}</div>
        </div>
        <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ $rfq->status }}</span>
    </div>

    {{-- Buyer + Product --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
            <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#111827)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {{ __('messages.provider_order_customer') }}
            </h3>
            <div style="font-size:14px;font-weight:700;color:var(--text,#111827)">{{ $rfq->buyer->name ?? 'N/A' }}</div>
            @if($rfq->buyer_company)
                <div style="font-size:13px;color:var(--muted,#6b7280);margin-top:4px">{{ $rfq->buyer_company }}</div>
            @endif
            @if($rfq->buyer_phone)
                <div style="font-size:13px;color:var(--muted,#6b7280);margin-top:4px">{{ $rfq->buyer_phone }}</div>
            @endif
            <div style="font-size:13px;color:var(--muted,#6b7280);margin-top:4px">{{ $rfq->buyer->email ?? '' }}</div>
        </div>
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
            <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#111827)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                Product
            </h3>
            <div style="font-size:14px;font-weight:700;color:var(--text,#111827)">{{ $rfq->product->name ?? 'N/A' }}</div>
            <div style="font-size:13px;color:var(--muted,#6b7280);margin-top:4px">{{ __('messages.rfq_quantity') }}: <strong style="color:var(--text,#111827)">{{ $rfq->quantity }}</strong></div>
        </div>
    </div>

    @if($rfq->message)
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:10px;color:var(--text,#111827)">{{ __('messages.rfq_message') }}</h3>
            <p style="font-size:13px;color:var(--text,#111827);white-space:pre-line;line-height:1.6">{{ $rfq->message }}</p>
        </div>
    @endif

    @if($rfq->isPending())
        <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:16px;color:var(--text,#111827)">{{ __('messages.provider_rfq_respond') }}</h3>
            <form method="POST" action="{{ route('provider.wholesale-rfq.quote', $rfq) }}" style="display:flex;flex-direction:column;gap:14px">
                @csrf
                @method('PATCH')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:700;color:var(--muted,#6b7280);margin-bottom:5px">{{ __('messages.rfq_quote_price') }} (NPR/unit) *</label>
                        <input type="number" name="quoted_price" step="0.01" min="0" required
                               style="width:100%;padding:10px 14px;border:1px solid var(--line,#e7eaf3);border-radius:10px;font-size:13px;font-weight:600;background:#fff;color:var(--text,#111827)">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:700;color:var(--muted,#6b7280);margin-bottom:5px">{{ __('messages.rfq_valid_until') }}</label>
                        <input type="date" name="valid_until" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               value="{{ date('Y-m-d', strtotime('+7 days')) }}"
                               style="width:100%;padding:10px 14px;border:1px solid var(--line,#e7eaf3);border-radius:10px;font-size:13px;background:#fff;color:var(--text,#111827)">
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:var(--muted,#6b7280);margin-bottom:5px">{{ __('messages.rfq_message') }}</label>
                    <textarea name="provider_response" rows="3" maxlength="2000"
                              style="width:100%;padding:10px 14px;border:1px solid var(--line,#e7eaf3);border-radius:10px;font-size:13px;background:#fff;color:var(--text,#111827);font-family:inherit"></textarea>
                </div>
                <button type="submit" style="align-self:flex-start;padding:11px 22px;border-radius:10px;background:var(--accent,#6366f1);color:#fff;font-size:13px;font-weight:800;border:0;cursor:pointer;box-shadow:0 6px 16px rgba(0,0,0,.12)">
                    {{ __('messages.provider_rfq_send_quote') }}
                </button>
            </form>
        </div>
    @elseif($rfq->isQuoted())
        <div style="background:#e9f1fe;border:1px solid #2563eb;border-radius:var(--radius,18px);padding:20px">
            <div style="display:flex;flex-direction:column;gap:6px;font-size:13px;color:#1e40af">
                <div>{{ __('messages.rfq_quote_price') }}: <strong>NPR {{ number_format($rfq->quoted_price, 2) }}</strong></div>
                <div>{{ __('messages.rfq_quote_total') }}: <strong>NPR {{ number_format($rfq->quoted_total, 2) }}</strong></div>
                @if($rfq->valid_until)
                    <div>{{ __('messages.rfq_valid_until') }}: <strong>{{ $rfq->valid_until->format('M d, Y') }}</strong></div>
                @endif
            </div>
        </div>
    @endif

    {{-- Messages thread --}}
    <div style="background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:16px;color:var(--text,#111827)">{{ __('messages.rfq_message') }}</h3>
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;max-height:400px;overflow-y:auto">
            @forelse($rfq->messages as $msg)
                <div style="padding:12px 14px;border-radius:10px;{{ $msg->sender_role === 'provider' ? 'background:#e9f1fe;margin-left:32px' : 'background:#f3f4f6;margin-right:32px' }}">
                    <div style="font-size:11px;color:var(--muted,#6b7280);margin-bottom:4px;font-weight:600">
                        {{ $msg->sender->name ?? 'N/A' }} ({{ $msg->sender_role }}) · {{ $msg->created_at->diffForHumans() }}
                    </div>
                    <div style="font-size:13px;color:var(--text,#111827);white-space:pre-line">{{ $msg->message }}</div>
                </div>
            @empty
                <p style="font-size:13px;color:var(--muted,#6b7280);text-align:center;padding:20px 0">No messages yet.</p>
            @endforelse
        </div>

        @if(!in_array($rfq->status, ['expired', 'cancelled', 'rejected', 'accepted']))
            <form method="POST" action="{{ route('provider.wholesale-rfq.message', $rfq) }}" style="display:flex;flex-direction:column;gap:10px">
                @csrf
                <textarea name="message" required rows="2" maxlength="2000"
                          placeholder="{{ __('messages.rfq_message') }}"
                          style="width:100%;padding:10px 14px;border:1px solid var(--line,#e7eaf3);border-radius:10px;font-size:13px;background:#fff;color:var(--text,#111827);font-family:inherit"></textarea>
                <button type="submit" style="align-self:flex-start;padding:9px 18px;border-radius:10px;background:var(--accent,#6366f1);color:#fff;font-size:13px;font-weight:800;border:0;cursor:pointer">
                    Send
                </button>
            </form>
        @endif
    </div>

</div>
@endsection