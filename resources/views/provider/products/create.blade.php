@extends('layouts.provider')

@section('title', __('messages.products_create_btn'))
@section('header', __('messages.products_create_btn'))

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded-lg">
            <strong>{{ __('messages.validation_error') ?? 'Please check the form' }}</strong>
            <ul class="mb-0 mt-1 list-disc list-inside">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('provider.products.store') }}">
        @csrf

        <div class="space-y-4">

            {{-- Product Type Selector --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.product_type') }} *</label>
                <div class="flex gap-4">
                    @foreach(['shop', 'rental', 'wholesale'] as $t)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="product_type" value="{{ $t }}"
                                   data-type-toggle
                                   {{ old('product_type', $type) === $t ? 'checked' : '' }}>
                            <span>{{ __('messages.product_type_' . $t) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Common Fields --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_name') }} *</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_description') }}</label>
                <textarea name="description" rows="4" maxlength="5000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_price') }} *</label>
                    <input type="number" name="price" step="0.01" min="0" required
                           value="{{ old('price') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_currency') }} *</label>
                    <select name="currency" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="NPR" {{ old('currency', 'NPR') === 'NPR' ? 'selected' : '' }}>NPR</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_cover_image') }}</label>
                <input type="text" name="cover_image" value="{{ old('cover_image') }}" maxlength="255"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="path/to/image.jpg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_status') }} *</label>
                <select name="status" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                        {{ __('messages.product_status_active') }}
                    </option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                        {{ __('messages.product_status_inactive') }}
                    </option>
                </select>
            </div>

            {{-- Type-specific fields --}}
            <div data-type-section="shop" style="display:none;">
                @include('provider.products._fields_shop', ['product' => null])
            </div>
            <div data-type-section="rental" style="display:none;">
                @include('provider.products._fields_rental', ['product' => null])
            </div>
            <div data-type-section="wholesale" style="display:none;">
                @include('provider.products._fields_wholesale', ['product' => null])
            </div>

        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('provider.products.index') }}"
               class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                {{ __('messages.cancel') ?? 'Cancel' }}
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                {{ __('messages.save') ?? 'Save' }}
            </button>
        </div>
    </form>
</div>

{{-- Vanilla JS: type toggle --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('[data-type-toggle]');
    const sections = document.querySelectorAll('[data-type-section]');

    function updateSections() {
        const selected = document.querySelector('[data-type-toggle]:checked');
        const type = selected ? selected.value : 'shop';
        sections.forEach(sec => {
            sec.style.display = (sec.dataset.typeSection === type) ? 'block' : 'none';
        });
    }

    toggles.forEach(t => t.addEventListener('change', updateSections));
    updateSections();
});
</script>
@endsection