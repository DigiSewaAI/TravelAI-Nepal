@extends('layouts.public')

@section('title', __('messages.contact_sales_title'))

@section('content')
<div class="min-h-screen bg-gray-100 py-12 px-4">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">

        <h1 class="text-3xl font-bold text-gray-900 mb-3">
            {{ __('messages.contact_sales_title') }}
        </h1>
        <p class="text-gray-600 mb-8">
            {{ __('messages.contact_sales_description') }}
        </p>

        {{-- Success / Error Messages --}}
        @if(session('enterprise_success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-6">
                {{ session('enterprise_success') }}
            </div>
        @endif
        @if(session('enterprise_error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6">
                {{ session('enterprise_error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('public.contact-sales.submit') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('messages.enterprise_form_company') }} *</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('messages.enterprise_form_person') }} *</label>
                <input type="text" name="contact_person" value="{{ old('contact_person') }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('messages.enterprise_form_email') }} *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('messages.enterprise_form_phone') }}</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('messages.enterprise_form_requirements') }} *</label>
                <textarea name="requirements" rows="5" required
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('requirements') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 rounded-xl transition">
                {{ __('messages.enterprise_form_submit') }}
            </button>
        </form>

        {{-- Contact Info --}}
        <div class="mt-8 pt-6 border-t text-sm text-gray-600 space-y-2">
            <p>
                <strong>{{ __('messages.contact_sales_email_label') }}:</strong>
                <a href="mailto:info@travelainepal.com" class="text-blue-600 hover:underline">info@travelainepal.com</a>
            </p>
            <p>
                <strong>{{ __('messages.contact_sales_phone_label') }}:</strong>
                <a href="tel:+977-9761762036" class="text-blue-600 hover:underline">+977-9761762036</a>
            </p>
        </div>

        <a href="{{ route('home') }}" class="inline-block mt-6 text-blue-600 hover:underline">
            ← {{ __('messages.contact_sales_back') }}
        </a>
    </div>
</div>
@endsection