<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_rental_price_per_day') }} *</label>
        <input type="number" name="rental_price_per_day" step="0.01" min="0" required
               value="{{ old('rental_price_per_day', $product->rentalDetail->rental_price_per_day ?? '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_rental_deposit') }}</label>
        <input type="number" name="rental_deposit" step="0.01" min="0"
               value="{{ old('rental_deposit', $product->rentalDetail->rental_deposit ?? '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_rental_condition') }}</label>
        <input type="text" name="rental_condition" maxlength="50"
               value="{{ old('rental_condition', $product->rentalDetail->rental_condition ?? '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="grid grid-cols-2 gap-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_rental_min_days') }}</label>
            <input type="number" name="rental_min_days" min="1"
                   value="{{ old('rental_min_days', $product->rentalDetail->rental_min_days ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_rental_max_days') }}</label>
            <input type="number" name="rental_max_days" min="1"
                   value="{{ old('rental_max_days', $product->rentalDetail->rental_max_days ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>
</div>