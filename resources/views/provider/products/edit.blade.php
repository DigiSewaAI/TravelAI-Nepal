@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.products_edit_btn'))
@section('header', __('messages.products_edit_btn'))

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">

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

    {{-- 👇 enctype="multipart/form-data" थपिएको छ (फोटो अपलोडको लागि अनिवार्य) --}}
    <form method="POST" action="{{ route('provider.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-4">

            {{-- Product Type (readonly badge) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.product_type') }}</label>
                <span class="px-3 py-1.5 rounded-full text-sm font-semibold
                    @if($type === 'shop') bg-purple-100 text-purple-800
                    @elseif($type === 'rental') bg-indigo-100 text-indigo-800
                    @else bg-amber-100 text-amber-800
                    @endif">
                    {{ __('messages.product_type_' . $type) }}
                </span>
            </div>

            {{-- Common Fields --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_name') }} *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required maxlength="255"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_description') }}</label>
                <textarea name="description" rows="4" maxlength="5000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_price') }} *</label>
                    <input type="number" name="price" step="0.01" min="0" required
                           value="{{ old('price', $product->price) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_currency') }} *</label>
                    <select name="currency" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="NPR" {{ old('currency', $product->currency) === 'NPR' ? 'selected' : '' }}>NPR</option>
                        <option value="USD" {{ old('currency', $product->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
            </div>

            {{-- 👇 Cover Image (Text Input लाई File Input ले रिप्लेस गरिएको छ) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_cover_image') }}</label>
                @if($product->cover_image)
                    <div class="mb-2">
                        <img src="{{ Storage::url($product->cover_image) }}" class="h-20 w-auto rounded border border-gray-200 shadow-sm">
                    </div>
                @endif
                <input type="file" name="cover_image" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing image. Max 5MB.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.product_status') }} *</label>
                <select name="status" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>
                        {{ __('messages.product_status_active') }}
                    </option>
                    <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>
                        {{ __('messages.product_status_inactive') }}
                    </option>
                </select>
            </div>

            {{-- Type-specific (all visible since type is fixed) --}}
            @if($type === 'shop')
                @include('provider.products._fields_shop', ['product' => $product])
            @elseif($type === 'rental')
                @include('provider.products._fields_rental', ['product' => $product])
            @elseif($type === 'wholesale')
                @include('provider.products._fields_wholesale', ['product' => $product])
            @endif

        </div>

        <div class="flex justify-between items-center gap-3 mt-6">
            {{-- 👇 Nested Form Bug Fix: Delete Form बाहिर निकालिएको छ र form attribute प्रयोग गरिएको छ --}}
            <button type="submit" form="delete-form-{{ $product->id }}" class="px-4 py-2 text-sm text-red-600 hover:text-red-800 border border-red-300 rounded-lg hover:bg-red-50">
                {{ __('messages.products_delete_btn') }}
            </button>

            <div class="flex gap-3">
                <a href="{{ route('provider.products.index') }}"
                   class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    {{ __('messages.products_edit_btn') }}
                </button>
            </div>
        </div>
    </form>

    {{-- 👇 Delete Form (मुख्य फर्म भन्दा बाहिर) --}}
    <form id="delete-form-{{ $product->id }}" method="POST" action="{{ route('provider.products.destroy', $product) }}"
          onsubmit="return confirm('{{ __('messages.products_delete_confirm') }}');" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection