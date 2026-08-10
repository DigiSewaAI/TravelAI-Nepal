@extends('layouts.provider')

@section('title', 'Verification | TravelAI Nepal')
@section('header', 'Provider Verification')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Verification Status -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Verification Status</h2>
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
                <span class="font-semibold text-gray-800">Status: </span>
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    @if($provider->verification_status === 'verified') bg-green-100 text-green-800
                    @elseif($provider->verification_status === 'rejected') bg-red-100 text-red-800
                    @elseif($provider->verification_status === 'under_review') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst(str_replace('_', ' ', $provider->verification_status)) }}
                </span>
                @if($provider->verification_status === 'verified')
                    <p class="text-sm text-green-600 mt-1">Your account is verified. You have a verified badge on your profile.</p>
                @elseif($provider->verification_status === 'pending')
                    <p class="text-sm text-gray-500 mt-1">Please upload required documents to start verification.</p>
                @elseif($provider->verification_status === 'under_review')
                    <p class="text-sm text-yellow-600 mt-1">Your documents are being reviewed by the admin team.</p>
                @elseif($provider->verification_status === 'rejected')
                    <p class="text-sm text-red-600 mt-1">Your verification was rejected. Please upload valid documents.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Upload Documents -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Upload Documents</h2>
        <form method="POST" action="{{ route('provider.verification.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Document Type</label>
                    <select name="type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="business_registration">Business Registration</option>
                        <option value="tourism_license">Tourism License</option>
                        <option value="guide_license">Guide License</option>
                        <option value="id_card">ID Card / Passport</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Upload File</label>
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG (max 5MB)</p>
                </div>
            </div>
            <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                <i class="fas fa-upload mr-1"></i> Upload Document
            </button>
        </form>
    </div>

    <!-- Documents List -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Uploaded Documents</h2>
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
                                {{ ucfirst($doc->status) }}
                            </span>
                            <p class="text-sm text-gray-500">{{ $doc->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-eye"></i> View
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
            <p class="text-gray-500 text-center py-4">No documents uploaded yet.</p>
        @endif
    </div>
</div>
@endsection