<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartService
{
    private function getIdentity(): array
    {
        if (Auth::check()) {
            return ['user_id' => Auth::id(), 'session_id' => null];
        }
        return ['user_id' => null, 'session_id' => session()->getId()];
    }

    private function applyIdentity($query)
    {
        if (Auth::check()) {
            return $query->where('user_id', Auth::id());
        }
        return $query->where('session_id', session()->getId());
    }

    public function items()
    {
        return $this->applyIdentity(Cart::query())
            ->with(['product.provider', 'product.shopDetail', 'product.rentalDetail', 'product.wholesaleDetail'])
            ->get();
    }

    public function count(): int
    {
        return (int) $this->applyIdentity(Cart::query())->sum('quantity');
    }

    public function add(Product $product, int $quantity = 1, ?string $startDate = null, ?string $endDate = null): Cart
    {
        $query = $this->applyIdentity(Cart::query())->where('product_id', $product->id);

        if ($product->isRental() && $startDate && $endDate) {
            $query->where('rental_start_date', $startDate)
                  ->where('rental_end_date', $endDate);
        } else {
            $query->whereNull('rental_start_date');
        }

        $existing = $query->first();

        if ($existing) {
            $existing->quantity += $quantity;
            $existing->save();
            return $existing;
        }

        $identity = $this->getIdentity();

        return Cart::create(array_merge($identity, [
            'product_id'        => $product->id,
            'quantity'          => $quantity,
            'rental_start_date' => $startDate,
            'rental_end_date'   => $endDate,
        ]));
    }

    public function update(Cart $cart, int $quantity): Cart
    {
        $cart->quantity = max(1, $quantity);
        $cart->save();
        return $cart;
    }

    public function remove(Cart $cart): void
    {
        $cart->delete();
    }

    public function clear(): void
    {
        $this->applyIdentity(Cart::query())->delete();
    }

    public function subtotal(): float
    {
        return $this->items()->sum(fn($item) => $item->subtotal);
    }

    public function mergeGuestCart(int $userId): void
    {
        $sessionId = session()->getId();
        $guestItems = Cart::where('session_id', $sessionId)->get();

        foreach ($guestItems as $guest) {
            $existing = Cart::where('user_id', $userId)
                ->where('product_id', $guest->product_id)
                ->where('rental_start_date', $guest->rental_start_date)
                ->where('rental_end_date', $guest->rental_end_date)
                ->first();

            if ($existing) {
                $existing->quantity += $guest->quantity;
                $existing->save();
                $guest->delete();
            } else {
                $guest->user_id = $userId;
                $guest->session_id = null;
                $guest->save();
            }
        }
    }
}