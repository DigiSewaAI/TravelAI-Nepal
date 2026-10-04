<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index()
    {
        $orders = Order::forUser(auth()->id())
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product', 'items.provider']);

        return view('orders.show', compact('order'));
    }

    public function notifyPayment(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_notice_sent_at !== null) {
            return back()->with('info', __('messages.order_payment_already_notified'));
        }

        $validated = $request->validate([
            'payment_reference' => 'required|string|max:100',
            'payment_note'      => 'nullable|string|max:500',
        ]);

        $order->update([
            'payment_reference'      => $validated['payment_reference'],
            'payment_note'           => $validated['payment_note'] ?? null,
            'payment_notice_sent_at' => now(),
        ]);

        return back()->with('success', __('messages.order_payment_notified'));
    }
}