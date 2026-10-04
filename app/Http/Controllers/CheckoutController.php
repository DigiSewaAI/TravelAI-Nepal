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

        return redirect()->route('orders.show', $order)
            ->with('success', __('messages.order_created'));
    }
}