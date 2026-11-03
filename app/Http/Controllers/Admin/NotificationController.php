<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();

        // AJAX (bell dropdown) → JSON; browser → Blade page
        if (request()->wantsJson() || request()->ajax()) {
            $notifications = $user->notifications()->latest()->take(10)->get();

            return response()->json([
                'notifications' => $notifications->map(fn($n) => [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Alert',
                    'message' => $n->data['message'] ?? '',
                    'category' => $n->data['category'] ?? 'info',
                    'url' => $n->data['url'] ?? '#',
                    'read' => $n->read_at !== null,
                    'created_at' => $n->created_at->diffForHumans(),
                ]),
                'unread_count' => $unreadCount,
            ]);
        }

        $notifications = $user->notifications()->latest()->paginate(20);

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}