{{-- New Order Modal (Shared Partial — used by dashboard + orders list) --}}
<div id="orderModal" style="display:none;position:fixed;inset:0;background:rgba(28,25,23,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:600px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text)">{{ __('messages.new_order') }}</h3>
            <button type="button" id="closeOrderModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.restaurant.orders.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:20px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.select_table') }}</label>
                <select name="table_id" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                    <option value="">{{ __('messages.select_table') }}...</option>
                    @foreach($tablesData ?? [] as $t)
                        <option value="{{ $t['db_id'] }}">{{ $t['label'] ?? ('T-'.str_pad($t['n'],2,'0',STR_PAD_LEFT)) }} ({{ $t['seats'] }} {{ __('messages.seats') }})</option>
                    @endforeach
                </select>
            </div>
            @if(config('claim.restaurant_enabled'))
            <div style="margin-top:12px;margin-bottom:20px;padding:12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;">
                <div style="font-size:12px;font-weight:700;color:#166534;margin-bottom:8px;">👤 Customer Details (optional — for order claiming)</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div>
                        <label style="font-size:11px;color:#6b7280;display:block;margin-bottom:2px;">Name</label>
                        <input type="text" name="customer_name" maxlength="100" value="{{ old('customer_name') }}" placeholder="Walk-in Customer" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;font-size:13px;">
                    </div>
                    <div>
                        <label style="font-size:11px;color:#6b7280;display:block;margin-bottom:2px;">Phone</label>
                        <input type="text" name="customer_phone" maxlength="20" value="{{ old('customer_phone') }}" placeholder="+977-..." style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;font-size:13px;">
                    </div>
                </div>
                <div style="margin-top:8px;">
                    <label style="font-size:11px;color:#6b7280;display:block;margin-bottom:2px;">Email (for OTP claim)</label>
                    <input type="email" name="customer_email" maxlength="190" value="{{ old('customer_email') }}" placeholder="customer@example.com (optional)" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;font-size:13px;">
                </div>
            </div>
            @endif
            <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;display:block;margin-bottom:8px">{{ __('messages.select_menu_items') }}</label>
            <div style="max-height:250px;overflow-y:auto;border:1px solid var(--line);border-radius:12px;padding:10px;margin-bottom:20px" id="menuItemsList">
                @forelse($allProducts ?? [] as $item)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px;border-bottom:1px solid #f0f0f0;gap:8px">
                    <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:0">
                        <input type="checkbox" class="order-item-check" data-price="{{ $item['price'] ?? 0 }}" name="items[{{ $loop->index }}][id]" value="{{ $item['db_id'] ?? '' }}">
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px">{{ $item['emoji'] ?? '🍽️' }} {{ $item['name'] ?? '' }} (Rs {{ number_format($item['price'] ?? 0) }})</span>
                    </div>
                    <input type="number" name="items[{{ $loop->index }}][qty]" class="item-qty" data-price="{{ $item['price'] ?? 0 }}" value="1" min="1" style="width:60px;padding:4px;text-align:center;border:1px solid var(--line);border-radius:6px;font-family:inherit;font-size:12px" disabled>
                </div>
                @empty
                <p class="empty">{{ __('messages.no_menu_items_yet') }}</p>
                @endforelse
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding:15px;background:#f8fafc;border-radius:12px">
                <span style="font-size:14px;font-weight:700">{{ __('messages.total_amount') }}</span>
                <span style="font-size:18px;font-weight:800" id="orderTotalDisplay">Rs 0</span>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.create_order') }}</button>
        </form>
    </div>
</div>

@once
<script>
(function() {
    const orderModal = document.getElementById('orderModal');
    if (!orderModal) return;

    const openFn  = () => { orderModal.style.display = 'flex'; };
    const closeFn = () => { orderModal.style.display = 'none'; };

    document.getElementById('newOrderBtn')?.addEventListener('click', openFn);
    document.getElementById('closeOrderModal')?.addEventListener('click', closeFn);
    window.openOrderModal  = openFn;
    window.closeOrderModal = closeFn;

    orderModal.addEventListener('click', (e) => { if (e.target === orderModal) closeFn(); });

    function calculateTotal() {
        let orderTotal = 0;
        document.querySelectorAll('.order-item-check').forEach(check => {
            const qtyInput = check.closest('div').parentElement.querySelector('.item-qty');
            if (!qtyInput) return;
            if (check.checked) {
                qtyInput.disabled = false;
                const price = parseFloat(check.dataset.price) || 0;
                const qty = parseInt(qtyInput.value) || 1;
                orderTotal += price * qty;
            } else {
                qtyInput.disabled = true;
                qtyInput.value = 1;
            }
        });
        const display = document.getElementById('orderTotalDisplay');
        if (display) display.textContent = 'Rs ' + orderTotal.toLocaleString('en-US');
    }

    document.querySelectorAll('.order-item-check').forEach(c => c.addEventListener('change', calculateTotal));
    document.querySelectorAll('.item-qty').forEach(q => q.addEventListener('input', calculateTotal));
})();
</script>
@endonce