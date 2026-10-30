<div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6 sticky top-24">

    <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
        <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="fas fa-sliders-h text-xs"></i>
            </span>
            {{ __('messages.filters') }}
        </h3>
        <a href="{{ url()->current() }}" class="text-xs font-semibold text-gray-400 hover:text-gray-900 transition-colors flex items-center gap-1">
            <i class="fas fa-undo text-[10px]"></i> {{ __('messages.reset') }}
        </a>
    </div>

    <form method="GET" action="{{ url()->current() }}" class="space-y-5">

        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">{{ __('messages.products_filter_search') }}</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs group-focus-within:text-blue-600 transition-colors"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_products') }}"
                       class="w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">{{ __('messages.products_filter_location') }}</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="fas fa-map-marker-alt text-gray-400 text-xs group-focus-within:text-blue-600 transition-colors"></i>
                </div>
                <select name="location" class="w-full pl-10 pr-8 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium appearance-none cursor-pointer transition-all">
                    <option value="">{{ __('messages.products_filter_all_locations') }}</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->city }}{{ $loc->district ? ' (' . $loc->district . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400 text-[10px]"></i>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">{{ __('messages.price_range') }}</label>
            <div class="flex items-center gap-2">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-xs font-bold pointer-events-none">$</span>
                    <input type="number" name="min_price" step="0.01" min="0" value="{{ request('min_price') }}" placeholder="{{ __("messages.min") }}"
                           class="w-full pl-6 pr-2 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                </div>
                <span class="text-gray-300 font-bold">—</span>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-xs font-bold pointer-events-none">$</span>
                    <input type="number" name="max_price" step="0.01" min="0" value="{{ request('max_price') }}" placeholder="{{ __("messages.max") }}"
                           class="w-full pl-6 pr-2 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium transition-all">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">{{ __('messages.products_sort_label') }}</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="fas fa-sort-amount-down text-gray-400 text-xs group-focus-within:text-blue-600 transition-colors"></i>
                </div>
                <select name="sort" class="w-full pl-10 pr-8 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm font-medium appearance-none cursor-pointer transition-all">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>{{ __('messages.products_sort_newest') }}</option>
                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('messages.products_sort_price_asc') }}</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('messages.products_sort_price_desc') }}</option>
                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ __('messages.products_sort_name') }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400 text-[10px]"></i>
                </div>
            </div>
        </div>

        <div class="pt-3 flex gap-2">
            <button type="submit" class="flex-[1.5] bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-1.5">
                <i class="fas fa-filter text-[11px]"></i>
                {{ __('messages.products_filter_apply') }}
            </button>
            <a href="{{ url()->current() }}" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 text-gray-700 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-center">
                {{ __('messages.products_filter_reset') }}
            </a>
        </div>

    </form>
</div>