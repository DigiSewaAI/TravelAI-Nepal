@extends('layouts.admin')

@section('title', 'Manage Reviews | TravelAI Nepal')
@section('header', 'Manage Reviews')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">All Reviews</h2>

    @if($reviews->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Service</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">User</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Rating</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Comment</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviews as $review)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm font-medium">{{ $review->service->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">{{ $review->user->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">{{ $review->rating }} ⭐</td>
                        <td class="py-3 text-sm">{{ Str::limit($review->comment, 50) }}</td>
                        <td class="py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($review->status === 'approved') bg-green-100 text-green-800
                                @elseif($review->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($review->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('admin.reviews.show', $review) }}" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($review->status === 'pending')
                                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline mr-1">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline mr-1">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="inline"
                                  onsubmit="return confirm('Delete this review?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">No reviews found.</p>
    @endif
</div>
@endsection