<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_wholesale_min_order_qty') }} *</label>
        <input type="number" name="min_order_qty" min="1" required
               value="{{ old('min_order_qty', $product->wholesaleDetail->min_order_qty ?? '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</div>
<p class="text-xs text-gray-500 mt-2">
    {{ __('messages.product_wholesale_bulk_pricing_hint') }}
</p>