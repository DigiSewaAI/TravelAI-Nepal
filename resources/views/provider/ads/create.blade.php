@extends('layouts.provider')

@section('title', __('messages.ads_buy_new'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    <a href="{{ route('provider.ads.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-amber-600 mb-4">
        <i class="fas fa-arrow-left"></i> {{ __('messages.ads_back') }}
    </a>

    <div class="bg-white rounded-2xl shadow-sm border p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('messages.ads_buy_new') }}</h1>
        <p class="text-sm text-gray-500 mb-6">{{ __('messages.ads_buy_subtitle') }}</p>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('provider.ads.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Ad content --}}
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_title_label') }} *</label>
                <input type="text" name="title" required value="{{ old('title') }}"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_description_label') }}</label>
                <textarea name="description" rows="3" maxlength="500"
                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_image_label') }} * (1200×300)</label>
                <input type="file" name="image" accept="image/*" required
                       class="w-full border rounded-lg px-3 py-2 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-amber-100 file:text-amber-700">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_link_label') }} *</label>
                    <input type="text" name="link_url" required value="{{ old('link_url') }}"
                           placeholder="/services/my-service or https://..."
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_link_target_label') }}</label>
                    <select name="link_target" class="w-full border rounded-lg px-3 py-2">
                        <option value="_blank">New Tab</option>
                        <option value="_self">Same Tab</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_cta_label') }}</label>
                <input type="text" name="cta_text" value="{{ old('cta_text', 'Learn More') }}"
                       maxlength="50"
                       class="w-full border rounded-lg px-3 py-2">
            </div>

            {{-- Duration --}}
            <div>
                <label class="block text-sm font-semibold mb-2">{{ __('messages.ads_duration_label') }} *</label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($pricing as $days => $price)
                        <label class="cursor-pointer">
                            <input type="radio" name="duration_days" value="{{ $days }}"
                                   {{ old('duration_days') == $days ? 'checked' : ($loop->first ? 'checked' : '') }}
                                   class="peer sr-only">
                            <div class="border-2 border-gray-200 peer-checked:border-amber-500 peer-checked:bg-amber-50 rounded-xl p-3 text-center transition">
                                <div class="font-bold text-lg">{{ $days }} {{ __('messages.ads_days') }}</div>
                                <div class="text-amber-600 font-bold">Rs. {{ number_format($price) }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Targeting (optional) --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_target_destination_label') }}</label>
                    <input type="text" name="target_destination" value="{{ old('target_destination') }}"
                           placeholder="e.g. Everest Base Camp"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_target_category_label') }}</label>
                    <select name="target_category_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">— {{ __('messages.ads_target_any') }} —</option>
                        @foreach(\App\Models\ServiceCategory::orderBy('name')->get() as $cat)
                            <option value="{{ $cat->id }}" {{ old('target_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Payment --}}
            <div class="border-t pt-5 mt-5">
                <h3 class="font-bold text-gray-800 mb-3">{{ __('messages.ads_payment_section') }}</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_payment_method_label') }} *</label>
                        <select name="payment_method" required class="w-full border rounded-lg px-3 py-2">
                            <option value="">— Select —</option>
                            <option value="eSewa">eSewa</option>
                            <option value="Khalti">Khalti</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="IME Pay">IME Pay</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_payment_ref_label') }}</label>
                        <input type="text" name="payment_reference" value="{{ old('payment_reference') }}"
                               placeholder="Transaction ID (optional)"
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-1">{{ __('messages.ads_payment_proof_label') }} * (screenshot)</label>
                    <input type="file" name="payment_proof" accept="image/*" required
                           class="w-full border rounded-lg px-3 py-2 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-amber-100 file:text-amber-700">
                </div>

                <div class="mt-3 text-xs text-gray-500 bg-amber-50 border border-amber-100 rounded-lg p-3">
                    <i class="fas fa-info-circle mr-1 text-amber-600"></i>
                    {{ __('messages.ads_payment_info') }}
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex gap-2 pt-4">
                <a href="{{ route('provider.ads.index') }}"
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-center py-2.5 rounded-lg font-medium">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-lg font-bold shadow-md">
                    <i class="fas fa-paper-plane mr-1"></i> {{ __('messages.ads_submit_btn') }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection