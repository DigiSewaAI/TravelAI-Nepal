<div class="category-fields hidden mt-4 p-4 border rounded-lg bg-gray-50" data-slug="restaurant">
    <h3 class="font-bold mb-3 text-lg">{{ __('messages.restaurant_details') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.cuisine_type') }}</label>
            <input type="text" name="cuisine_type"
                   value="{{ old('cuisine_type', $service?->restaurantDetail?->cuisine_type ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. Nepali, Italian">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.seating_capacity') }}</label>
            <input type="number" name="seating_capacity" min="0"
                   value="{{ old('seating_capacity', $service?->restaurantDetail?->seating_capacity ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. 50">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.opening_hours') }}</label>
            <input type="text" name="opening_hours"
                   value="{{ old('opening_hours', $service?->restaurantDetail?->opening_hours ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. 10 AM - 9 PM">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.menu_url') }}</label>
            <input type="url" name="menu_url"
                   value="{{ old('menu_url', $service?->restaurantDetail?->menu_url ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500"
                   placeholder="https://...">
        </div>
    </div>
</div>