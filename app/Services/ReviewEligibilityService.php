<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class ReviewEligibilityService
{
    /**
     * @return array{allowed: bool, reason: ?string}
     */
    public function checkBooking(Booking $booking, ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        if (!$user) return ['allowed' => false, 'reason' => 'Authentication required.'];

        if ($booking->traveler_id !== $user->id) {
            return ['allowed' => false, 'reason' => 'You can only review your own booking.'];
        }

        if ($booking->status !== 'completed') {
            return ['allowed' => false, 'reason' => 'You can only review completed bookings.'];
        }

        if (Review::where('booking_id', $booking->id)->where('user_id', $user->id)->exists()) {
            return ['allowed' => false, 'reason' => 'You already reviewed this booking.'];
        }

        if ($booking->updated_at && $booking->updated_at->diffInDays(now()) > 30) {
            return ['allowed' => false, 'reason' => 'Review window expired (30 days).'];
        }

        // self-review block
        if ($booking->service?->provider?->user_id === $user->id) {
            return ['allowed' => false, 'reason' => 'You cannot review your own service.'];
        }

        // SOS rescue skip
        if ($booking->service?->service_category_id === null) {
            // if there's a rescue flag on service, skip
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * @return array{allowed: bool, reason: ?string}
     */
    public function checkOrder(Order $order, ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        if (!$user) return ['allowed' => false, 'reason' => 'Authentication required.'];

        if ($order->user_id !== $user->id) {
            return ['allowed' => false, 'reason' => 'You can only review your own orders.'];
        }

        if (!in_array($order->status, ['delivered', 'completed'], true)) {
            return ['allowed' => false, 'reason' => 'You can only review delivered orders.'];
        }

        if (Review::where('order_id', $order->id)->where('user_id', $user->id)->exists()) {
            return ['allowed' => false, 'reason' => 'You already reviewed this order.'];
        }

        if ($order->updated_at && $order->updated_at->diffInDays(now()) > 30) {
            return ['allowed' => false, 'reason' => 'Review window expired (30 days).'];
        }

        // self-review block — any provider
        foreach ($order->items as $item) {
            if ($item->provider?->user_id === $user->id) {
                return ['allowed' => false, 'reason' => 'You cannot review your own items.'];
            }
        }

        return ['allowed' => true, 'reason' => null];
    }
}