@extends('layouts.public')

@section('title', 'Respond to Quotation — ' . $order->booking_number)

@section('content')
<style>
    *, *::before, *::after { box-sizing: border-box; }
    .qr-page { max-width: 720px; margin: 40px auto; padding: 0 20px; }
    .qr-card { background: #fff; border-radius: 20px; padding: 32px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .qr-title { font-size: 26px; font-weight: 900; color: #111; margin: 0 0 8px; }
    .qr-sub { font-size: 14px; color: #6b7280; margin: 0 0 24px; }
    .qr-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
    .qr-row:last-child { border-bottom: none; }
    .qr-label { color: #6b7280; }
    .qr-value { color: #111; font-weight: 700; }
    .qr-amount { font-size: 30px; font-weight: 900; color: #7c3aed; text-align: center; margin: 20px 0; padding: 20px; background: #faf5ff; border-radius: 14px; }
    .qr-btn-group { display: flex; flex-direction: column; gap: 12px; margin-top: 24px; }
    .qr-btn { padding: 16px 24px; border-radius: 12px; font-size: 15px; font-weight: 800; cursor: pointer; border: none; text-align: center; }
    .qr-btn-accept { background: linear-gradient(135deg, #059669, #047857); color: #fff; }
    .qr-btn-revision { background: #fff; color: #ea580c; border: 2px solid #ea580c; }
    .qr-btn-reject { background: #fff; color: #dc2626; border: 2px solid #fecaca; }
    .qr-alert-success { background: #d1fae5; color: #065f46; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700; border: 1px solid #6ee7b7; }
    @media (max-width: 600px) { .qr-card { padding: 20px; } .qr-title { font-size: 22px; } .qr-amount { font-size: 24px; padding: 14px; } }
</style>

<div class="qr-page">
    @if(session('success'))
        <div class="qr-alert-success">✓ {{ session('success') }}</div>
    @endif

    <div class="qr-card">
        <h1 class="qr-title">Your ArtCraft Quotation</h1>
        <p class="qr-sub">Review the quote and let the provider know your decision.</p>

        <div class="qr-row"><span class="qr-label">Order</span><span class="qr-value">{{ $order->booking_number }}</span></div>
        <div class="qr-row"><span class="qr-label">Provider</span><span class="qr-value">{{ $order->provider->name ?? '—' }}</span></div>
        <div class="qr-row"><span class="qr-label">Quote Version</span><span class="qr-value">#{{ $latestQuote->version }}</span></div>
        <div class="qr-row"><span class="qr-label">Materials</span><span class="qr-value">{{ $latestQuote->materials ?? '—' }}</span></div>
        <div class="qr-row"><span class="qr-label">Dimensions</span><span class="qr-value">{{ $latestQuote->dimensions ?? '—' }}</span></div>
        <div class="qr-row"><span class="qr-label">Quantity</span><span class="qr-value">{{ $latestQuote->quantity }}</span></div>
        <div class="qr-row"><span class="qr-label">Production Time</span><span class="qr-value">{{ $latestQuote->production_days }} days</span></div>
        <div class="qr-row"><span class="qr-label">Valid Until</span><span class="qr-value">{{ $latestQuote->valid_until?->format('M d, Y') }}</span></div>

        <div class="qr-amount">{{ $latestQuote->currency }} {{ number_format($latestQuote->quoted_amount, 2) }}</div>

        @if($latestQuote->provider_message)
        <div style="background:#f9fafb;padding:16px;border-radius:12px;margin-bottom:20px;">
            <div style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Message from Provider</div>
            <div style="font-size:14px;color:#374151;line-height:1.6;">{{ $latestQuote->provider_message }}</div>
        </div>
        @endif

        <form method="POST" action="{{ URL::temporarySignedRoute('public.artcraft.quote.respond.submit', now()->addHours(2), ['order' => $order->id]) }}" x-data="{ action: '' }">
            @csrf
            <input type="hidden" name="action" :value="action" x-ref="actionInput">

            <div id="responseReason" style="display:none;margin-top:16px;">
                <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:6px;">Reason / Revision Details *</label>
                <textarea name="customer_response" rows="3" placeholder="Tell the provider what you'd like to change or why you're rejecting..." style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;"></textarea>
            </div>

            <div class="qr-btn-group">
                <button type="submit" class="qr-btn qr-btn-accept" onclick="document.querySelector('[name=customer_response]').removeAttribute('required');this.form.querySelector('[name=action]').value='accept';">✓ Accept This Quote</button>
                <button type="button" class="qr-btn qr-btn-revision" onclick="document.getElementById('responseReason').style.display='block';document.querySelector('[name=customer_response]').setAttribute('required','required');this.dataset.mode='revision';">↻ Request Revision</button>
                <button type="button" class="qr-btn qr-btn-reject" onclick="document.getElementById('responseReason').style.display='block';document.querySelector('[name=customer_response]').setAttribute('required','required');this.dataset.mode='reject';">✕ Reject Quote</button>
            </div>
        </form>

        {{-- Separate simple forms (cleaner than Alpine trick) --}}
        <div style="margin-top:10px;display:flex;gap:10px;flex-direction:column;">
            {{-- Action buttons are above; JS below submits correctly --}}
        </div>

        <script>
        document.querySelectorAll('.qr-btn-revision, .qr-btn-reject').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.classList.contains('qr-btn-revision') ? 'revision' : 'reject';
                const reason = document.querySelector('[name=customer_response]').value;
                if (!reason.trim()) { alert('Please provide a reason.'); return; }
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ URL::temporarySignedRoute('public.artcraft.quote.respond.submit', now()->addHours(2), ['order' => $order->id]) }}';
                ['_token','action','customer_response'].forEach(n => {
                    const i = document.createElement('input');
                    i.type = 'hidden'; i.name = n;
                    i.value = n === '_token' ? '{{ csrf_token() }}' : (n === 'action' ? action : reason);
                    form.appendChild(i);
                });
                document.body.appendChild(form);
                form.submit();
            });
        });
        </script>
    </div>
</div>
@endsection