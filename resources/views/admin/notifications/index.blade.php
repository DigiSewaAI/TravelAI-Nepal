@extends('layouts.admin')

@section('title', 'Notifications | TravelAI Nepal')
@section('header', 'Notifications')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">All Notifications</h2>
            @if($unreadCount > 0)
                <p class="text-sm text-gray-500 mt-1">{{ $unreadCount }} unread</p>
            @endif
        </div>
        @if($unreadCount > 0)
        <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                <i class="fas fa-check-double mr-1"></i> Mark all as read
            </button>
        </form>
        @endif
    </div>

    @if($notifications->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($notifications as $notif)
            <div class="flex items-start gap-3 py-4 {{ $notif->read_at ? '' : 'bg-blue-50 -mx-6 px-6' }}">
                <div class="flex-shrink-0 mt-1">
                    @php
                        $category = $notif->data['category'] ?? 'info';
                        $icon = match($category) {
                            'success' => 'fa-check-circle text-green-500',
                            'warning' => 'fa-exclamation-triangle text-yellow-500',
                            'error'   => 'fa-times-circle text-red-500',
                            default   => 'fa-info-circle text-blue-500',
                        };
                    @endphp
                    <i class="fas {{ $icon }} text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start gap-2">
                        <p class="text-sm font-medium text-gray-800">
                            {{ $notif->data['title'] ?? 'Alert' }}
                        </p>
                        <span class="text-xs text-gray-400 whitespace-nowrap">
                            {{ $notif->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $notif->data['message'] ?? '' }}
                    </p>
                    <div class="flex items-center gap-3 mt-2">
                        @if(!empty($notif->data['url']) && $notif->data['url'] !== '#')
                            <a href="{{ $notif->data['url'] }}" class="text-xs text-blue-600 hover:underline">View →</a>
                        @endif
                        @if(!$notif->read_at)
                        <form action="{{ route('admin.notifications.mark-read', $notif->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-700">Mark read</button>
                        </form>
                        @else
                            <span class="text-xs text-gray-400">Read</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-bell-slash text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No notifications yet.</p>
        </div>
    @endif
</div>
@endsection