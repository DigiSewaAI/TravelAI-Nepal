<div class="category-fields hidden mt-4 p-4 border rounded-lg bg-gray-50" data-slug="money-changer">
    <h3 class="font-bold mb-3 text-lg">{{ __('messages.money_changer_details') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.license_number') }}</label>
            <input type="text" name="license_number"
                   value="{{ old('license_number', $service?->moneyChangerDetail?->license_number ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.exchange_rate_margin') }}</label>
            <input type="number" step="0.01" name="exchange_rate_margin" min="0" max="100"
                   value="{{ old('exchange_rate_margin', $service?->moneyChangerDetail?->exchange_rate_margin ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">{{ __('messages.supported_currencies') }}</label>
            <input type="text" name="supported_currencies_str"
                   value="{{ old('supported_currencies_str', is_array($service?->moneyChangerDetail?->supported_currencies ?? null) ? implode(', ', $service->moneyChangerDetail->supported_currencies) : '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500"
                   placeholder="USD, EUR, GBP, INR">
            <p class="text-xs text-gray-500 mt-1">{{ __('messages.supported_currencies_hint') }}</p>
        </div>
    </div>
</div>