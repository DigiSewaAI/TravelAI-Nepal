@extends('layouts.public')
@section('title', 'Order Status — ' . $order->booking_number)
@section('content')
<style>
    *, *::before, *::after { box-sizing: border-box; }
    .os-page { max-width: 720px; margin: 40px auto; padding: 0 20px; }
    .os-card { background: #fff; border-radius: 20px; padding: 32px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .os-title { font-size: 26px; font-weight: 900; color: #111; margin: 0 0 8px; }
    .os-sub { font-size: 14px; color: #6b7280; margin: 0 0 24px; }
    .os-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
    .os-row:last-child { border-bottom: none; }
    .os-label { color: #6b7280; }
    .os-value { color: #111; font-weight: 700; }
    .os-timeline { display: flex; gap: 0; overflow-x: auto; padding: 8px 0; }
    .os-step { flex: 1; min-width: 90px; text-align: center; position: relative; }
    .os-step-num { width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; }
    .os-step-label { font-size: 11px; font-weight: 600; margin-top: 6px; line-height: 1.3; }
    .os-cta { text-align: center; margin-top: 24px; }
    .os-btn { display: inline-block; background: #7c3aed; color: #fff; padding: 14px 32px; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 14px; }
</style>

<div class="os-page">
    <div class="os-card">
        <h1 class="os-title">Order Status</h1>
        <p class="os-sub">Track your ArtCraft order in real time.</p>

        <div class="os-row"><span class="os-label">Order</span><span class="os-value">{{ $order->booking_number }}</span></div>
        <div class="os-row"><span class="os-label">Provider</span><span class="os-value">{{ $order->provider->name ?? '—' }}</span></div>
        <div class="os-row"><span class="os-label">Type</span><span class="os-value">{{ $order->request_type === 'ready_made' ? 'Ready-Made' : 'Custom Commission' }}</span></div>
        <div class="os-row"><span class="os-label">Current Status</span><span class="os-value">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span></div>
        @if($order->quoted_amount)
        <div class="os-row"><span class="os-label">Quoted Amount</span><span class="os-value">{{ $order->accepted_quote_currency ?? 'NPR' }} {{ number_format($order->quoted_amount, 2) }}</span></div>
        @endif
    </div>

    {{-- Pending quote CTA --}}
    @if($latestPendingQuote)
    <div class="os-card">
        <h2 style="font-size: 18px; font-weight: 800; margin: 0 0 8px;">📩 You have a pending quote</h2>
        <p style="color: #6b7280; font-size: 14px;">The provider has sent you a quote for {{ $latestPendingQuote->currency }} {{ number_format($latestPendingQuote->quoted_amount, 2) }}. Please respond to continue.</p>
        <div class="os-cta">
            <a href="{{ URL::temporarySignedRoute('public.artcraft.quote.respond', now()->addDays(30), ['order' => $order->id]) }}" class="os-btn">Review & Respond →</a>
        </div>
    </div>
    @endif

    {{-- Quote History --}}
    @if($order->quotes->count() > 0)
    <div class="os-card">
        <h2 style="font-size: 18px; font-weight: 800; margin: 0 0 16px;">Quotation History</h2>
        @foreach($order->quotes as $quote)
        <div style="padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                <div>
                    <div style="font-size: 13px; font-weight: 800;">Quote #{{ $quote->version }}</div>
                    <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">{{ $quote->currency }} {{ number_format($quote->quoted_amount, 2) }} · {{ $quote->production_days }}d</div>
                </div>
                <span style="padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: {{ $quote->status === 'accepted' ? '#d1fae5' : ($quote->status === 'rejected' ? '#fee2e2' : '#f3f4f6') }}; color: {{ $quote->status === 'accepted' ? '#065f46' : ($quote->status === 'rejected' ? '#b91c1c' : '#6b7280') }};">{{ $quote->status }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection