@extends('layouts.provider')

@section('title', __('messages.edit_provider_profile_page_title'))
@section('header', __('messages.edit_profile_header'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6 max-w-3xl mx-auto">
    <form method="POST" action="{{ route('provider.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <!-- 🔥 Logo Upload -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.logo') }}</label>
                @if($provider->logo_url)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $provider->logo_url) }}" 
                             class="h-20 w-20 object-cover rounded-full border-2 border-gray-200">
                    </div>
                @endif
                <input type="file" name="logo" accept="image/*" 
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">{{ __('messages.logo_upload_hint') }}</p>
                @error('logo')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- 🔥 Cover Image Upload -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.cover_image') }}</label>
                @if($provider->cover_image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $provider->cover_image) }}" 
                             class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
                    </div>
                @endif
                <input type="file" name="cover_image" accept="image/*" 
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">{{ __('messages.cover_image_upload_hint') }}</p>
                @error('cover_image')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.business_name') }}</label>
                <input type="text" name="name" value="{{ old('name', $provider->name) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.contact_email') }}</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $provider->contact_email) }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('contact_email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.contact_phone') }}</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone', $provider->contact_phone) }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('contact_phone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.address') }}</label>
                <textarea name="address" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address', $provider->address) }}</textarea>
                @error('address')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.description') }}</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $provider->description) }}</textarea>
                @error('description')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-1"></i> {{ __('messages.update_profile') }}
            </button>
            <a href="{{ route('provider.profile') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                <i class="fas fa-times mr-1"></i> {{ __('messages.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection