<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout
    ) {}

    public function index()
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('messages.cart_empty'));
        }

        $subtotal = $this->cart->subtotal();

        return view('checkout.index', compact('items', 'subtotal'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_name'     => 'required|string|max:100',
            'contact_email'    => 'required|email|max:150',
            'contact_phone'    => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_city'    => 'nullable|string|max:100',
            'shipping_country' => 'nullable|string|max:60',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $order = $this->checkout->createOrder(auth()->user(), $validated);

        $providerIds = [];
        foreach ($order->items()->get() as $__it) {
            $__pid = \App\Models\Product::where('id', $__it->product_id)->value('provider_id');
            if ($__pid) $providerIds[] = (int)$__pid;
        }
        $providerIds = array_values(array_unique($providerIds));
        $__orderNum = $order->order_number ?? $order->id;
        $__orderTotal = $order->total ?? 0;
        try {
            if (!empty($providerIds)) {
                foreach ($providerIds as $pid) {
                    $ownerId = \App\Models\Provider::where('id', $pid)->value('user_id');
                    if (!$ownerId) continue;
                    $owner = \App\Models\User::find($ownerId);
                    if ($owner && method_exists($owner, 'notify')) {
                        $owner->notify(new \App\Notifications\ProviderAlertNotification(
                            title: 'New Order Received',
                            message: 'Order ' . $__orderNum . ' — NPR ' . number_format($__orderTotal),
                            category: 'product_order'
                        ));
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notify failed', ['ctx' => 'product_order', 'e' => $e->getMessage()]);
        }

        return redirect()->route('orders.show', $order)
            ->with('success', __('messages.order_created'));
    }
}