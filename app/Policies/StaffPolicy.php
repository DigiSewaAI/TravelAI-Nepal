<?php

namespace App\Policies;

use App\Models\ProviderStaff;
use App\Models\User;

/**
 * FIX-14: Canonical staff membership policy.
 *
 * Scope: Provider-owner only.
 * Provider staff is managed exclusively by the provider owner —
 * super admin does not operate at this layer (platform-level concerns
 * are out of scope for this policy).
 */
class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isProviderOwner();
    }

    public function view(User $user, ProviderStaff $staff): bool
    {
        if (!$user->isProviderOwner()) {
            return false;
        }

        return $staff->provider_id === $user->ownProvider()?->id;
    }

    public function create(User $user): bool
    {
        return $user->isProviderOwner();
    }

    public function update(User $user, ProviderStaff $staff): bool
    {
        return $this->view($user, $staff);
    }

    public function delete(User $user, ProviderStaff $staff): bool
    {
        return $this->view($user, $staff);
    }
}