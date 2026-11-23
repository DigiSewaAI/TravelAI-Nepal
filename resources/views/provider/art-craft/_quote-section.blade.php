@php
    $canQuote = in_array($order->status, ['pending', 'under_review', 'revision_requested'], true);
    $statusColors = [
        'pending' => ['#fef4e2', '#b45309'],
        'under_review' => ['#eff6ff', '#2563eb'],
        'quoted' => ['#faf5ff', '#7c3aed'],
        'revision_requested' => ['#fff7ed', '#ea580c'],
        'accepted' => ['#ecfdf5', '#059669'],
        'in_progress' => ['#dbeafe', '#1d4ed8'],
        'ready_for_delivery' => ['#fef3c7', '#b45309'],
        'completed' => ['#d1fae5', '#065f46'],
        'rejected' => ['#fee2e2', '#b91c1c'],
        'cancelled' => ['#f3f4f6', '#6b7280'],
    ];
    [$sBg, $sFg] = $statusColors[$order->status] ?? ['#f3f4f6', '#6b7280'];
@endphp

{{-- Quotation History --}}
<div style="background:#fff;border:1px solid #e7eaf3;border-radius:18px;padding:22px;margin-bottom:18px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
        <h3 style="font-size:14px;font-weight:800;margin:0;color:#111;">Quotation History ({{ $order->quotes->count() }})</h3>
        <span style="padding:5px 12px;border-radius:8px;background:{{ $sBg }};color:{{ $sFg }};font-size:11px;font-weight:800;text-transform:uppercase;">
            {{ str_replace('_', ' ', $order->status) }}
        </span>
    </div>

    @forelse($order->quotes as $quote)
    <div style="padding:14px;border:1px solid #e5e7eb;border-radius:10px;margin-bottom:10px;background:{{ $quote->status === 'accepted' ? '#f0fdf4' : ($quote->status === 'superseded' ? '#f9fafb' : '#fff') }};">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <div style="font-size:13px;font-weight:800;color:#111;">Quote #{{ $quote->version }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;">
                    {{ $quote->currency }} {{ number_format($quote->quoted_amount, 2) }} · {{ $quote->production_days }} days · {{ $quote->created_at->format('M d, Y') }}
                </div>
            </div>
            <span style="padding:4px 10px;border-radius:6px;font-size:10px;font-weight:800;text-transform:uppercase;
                background:{{ $quote->status === 'accepted' ? '#d1fae5' : ($quote->status === 'rejected' ? '#fee2e2' : ($quote->status === 'superseded' ? '#f3f4f6' : '#fef4e2')) }};
                color:{{ $quote->status === 'accepted' ? '#065f46' : ($quote->status === 'rejected' ? '#b91c1c' : ($quote->status === 'superseded' ? '#6b7280' : '#b45309')) }};">
                {{ $quote->status }}
            </span>
        </div>
        @if($quote->customer_response)
        <div style="margin-top:10px;padding:10px;background:#fff7ed;border-radius:8px;border-left:3px solid #ea580c;">
            <div style="font-size:10px;font-weight:800;color:#9a3412;text-transform:uppercase;margin-bottom:3px;">Customer Response</div>
            <div style="font-size:12px;color:#374151;">{{ $quote->customer_response }}</div>
        </div>
        @endif
    </div>
    @empty
    <p style="font-size:13px;color:#9ca3af;text-align:center;padding:20px 0;margin:0;">No quotes sent yet.</p>
    @endforelse
</div>

{{-- Send Quote Form --}}
@if($canQuote)
<div style="background:#fff;border:1px solid #e7eaf3;border-radius:18px;padding:22px;margin-bottom:18px;">
    <h3 style="font-size:14px;font-weight:800;margin:0 0 16px;color:#111;">
        {{ $order->quotes->count() > 0 ? 'Send Revised Quote' : 'Send Quotation' }}
    </h3>

    @if($errors->any())
    <div style="padding:10px 14px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;margin-bottom:12px;font-size:12px;">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('provider.art-craft.orders.quote', $order) }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
                <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Quoted Amount *</label>
                <input type="number" name="quoted_amount" step="0.01" min="1" required value="{{ old('quoted_amount', $order->quoted_amount) }}" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Currency</label>
                <select name="currency" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
                    <option value="NPR" {{ old('currency', 'NPR') === 'NPR' ? 'selected' : '' }}>NPR</option>
                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Quantity *</label>
                <input type="number" name="quantity" min="1" required value="{{ old('quantity', $order->quantity ?? 1) }}" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Production Days *</label>
                <input type="number" name="production_days" min="1" required value="{{ old('production_days', 7) }}" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Materials</label>
                <input type="text" name="materials" value="{{ old('materials', $order->materials) }}" placeholder="e.g. Copper, Lokta paper" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Dimensions</label>
                <input type="text" name="dimensions" value="{{ old('dimensions', $order->dimensions) }}" placeholder="e.g. 30cm × 20cm" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Advance (optional)</label>
                <input type="number" name="advance_proposal" step="0.01" min="0" value="{{ old('advance_proposal') }}" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
            </div>
            <div>
                <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Valid Until *</label>
                <input type="date" name="valid_until" required min="{{ now()->addDay()->format('Y-m-d') }}" value="{{ old('valid_until', now()->addDays(7)->format('Y-m-d')) }}" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
            </div>
        </div>
        <div style="margin-top:14px;">
            <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Delivery Terms</label>
            <textarea name="delivery_terms" rows="2" placeholder="e.g. Pickup from studio" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">{{ old('delivery_terms') }}</textarea>
        </div>
        <div style="margin-top:14px;">
            <label style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;display:block;margin-bottom:4px;">Message to Customer</label>
            <textarea name="provider_message" rows="3" placeholder="Optional note" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">{{ old('provider_message') }}</textarea>
        </div>
        <button type="submit" style="margin-top:16px;padding:12px 24px;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;">
            Send Quote to Customer
        </button>
    </form>
</div>
@endif