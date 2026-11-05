<?php

namespace App\Services\Traveler;

use App\Models\Booking;
use App\Models\Order;
use App\Models\WholesaleRfq;

/**
 * UnifiedDashboardService
 *
 * Provides unified data for the V2 traveler dashboard.
 *
 * SCOPE (Phase 3):
 *   - 4 verified categories only: Trekking, Shop, Rental, Wholesale
 *   - Hotel / Restaurant / Transport / ArtCraft / MoneyChanger: NOT covered
 *     (requires ownership migration / claim flow — future phase)
 *
 * CLASSIFICATION RULES:
 *   - Pure Shop:    ≥1 shop item AND 0 non-shop items (incl. null/unknown)
 *   - Pure Rental:  ≥1 rental item AND 0 non-rental items (incl. null/unknown)
 *   - Mixed:        Any order with >1 distinct type, OR unknown/null type,
 *                   OR no items. Mixed orders appear ONLY in "All" tab.
 *
 * SAFETY: Unknown/future product_type values are NEVER classified as
 *         Pure Shop or Pure Rental. They always fall into Mixed.
 */
class UnifiedDashboardService
{
    private const PER_PAGE = 15;
    private const KNOWN_TYPES = ['shop', 'rental'];

    /**
     * Get all data needed by the V2 traveler dashboard.
     */
    public function getData(int $userId): array
    {
        // Phase 4.4 — Claimed counts per category
        $claimedCounts = [
            'restaurant' => \App\Models\BookingClaim::where('traveler_id', $userId)
                ->where('claimable_type', \App\Models\RestaurantOrder::class)->count(),
            'transport' => \App\Models\BookingClaim::where('traveler_id', $userId)
                ->where('claimable_type', \App\Models\TransportBooking::class)->count(),
            'hotel' => \App\Models\BookingClaim::where('traveler_id', $userId)
                ->where('claimable_type', \App\Models\HotelBooking::class)->count(),
            'artcraft' => \App\Models\BookingClaim::where('traveler_id', $userId)
                ->where('claimable_type', \App\Models\CustomOrder::class)->count(),
        ];
        // Phase 4.4 — Fetch claimed orders for My Trips tabs
        $claimedRestaurant = \App\Models\BookingClaim::where('traveler_id', $userId)
            ->where('claimable_type', \App\Models\RestaurantOrder::class)
            ->with('claimable')
            ->latest('claimed_at')
            ->get()
            ->pluck('claimable')
            ->filter();

        $claimedTransport = \App\Models\BookingClaim::where('traveler_id', $userId)
            ->where('claimable_type', \App\Models\TransportBooking::class)
            ->with('claimable')
            ->latest('claimed_at')
            ->get()
            ->pluck('claimable')
            ->filter();

        $claimedHotel = \App\Models\BookingClaim::where('traveler_id', $userId)
            ->where('claimable_type', \App\Models\HotelBooking::class)
            ->with('claimable')
            ->latest('claimed_at')
            ->get()
            ->pluck('claimable')
            ->filter();
        $claimedArtcraft = \App\Models\BookingClaim::where('traveler_id', $userId)
            ->where('claimable_type', \App\Models\CustomOrder::class)
            ->with('claimable')
            ->latest('claimed_at')
            ->get()
            ->pluck('claimable')
            ->filter();
        return [
            'counts' => $this->getCounts($userId),
            'treks' => $this->getTreks($userId),
            'allOrders' => $this->getAllOrders($userId),
            'shopOrders' => $this->getPureOrders($userId, 'shop'),
            'rentalOrders' => $this->getPureOrders($userId, 'rental'),
            'rfqs' => $this->getWholesaleRfqs($userId),
            'mixedOrderIds' => $this->getMixedOrderIds($userId),
            'claimedCounts' => $claimedCounts,
            'claimedRestaurant' => $claimedRestaurant,
            'claimedTransport' => $claimedTransport,
            'claimedHotel' => $claimedHotel,
            'claimedArtcraft' => $claimedArtcraft,
        ];
    }

    private function getCounts(int $userId): array
    {
        return [
            'trekking' => Booking::where('traveler_id', $userId)->count(),
            'shop' => $this->pureOrdersQuery($userId, 'shop')->count(),
            'rental' => $this->pureOrdersQuery($userId, 'rental')->count(),
            'wholesale' => WholesaleRfq::where('buyer_id', $userId)->count(),
            'all_orders' => Order::where('user_id', $userId)->count(),
        ];
    }

    private function getTreks(int $userId)
    {
        return Booking::where('traveler_id', $userId)
            ->with('service')
            ->latest()
            ->paginate(self::PER_PAGE, ['*'], 'treks_page');
    }

    private function getAllOrders(int $userId)
    {
        return Order::where('user_id', $userId)
            ->with(['items:id,order_id,product_type,product_name,quantity'])
            ->latest()
            ->paginate(self::PER_PAGE, ['*'], 'all_orders_page');
    }

    private function getPureOrders(int $userId, string $type)
    {
        return $this->pureOrdersQuery($userId, $type)
            ->with(['items:id,order_id,product_type,product_name,quantity'])
            ->latest()
            ->paginate(self::PER_PAGE, ['*'], "{$type}_page");
    }

    /**
     * Pure orders = has ≥1 item of $type AND zero items of other type
     * (including null and unknown future types).
     */
    private function pureOrdersQuery(int $userId, string $type)
    {
        return Order::where('user_id', $userId)
            ->whereHas('items', fn($q) => $q->where('product_type', $type))
            ->whereDoesntHave('items', function ($q) use ($type) {
                $q->where(function ($sub) use ($type) {
                    $sub->where('product_type', '!=', $type)
                        ->orWhereNull('product_type');
                });
            });
    }

    private function getWholesaleRfqs(int $userId)
    {
        return WholesaleRfq::where('buyer_id', $userId)
            ->with(['product', 'provider'])
            ->latest()
            ->paginate(self::PER_PAGE, ['*'], 'rfqs_page');
    }

    /**
     * Compute mixed order IDs for the current user.
     * Mixed = any order that is NOT Pure Shop and NOT Pure Rental.
     */
    private function getMixedOrderIds(int $userId): array
    {
        $orders = Order::where('user_id', $userId)
            ->with(['items:id,order_id,product_type'])
            ->get(['id']);

        $mixedIds = [];
        foreach ($orders as $order) {
            if ($this->isOrderMixed($order)) {
                $mixedIds[] = $order->id;
            }
        }

        return $mixedIds;
    }

    private function isOrderMixed(Order $order): bool
    {
        if ($order->items->isEmpty()) {
            return true;
        }

        $rawTypes = $order->items->pluck('product_type')->toArray();
        $knownTypes = [];

        foreach ($rawTypes as $type) {
            if ($type === null || !in_array($type, self::KNOWN_TYPES, true)) {
                return true;
            }
            $knownTypes[$type] = true;
        }

        return count($knownTypes) > 1;
    }
}