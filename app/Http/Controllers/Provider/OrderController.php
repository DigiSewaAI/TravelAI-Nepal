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

        $order->update([
            'payment_verified_at' => now(),
            'payment_verified_by' => auth()->id(),
            'payment_status'      => 'paid',
            'paid_at'             => now(),
            'status'              => 'confirmed',
        ]);

        return back()->with('success', __('messages.order_payment_verified_success'));
    }
}