<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        return view('cart.index', compact('items', 'subtotal'));
    }

    public function add(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity'          => 'required|integer|min:1|max:100',
            'rental_start_date' => 'nullable|date|after_or_equal:today',
            'rental_end_date'   => 'nullable|date|after:rental_start_date',
        ]);

        if ($product->isRental()) {
            if (empty($validated['rental_start_date']) || empty($validated['rental_end_date'])) {
                return back()->with('error', __('messages.cart_rental_dates_required'));
            }

            $days = \Carbon\Carbon::parse($validated['rental_start_date'])
                ->diffInDays($validated['rental_end_date']) + 1;

            $detail = $product->rentalDetail;
            if ($detail && $detail->rental_min_days && $days < $detail->rental_min_days) {
                return back()->with('error', __('messages.cart_min_days_error', ['min' => $detail->rental_min_days]));
            }
            if ($detail && $detail->rental_max_days && $days > $detail->rental_max_days) {
                return back()->with('error', __('messages.cart_max_days_error', ['max' => $detail->rental_max_days]));
            }
        }

        $this->cart->add(
            $product,
            (int) $validated['quantity'],
            $validated['rental_start_date'] ?? null,
            $validated['rental_end_date'] ?? null
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'count'   => $this->cart->count(),
                'message' => __('messages.cart_added'),
            ]);
        }

        return back()->with('success', __('messages.cart_added'));
    }

    public function update(Request $request, Cart $cart)
    {
        $validated = $request->validate(['quantity' => 'required|integer|min:1|max:100']);
        $this->cart->update($cart, (int) $validated['quantity']);

        if ($request->ajax()) {
            return response()->json([
                'success'  => true,
                'subtotal' => $this->cart->subtotal(),
                'message'  => __('messages.cart_updated'),
            ]);
        }

        return back()->with('success', __('messages.cart_updated'));
    }

    public function remove(Request $request, Cart $cart)
    {
        $this->cart->remove($cart);

        if ($request->ajax()) {
            return response()->json([
                'success'  => true,
                'count'    => $this->cart->count(),
                'subtotal' => $this->cart->subtotal(),
                'message'  => __('messages.cart_removed'),
            ]);
        }

        return back()->with('success', __('messages.cart_removed'));
    }
}