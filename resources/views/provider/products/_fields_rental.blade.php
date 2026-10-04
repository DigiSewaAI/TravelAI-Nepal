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

    {{-- PATH-3C C4: Return & Deposit Policy --}}
    <div class="col-span-full mt-4 pt-4 border-t">
        <h4 class="font-semibold text-gray-700 text-sm mb-3">
            {{ __('messages.product_rental_policy_section') }}
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('messages.product_rental_late_fee') }}
                </label>
                <input type="number" name="late_fee_per_day" step="0.01" min="0"
                       value="{{ old('late_fee_per_day', $product->rentalDetail->late_fee_per_day ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">
                    {{ __('messages.product_rental_late_fee_hint') }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('messages.product_rental_damage_pct') }}
                </label>
                <input type="number" name="damage_deposit_pct" min="0" max="100"
                       value="{{ old('damage_deposit_pct', $product->rentalDetail->damage_deposit_pct ?? 100) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">
                    {{ __('messages.product_rental_damage_pct_hint') }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('messages.product_rental_lost_pct') }}
                </label>
                <input type="number" name="lost_deposit_pct" min="0" max="100"
                       value="{{ old('lost_deposit_pct', $product->rentalDetail->lost_deposit_pct ?? 100) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">
                    {{ __('messages.product_rental_lost_pct_hint') }}
                </p>
            </div>
        </div>
    </div>
</div>