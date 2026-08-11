@extends('layouts.admin')

@section('title', 'Review Details | TravelAI Nepal')
@section('header', 'Review Details')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <div class="mb-4">
        <a href="{{ route('admin.reviews.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> Back to Reviews
        </a>
    </div>

    <div class="flex justify-between items-start">
        <h2 class="text-2xl font-bold text-gray-900">Review #{{ $review->id }}</h2>
        <span class="px-3 py-1 rounded-full text-sm font-semibold
            @if($review->status === 'approved') bg-green-100 text-green-800
            @elseif($review->status === 'pending') bg-yellow-100 text-yellow-800
            @else bg-red-100 text-red-800 @endif">
            {{ ucfirst($review->status) }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 mt-4 border-t pt-4">
        <div>
            <p class="text-sm text-gray-500">Service</p>
            <p class="font-medium">{{ $review->service->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">User</p>
            <p class="font-medium">{{ $review->user->name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600">{{ $review->user->email ?? '' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Rating</p>
            <p class="font-medium">{{ $review->rating }} ⭐</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Booking</p>
            <p class="font-medium">#{{ $review->booking_id }}</p>
        </div>
        <div class="col-span-2">
            <p class="text-sm text-gray-500">Comment</p>
            <p class="text-gray-700 mt-1">{{ $review->comment ?: 'No comment' }}</p>
        </div>
        <div class="col-span-2">
            <p class="text-sm text-gray-500">Submitted</p>
            <p class="font-medium">{{ $review->created_at->format('M d, Y H:i') }}</p>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        @if($review->status === 'pending')
            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-check mr-1"></i> Approve
                </button>
            </form>
            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-times mr-1"></i> Reject
                </button>
            </form>
        @endif
        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="inline"
              onsubmit="return confirm('Delete this review?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fas fa-trash mr-1"></i> Delete
            </button>
        </form>
        <a href="{{ route('admin.reviews.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
            Back
        </a>
    </div>
</div>
@endsection