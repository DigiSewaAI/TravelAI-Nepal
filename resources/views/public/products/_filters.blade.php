<div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-5 sticky top-24 transition-all">
    
    <!-- Header & Top Reset Link -->
    <div class="flex items-center justify-between mb-5 border-b border-gray-100 pb-3">
        <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
            <i class="fas fa-sliders-h text-gray-400 text-sm"></i> Filters
        </h3>
        <a href="{{ url()->current() }}" class="text-xs font-medium text-gray-400 hover:text-gray-800 transition-colors flex items-center gap-1">
            <i class="fas fa-undo text-[10px]"></i> Reset
        </a>
    </div>

    <form method="GET" action="{{ url()->current() }}" class="space-y-4">

        <!-- Search -->
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">{{ __('messages.products_filter_search') }}</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs group-focus-within:text-gray-900 transition-colors"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                       class="w-full pl-9 pr-3 py-2 bg-gray-50/50 border border-gray-200 rounded-lg focus:bg-white focus:ring-1 focus:ring-gray-900 focus:border-gray-900 text-sm transition-all">
            </div>
        </div>

        <!-- Location -->
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">{{ __('messages.products_filter_location') }}</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-map-marker-alt text-gray-400 text-xs group-focus-within:text-gray-900 transition-colors"></i>
                </div>
                <select name="location" class="w-full pl-9 pr-8 py-2 bg-gray-50/50 border border-gray-200 rounded-lg focus:bg-white focus:ring-1 focus:ring-gray-900 focus:border-gray-900 text-sm appearance-none cursor-pointer transition-all">
                    <option value="">{{ __('messages.products_filter_all_locations') }}</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->city }}{{ $loc->district ? ' (' . $loc->district . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400 text-[10px]"></i>
                </div>
            </div>
        </div>

        <!-- Price Range -->
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Price Range</label>
            <div class="flex items-center gap-2">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-gray-400 text-xs font-bold">$</span>
                    <input type="number" name="min_price" step="0.01" min="0" value="{{ request('min_price') }}" placeholder="Min"
                           class="w-full pl-6 pr-2 py-2 bg-gray-50/50 border border-gray-200 rounded-lg focus:bg-white focus:ring-1 focus:ring-gray-900 focus:border-gray-900 text-sm transition-all">
                </div>
                <span class="text-gray-300">-</span>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-gray-400 text-xs font-bold">$</span>
                    <input type="number" name="max_price" step="0.01" min="0" value="{{ request('max_price') }}" placeholder="Max"
                           class="w-full pl-6 pr-2 py-2 bg-gray-50/50 border border-gray-200 rounded-lg focus:bg-white focus:ring-1 focus:ring-gray-900 focus:border-gray-900 text-sm transition-all">
                </div>
            </div>
        </div>

        <!-- Sort By -->
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">{{ __('messages.products_sort_label') }}</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-sort-amount-down text-gray-400 text-xs group-focus-within:text-gray-900 transition-colors"></i>
                </div>
                <select name="sort" class="w-full pl-9 pr-8 py-2 bg-gray-50/50 border border-gray-200 rounded-lg focus:bg-white focus:ring-1 focus:ring-gray-900 focus:border-gray-900 text-sm appearance-none cursor-pointer transition-all">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>{{ __('messages.products_sort_newest') }}</option>
                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('messages.products_sort_price_asc') }}</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('messages.products_sort_price_desc') }}</option>
                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ __('messages.products_sort_name') }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400 text-[10px]"></i>
                </div>
            </div>
        </div>

        <!-- Buttons: Apply & Reset (Slimmer & Cleaner) -->
        <div class="pt-2 flex gap-2">
            <button type="submit" class="flex-[1.5] bg-gray-900 hover:bg-black text-white py-2 rounded-lg text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-1.5">
                {{ __('messages.products_filter_apply') }}
            </button>
            <a href="{{ url()->current() }}" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 text-gray-700 py-2 rounded-lg text-sm font-medium transition-all flex items-center justify-center">
                {{ __('messages.products_filter_reset') }}
            </a>
        </div>

    </form>
</div>