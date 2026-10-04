<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function getProvider()
    {
        $provider = Auth::user()->ownProvider();
        if (!$provider) {
            abort(403);
        }
        return $provider;
    }

    public function index(Request $request)
    {
        $provider = $this->getProvider();
        $status = $request->query('status', 'all');

        $orderIds = OrderItem::where('provider_id', $provider->id)
            ->when($status !== 'all', fn($q) => $q->where('provider_status', $status))
            ->pluck('order_id')
            ->unique();

        $orders = Order::whereIn('id', $orderIds)
            ->with([
                'user',
                'items' => fn($q) => $q->where('provider_id', $provider->id),
            ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all'       => OrderItem::where('provider_id', $provider->id)->distinct('order_id')->count('order_id'),
            'pending'   => OrderItem::where('provider_id', $provider->id)->where('provider_status', 'pending')->distinct('order_id')->count('order_id'),
            'confirmed' => OrderItem::where('provider_id', $provider->id)->where('provider_status', 'confirmed')->distinct('order_id')->count('order_id'),
            'shipped'   => OrderItem::where('provider_id', $provider->id)->where('provider_status', 'shipped')->distinct('order_id')->count('order_id'),
            'delivered' => OrderItem::where('provider_id', $provider->id)->where('provider_status', 'delivered')->distinct('order_id')->count('order_id'),
        ];

        return view('provider.orders.index', compact('orders', 'status', 'counts'));
    }

    public function show(Order $order)
    {
        $provider = $this->getProvider();

        $items = $order->items()->where('provider_id', $provider->id)->get();
        if ($items->isEmpty()) {
            abort(403);
        }

        $order->load('user');

        return view('provider.orders.show', compact('order', 'items'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $provider = $this->getProvider();

        // PATH-3B B4: verify provider has items in this order (ownership)
        if ($order->items()->where('provider_id', $provider->id)->doesntExist()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;

        $updated = DB::transaction(function () use ($order, $provider, $validated) {
            $count = OrderItem::where('order_id', $order->id)
                ->where('provider_id', $provider->id)
                ->update([
                    'provider_status' => $validated['status'],
                    'provider_status_updated_at' => now(),
                ]);

            $allStatuses = OrderItem::where('order_id', $order->id)
                ->pluck('provider_status')
                ->unique();

            if ($allStatuses->count() === 1) {
                $order->status = $allStatuses->first();
                $order->save();
            }

            return $count;
        });

        // PATH-3C C3: restore stock on cancel
        if ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled') {
            app(\App\Services\InventoryService::class)->restoreForOrder($order);
        }

        // PATH-3B B7: record history + email
        \App\Models\OrderStatusHistory::record(
            $order, $validated['status'], $oldStatus, null, auth()->id(), 'provider', 'Provider updated status'
        );

        try {
            \Mail::to($order->user->email)
                ->queue(new \App\Mail\OrderStatusChangedMail($order, $oldStatus, $validated['status']));
        } catch (\Throwable $e) {
            \Log::warning('OrderStatusChangedMail failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', __('messages.provider_order_status_updated', ['count' => $updated]));
    }

    public function verifyPayment(Order $order)
    {
        $provider = $this->getProvider();

        if ($order->items()->where('provider_id', $provider->id)->doesntExist()) {
            abort(403);
        }

        if ($order->payment_verified_at !== null) {
            return back()->with('info', __('messages.order_payment_already_verified'));
        }

        $oldStatus = $order->status;

        $order->update([
            'payment_verified_at' => now(),
            'payment_verified_by' => auth()->id(),
            'payment_status'      => 'paid',
            'paid_at'             => now(),
            'status'              => 'confirmed',
        ]);

        // PATH-3B B7: record history + email
        \App\Models\OrderStatusHistory::record(
            $order, 'confirmed', $oldStatus, null, auth()->id(), 'provider', 'Payment verified'
        );

        try {
            \Mail::to($order->user->email)->queue(new \App\Mail\OrderPaymentVerifiedMail($order));
        } catch (\Throwable $e) {
            \Log::warning('OrderPaymentVerifiedMail failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', __('messages.order_payment_verified_success'));
    }

    // ═══════════════════════════════════════════
    // PATH-3C C2: Provider confirms return + condition
    // ═══════════════════════════════════════════
    public function confirmReturn(Request $request, Order $order, \App\Models\OrderItem $item)
    {
        $provider = $this->getProvider();
        if ($order->items()->where('provider_id', $provider->id)->doesntExist()) abort(403);
        if ($item->provider_id !== $provider->id) abort(403);
        if (!$item->isRental()) abort(403);
        if ($item->isReturnConfirmed()) {
            return back()->with('info', __('messages.return_already_confirmed'));
        }

        $validated = $request->validate([
            'return_condition' => 'required|in:good,damaged,lost',
            'return_notes'     => 'nullable|string|max:1000',
        ]);

        $item->return_condition = $validated['return_condition'];
        $refundAmount = $item->calculateDepositRefund();
        $lateFee = $item->calculateLateFee();
        $finalRefund = max(0, $refundAmount - $lateFee);

        $item->update([
            'return_confirmed_at'   => now(),
            'return_condition'      => $validated['return_condition'],
            'return_notes'          => $validated['return_notes'] ?? null,
            'deposit_refund_amount' => $finalRefund,
            'deposit_refunded_at'   => now(),
        ]);

        // Update order-level deposit_refunded_amount
        $order->deposit_refunded_amount = $order->items()
            ->whereNotNull('deposit_refunded_at')
            ->sum('deposit_refund_amount');
        $order->deposit_refunded_at = now();
        $order->save();

        // Notify buyer
        try {
            \Mail::to($order->user->email)
                ->queue(new \App\Mail\ReturnConfirmedMail($order, $item, $finalRefund));
        } catch (\Throwable $e) {
            \Log::warning('ReturnConfirmedMail failed', ['order' => $order->id, 'error' => $e->getMessage()]);
        }

        // Record history
        \App\Models\OrderStatusHistory::record(
            $order, 'returned', $order->status, null, auth()->id(), 'provider',
            'Return confirmed: ' . $validated['return_condition']
        );

        return back()->with('success', __('messages.return_confirmed'));
    }
}