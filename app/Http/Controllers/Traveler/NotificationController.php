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
            'notifications' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'message' => $n->data['message'] ?? '',
                'url' => $n->data['url'] ?? '#',
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
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