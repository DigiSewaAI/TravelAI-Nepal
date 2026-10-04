<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Decrement stock for shop items (called on order creation).
     */
    public function decrementForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            if (!$item->isShop()) continue;

            $detail = $item->product?->shopDetail;
            if (!$detail || $detail->stock_count === null) continue;

            DB::table('shop_details')
                ->where('id', $detail->id)
                ->where('stock_count', '>=', $item->quantity)
                ->decrement('stock_count', $item->quantity);
        }
    }

    /**
     * Restore stock (called on cancel).
     */
    public function restoreForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            if (!$item->isShop()) continue;

            $detail = $item->product?->shopDetail;
            if (!$detail || $detail->stock_count === null) continue;

            DB::table('shop_details')
                ->where('id', $detail->id)
                ->increment('stock_count', $item->quantity);
        }
    }

    /**
     * Check if shop product has enough stock.
     */
    public function hasStock(Product $product, int $qty): bool
    {
        if (!$product->isShop()) return true;

        $detail = $product->shopDetail;
        if (!$detail || $detail->stock_count === null) return true;

        return $detail->stock_count >= $qty;
    }

    /**
     * Check rental availability for date range (no overlap).
     */
    public function isRentalAvailable(Product $product, string $startDate, string $endDate, ?int $excludeOrderId = null): bool
    {
        if (!$product->isRental()) return true;

        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $product->id)
            ->whereIn('orders.status', ['pending', 'confirmed', 'processing', 'picked_up'])
            ->whereNull('order_items.return_confirmed_at')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('order_items.rental_start_date', '<=', $endDate)
                  ->where('order_items.rental_end_date', '>=', $startDate);
            });

        if ($excludeOrderId) {
            $query->where('orders.id', '!=', $excludeOrderId);
        }

        return $query->count() === 0;
    }
}