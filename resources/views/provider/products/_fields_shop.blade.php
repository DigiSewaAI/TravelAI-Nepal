<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_shop_stock_count') }}</label>
        <input type="number" name="stock_count" min="0"
               value="{{ old('stock_count', $product->shopDetail->stock_count ?? '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_shop_sku') }}</label>
        <input type="text" name="sku" maxlength="100"
               value="{{ old('sku', $product->shopDetail->sku ?? '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</div>