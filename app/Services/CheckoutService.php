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

            // PATH-3B B5: capture payment methods snapshot per provider
            $providerIds = collect($itemsData)->pluck('provider_id')->unique();
            $paymentMethodsSnapshot = [];
            foreach ($providerIds as $pid) {
                $provider = \App\Models\Provider::find($pid);
                if ($provider) {
                    $methods = $provider->paymentMethods()
                        ->where('is_active', true)
                        ->get(['type', 'label', 'account_name', 'account_number', 'identifier', 'swift_code', 'bank_name', 'instructions', 'qr_image_path']);
                    $paymentMethodsSnapshot[$pid] = [
                        'provider_name' => $provider->name,
                        'methods'       => $methods->toArray(),
                    ];
                }
            }

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
                'currency'                 => 'NPR',
                'payment_status'           => 'pending',
                'payment_methods_snapshot' => $paymentMethodsSnapshot,
                'notes'                    => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            // PATH-3B B7: record initial history
            \App\Models\OrderStatusHistory::record(
                $order, 'pending', null, null, $user->id, 'buyer', 'Order placed'
            );

            // PATH-3B B7: notify each provider (queued)
            foreach ($providerIds as $pid) {
                $p = \App\Models\Provider::find($pid);
                if ($p && $p->contact_email) {
                    try {
                        \Mail::to($p->contact_email)
                            ->queue(new \App\Mail\OrderPlacedMail($order, $p));
                    } catch (\Throwable $e) {
                        \Log::warning('OrderPlacedMail failed', [
                            'order_id' => $order->id, 'provider_id' => $pid, 'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $this->cart->clear();

            return $order;
        });
    }
}