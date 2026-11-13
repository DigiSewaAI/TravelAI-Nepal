@extends('layouts.traveler-v2')

@section('title', 'Notifications | TravelAI Nepal')

@section('traveler-v2-content')
<div style="max-width: 900px; margin: 32px auto; padding: 0 16px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">🔔 Notifications</h1>
            <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0;">
                {{ $unreadCount }} unread · {{ $notifications->total() }} total
            </p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('traveler.notifications.mark-all-read') }}">
                @csrf
                <button type="submit" style="background: #6366f1; color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; font-size: 14px;">
                    ✓ Mark All as Read
                </button>
            </form>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 48px 24px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">🔔</div>
            <h2 style="font-size: 18px; color: #111827; margin: 0 0 8px;">No notifications yet</h2>
            <p style="color: #6b7280; margin: 0;">You'll see booking updates, announcements, and alerts here.</p>
        </div>
    @else
        <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
            @foreach($notifications as $notification)
                @php
                    $data = $notification->data;
                    $url = $data['url'] ?? '#';
                    $category = $data['category'] ?? 'info';
                    $isRead = $notification->read_at !== null;

                    // Broadcast → dedicated page
                    if (($data['type'] ?? null) === 'admin_broadcast') {
                        $url = route('traveler.notifications.show', $notification->id);
                        $category = 'announcement';
                    }
                    // Booking status fallback URL
                    if ($url === '#' && isset($data['booking_id'])) {
                        $url = route('traveler.bookings.show', $data['booking_id']);
                    }

                    // Fallback title based on category
                    $title = $data['title'] ?? match($category) {
                        'booking' => 'Booking Update',
                        'order' => 'Order Update',
                        'rfq' => 'Wholesale RFQ',
                        'announcement' => 'Announcement',
                        default => 'Alert',
                    };

                    $catColors = [
                        'announcement' => ['#ede9fe', '📢'],
                        'booking'      => ['#dbeafe', '📅'],
                        'rfq'          => ['#e0e7ff', '📋'],
                        'order'        => ['#dcfce7', '📦'],
                        'info'         => ['#f3f4f6', 'ℹ️'],
                    ];
                    [$bg, $icon] = $catColors[$category] ?? $catColors['info'];
                @endphp
                <div style="border-bottom: 1px solid #f3f4f6; padding: 16px 20px; display: flex; align-items: start; gap: 14px; {{ !$isRead ? 'background: #fafbff;' : '' }}">
                    <div style="width: 40px; height: 40px; background: {{ $bg }}; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                        {{ $icon }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <h3 style="font-size: 15px; font-weight: 600; color: #111827; margin: 0 0 4px;">
                            @if(!$isRead)
                                <span style="display: inline-block; width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; margin-right: 6px; vertical-align: middle;"></span>
                            @endif
                            {{ $title }}
                        </h3>
                        <p style="font-size: 14px; color: #4b5563; margin: 0 0 8px; line-height: 1.5;">{{ $data['message'] ?? '' }}</p>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 12px; color: #9ca3af;">{{ $notification->created_at->diffForHumans() }}</span>
                            @if($url !== '#')
                                <a href="{{ $url }}" style="font-size: 13px; color: #2563eb; font-weight: 600; text-decoration: none;">View →</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 20px;">
            {{ $notifications->links() }}
        </div>
    @endif

    <div style="margin-top: 24px; text-align: center;">
        <a href="{{ route('traveler.dashboard-v2') }}" style="color: #6b7280; text-decoration: none; font-size: 14px;">
            ← Back to Dashboard
        </a>
    </div>
</div>
@endsection