<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();

        // 🔔 Bell Dropdown (AJAX) → JSON return गर्ने
        if ($request->wantsJson() || $request->ajax()) {
            $notifications = $user->notifications()->latest()->take(10)->get();

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

        // 🌐 Browser (View All) → सुन्दर Blade page देखाउने
        $notifications = $user->notifications()->latest()->paginate(20);
        return view('traveler.notifications.index', compact('notifications', 'unreadCount'));
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