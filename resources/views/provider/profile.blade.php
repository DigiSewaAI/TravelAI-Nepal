@extends('layouts.provider')

@section('title', __('messages.provider_profile_page_title'))
@section('header', __('messages.provider_profile_header'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6 max-w-3xl mx-auto">
    <div class="flex items-center space-x-4 mb-6">
        @if($provider->logo_url)
            <img src="{{ asset('storage/' . $provider->logo_url) }}" 
                 class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
        @else
            <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-building text-blue-600 text-3xl"></i>
            </div>
        @endif
        <div>
            <h2 class="text-2xl font-bold">{{ $provider->name }}</h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-500">{{ __('messages.id') }}: #{{ $provider->id }}</span>
                @if($provider->verification_status === 'verified')
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">{{ __('messages.verified') }} ✅</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500">{{ __('messages.contact_email') }}</p>
            <p class="font-medium">{{ $provider->contact_email ?? $provider->user->email ?? __('messages.na') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">{{ __('messages.contact_phone') }}</p>
            <p class="font-medium">{{ $provider->contact_phone ?? __('messages.na') }}</p>
        </div>
        <div class="col-span-2">
            <p class="text-sm text-gray-500">{{ __('messages.address') }}</p>
            <p class="font-medium">{{ $provider->address ?? __('messages.na') }}</p>
        </div>
        <div class="col-span-2">
            <p class="text-sm text-gray-500">{{ __('messages.description') }}</p>
            <p class="text-gray-700">{{ $provider->description ?? __('messages.no_description_provided') }}</p>
        </div>
    </div>

    <div class="mt-6 flex space-x-3">
        <a href="{{ route('provider.profile.edit') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-edit mr-1"></i> {{ __('messages.edit_profile') }}
        </a>
        <a href="{{ route('provider.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-arrow-left mr-1"></i> {{ __('messages.back_to_dashboard') }}
        </a>
    </div>
</div>
@endsection