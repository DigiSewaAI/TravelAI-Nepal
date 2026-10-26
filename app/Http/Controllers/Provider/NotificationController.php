<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->take(10)->get();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications->map(function ($n) {
                $data = $n->data;
                $title = $data['title'] ?? null;
                $message = $data['message'] ?? 'New notification';
                $url = $data['url'] ?? null;
                $category = $data['category'] ?? 'info';

                // Build URL/title based on notification type
                if (isset($data['quotation_request_id'])) {
                    $title = $title ?? 'New Quotation Request';
                    $url = $url ?? route('provider.quotation-requests.show', $data['quotation_request_id']);
                    $category = 'quotation';
                } elseif (isset($data['quotation_id'])) {
                    $title = $title ?? 'Quotation Ready';
                    $url = $url ?? route('provider.quotation-requests.show', $data['quotation_id']);
                    $category = 'quotation';
                } elseif (isset($data['order_id'])) {
                    $title = $title ?? 'Order Update';
                    $url = $url ?? route('provider.orders.show', $data['order_id']);
                    $category = 'order';
                } elseif (isset($data['booking_id'])) {
                    $title = $title ?? 'Booking Update';
                    $url = $url ?? route('provider.bookings.show', $data['booking_id']);
                    $category = 'booking';
                }

                return [
                    'id' => $n->id,
                    'title' => $title ?? 'Alert',
                    'message' => $message,
                    'category' => $category,
                    'url' => $url ?? route('provider.dashboard'),
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

        // Cascade: mark linked quotation request as viewed
        $data = $notification->data;
        if (isset($data['quotation_request_id'])) {
            \App\Models\QuotationRequest::where('id', $data['quotation_request_id'])
                ->where('status', 'pending')
                ->update(['status' => 'viewed']);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return back();
    }

       public function markAllAsRead()
    {
        $user = auth()->user();

        // 1. Get unread notifications (lightweight — sirf data column)
        $unread = $user->unreadNotifications()->get(['id', 'data']);

        $quotationIds = [];
        foreach ($unread as $notification) {
            $data = $notification->data;
            if (isset($data['quotation_request_id'])) {
                $quotationIds[] = $data['quotation_request_id'];
            }
        }

        // 2. ✅ BULK UPDATE — ekै query ma sabai read
        $user->unreadNotifications()->update(['read_at' => now()]);

        // 3. Cascade — linked quotations viewed
        if (!empty($quotationIds)) {
            \App\Models\QuotationRequest::whereIn('id', $quotationIds)
                ->where('status', 'pending')
                ->update(['status' => 'viewed']);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'All notifications marked as read.');
    }
}