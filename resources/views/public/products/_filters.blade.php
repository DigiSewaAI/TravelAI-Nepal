<div class="bg-white rounded-xl shadow-sm border p-5 sticky top-4">
    <form method="GET" action="{{ url()->current() }}" class="space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.products_filter_search') }}</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.products_filter_location') }}</label>
            <select name="location" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="">{{ __('messages.products_filter_all_locations') }}</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ request('location') == $loc->id ? 'selected' : '' }}>
                        {{ $loc->city }}{{ $loc->district ? ' (' . $loc->district . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.products_filter_price_min') }}</label>
                <input type="number" name="min_price" step="0.01" min="0" value="{{ request('min_price') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.products_filter_price_max') }}</label>
                <input type="number" name="max_price" step="0.01" min="0" value="{{ request('max_price') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.products_sort_label') }}</label>
            <select name="sort" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>{{ __('messages.products_sort_newest') }}</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('messages.products_sort_price_asc') }}</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('messages.products_sort_price_desc') }}</option>
                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ __('messages.products_sort_name') }}</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm">
                {{ __('messages.products_filter_apply') }}
            </button>
            <a href="{{ url()->current() }}" class="flex-1 text-center border border-gray-300 hover:bg-gray-50 py-2 rounded-lg text-sm">
                {{ __('messages.products_filter_reset') }}
            </a>
        </div>

    </form>
</div>