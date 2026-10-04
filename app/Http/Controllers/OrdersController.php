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

        // PATH-3B B7: record history (payment notice = same status but notable event)
        \App\Models\OrderStatusHistory::record(
            $order, $order->status, $order->status, null, auth()->id(), 'buyer', 'Payment notice sent'
        );

        return back()->with('success', __('messages.order_payment_notified'));
    }

    // ═══════════════════════════════════════════
    // PATH-3C C2: Buyer marks rental as returned
    // ═══════════════════════════════════════════
    public function requestReturn(Request $request, Order $order, \App\Models\OrderItem $item)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        if ($item->order_id !== $order->id) abort(403);
        if (!$item->isRental()) abort(403, 'Not a rental item');
        if ($item->hasReturnRequest()) {
            return back()->with('info', __('messages.return_already_requested'));
        }
        if (!$item->isReturnConfirmed() && !$item->provider_status !== 'confirmed') {
            // Optional: require provider confirmation before return
            // Skipping for MVP
        }

        $item->update(['return_requested_at' => now()]);

        $this->syncOrderRentalStatus($order);
        $this->notifyProviderReturnRequested($order, $item);

        return back()->with('success', __('messages.return_requested'));
    }

    private function syncOrderRentalStatus(Order $order): void
    {
        $rentalItems = $order->items()->where('product_type', 'rental')->get();
        if ($rentalItems->isEmpty()) return;

        if ($rentalItems->every(fn($i) => $i->isReturnConfirmed())) {
            $order->update(['status' => 'returned']);
        } elseif ($rentalItems->every(fn($i) => $i->hasReturnRequest())) {
            $order->update(['status' => 'returned']);
        } elseif ($rentalItems->contains(fn($i) => $i->isOverdue())) {
            $order->update(['status' => 'overdue']);
        } elseif ($rentalItems->every(fn($i) => $i->provider_status === 'confirmed')) {
            $order->update(['status' => 'picked_up']);
        }
    }

    private function notifyProviderReturnRequested(Order $order, \App\Models\OrderItem $item): void
    {
        try {
            $provider = $item->provider;
            if ($provider?->contact_email) {
                \Mail::to($provider->contact_email)
                    ->queue(new \App\Mail\ReturnRequestedMail($order, $item));
            }
        } catch (\Throwable $e) {
            \Log::warning('ReturnRequestedMail failed', ['order' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}