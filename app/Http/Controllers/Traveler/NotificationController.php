<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->take(10)->get();
        $unreadCount = auth()->user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications->map(function ($n) {
                $data = $n->data;
                $url = $data['url'] ?? '#';
                $category = $data['category'] ?? 'info';

                // 🆕 Broadcast announcements: dedicated page (not home redirect)
                if (($data['type'] ?? null) === 'admin_broadcast') {
                    $url = route('traveler.notifications.show', $n->id);
                    $category = 'announcement';
                }

                return [
                    'id' => $n->id,
                    'title' => $data['title'] ?? 'Alert',
                    'message' => $data['message'] ?? '',
                    'category' => $category,
                    'url' => $url,
                    'read' => $n->read_at !== null,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    /**
     * 🆕 Show full notification (for announcements / broadcasts)
     */
    public function show($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // Auto-mark as read on view
        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return view('traveler.notifications.show', compact('notification'));
    }

    public function markAllAsRead()
{
    auth()->user()->unreadNotifications->markAsRead();

    if (request()->wantsJson() || request()->ajax()) {
        return response()->json(['success' => true]);
    }

    return back()->with('success', 'All notifications marked as read.');
}
}