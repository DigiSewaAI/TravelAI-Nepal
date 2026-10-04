<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private CartService $cart) {}

    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $cartItems = $this->cart->items();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => __('messages.cart_empty'),
                ]);
            }

            $subtotal = 0;
            $itemsData = [];

            foreach ($cartItems as $cart) {
                $product = $cart->product;
                if (!$product) continue;

                $lineTotal = $cart->subtotal;
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'product_id'        => $product->id,
                    'provider_id'       => $product->provider_id,
                    'product_name'      => $product->name,
                    'product_type'      => $product->product_type,
                    'unit_price'        => $product->price,
                    'quantity'          => $cart->quantity,
                    'rental_start_date' => $cart->rental_start_date,
                    'rental_end_date'   => $cart->rental_end_date,
                    'rental_days'       => ($product->isRental() && $cart->rental_start_date && $cart->rental_end_date)
                        ? $cart->rental_start_date->diffInDays($cart->rental_end_date) + 1
                        : null,
                    'rental_deposit'    => $product->isRental()
                        ? ($product->rentalDetail->rental_deposit ?? 0)
                        : null,
                    'line_total'        => $lineTotal,
                ];
            }

            $shippingFee = 0;
            $total = $subtotal + $shippingFee;

            $order = Order::create([
                'user_id'          => $user->id,
                'status'           => 'pending',
                'contact_name'     => $data['contact_name'],
                'contact_email'    => $data['contact_email'],
                'contact_phone'    => $data['contact_phone'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'shipping_city'    => $data['shipping_city'] ?? null,
                'shipping_country' => $data['shipping_country'] ?? null,
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'total'            => $total,
                'currency'         => 'NPR',
                'payment_status'   => 'pending',
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            $this->cart->clear();

            return $order;
        });
    }
}