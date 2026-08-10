<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isProviderOwner() || $user->isSuperAdmin();
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->isSuperAdmin()) return true;

        $provider = $user->ownProvider();
        if (!$provider) return false;

        return $booking->service->provider_id === $provider->id;
    }

    public function update(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }
}