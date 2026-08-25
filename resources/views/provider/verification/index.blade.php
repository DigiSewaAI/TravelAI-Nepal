@extends('layouts.provider')

@section('title', __('messages.verification_page_title'))
@section('header', __('messages.verification_header'))

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Verification Status -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.verification_status') }}</h2>
        <div class="flex items-center gap-4">
            <span class="text-2xl">
                @if($provider->verification_status === 'verified')
                    ✅
                @elseif($provider->verification_status === 'rejected')
                    ❌
                @elseif($provider->verification_status === 'under_review')
                    🔍
                @else
                    ⏳
                @endif
            </span>
            <div>
                <span class="font-semibold text-gray-800">{{ __('messages.status_label') }}: </span>
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    @if($provider->verification_status === 'verified') bg-green-100 text-green-800
                    @elseif($provider->verification_status === 'rejected') bg-red-100 text-red-800
                    @elseif($provider->verification_status === 'under_review') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800 @endif">
                    @if($provider->verification_status === 'verified') {{ __('messages.verified') }}
                    @elseif($provider->verification_status === 'rejected') {{ __('messages.rejected') }}
                    @elseif($provider->verification_status === 'under_review') {{ __('messages.under_review') }}
                    @else {{ __('messages.pending_verification') }} @endif
                </span>
                @if($provider->verification_status === 'verified')
                    <p class="text-sm text-green-600 mt-1">{{ __('messages.verified_message') }}</p>
                @elseif($provider->verification_status === 'pending')
                    <p class="text-sm text-gray-500 mt-1">{{ __('messages.pending_message') }}</p>
                @elseif($provider->verification_status === 'under_review')
                    <p class="text-sm text-yellow-600 mt-1">{{ __('messages.under_review_message') }}</p>
                @elseif($provider->verification_status === 'rejected')
                    <p class="text-sm text-red-600 mt-1">{{ __('messages.rejected_message') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Upload Documents -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.upload_documents') }}</h2>
        <form method="POST" action="{{ route('provider.verification.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.document_type') }}</label>
                    <select name="type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="business_registration">{{ __('messages.business_registration') }}</option>
                        <option value="tourism_license">{{ __('messages.tourism_license') }}</option>
                        <option value="guide_license">{{ __('messages.guide_license') }}</option>
                        <option value="id_card">{{ __('messages.id_card') }}</option>
                        <option value="other">{{ __('messages.other_doc') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.upload_file') }}</label>
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">{{ __('messages.file_hint') }}</p>
                </div>
            </div>
            <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                <i class="fas fa-upload mr-1"></i> {{ __('messages.upload_document_btn') }}
            </button>
        </form>
    </div>

    <!-- Documents List -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.uploaded_documents') }}</h2>
        @if($documents && $documents->count() > 0)
            <div class="space-y-3">
                @foreach($documents as $doc)
                    <div class="flex justify-between items-center border-b pb-3">
                        <div>
                            <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $doc->type)) }}</span>
                            <span class="px-2 py-1 text-xs rounded-full ml-2
                                @if($doc->status === 'approved') bg-green-100 text-green-800
                                @elseif($doc->status === 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                @if($doc->status === 'approved') {{ __('messages.approved') }}
                                @elseif($doc->status === 'rejected') {{ __('messages.rejected') }}
                                @else {{ __('messages.pending') }} @endif
                            </span>
                            <p class="text-sm text-gray-500">{{ $doc->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-eye"></i> {{ __('messages.view') }}
                            </a>
                            <form method="POST" action="{{ route('provider.verification.destroy', $doc) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">{{ __('messages.no_documents') }}</p>
        @endif
    </div>
</div>
@endsection