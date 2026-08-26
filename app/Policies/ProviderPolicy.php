<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProviderPolicy
{
    /**
     * Determine if the user can view any staff.
     */
    public function viewAny(User $user): bool
    {
        return $user->provider_id !== null;
    }

    /**
     * Determine if the user can view staff.
     */
    public function view(User $user, User $staff): bool
    {
        return $user->provider_id === $staff->provider_id;
    }

    /**
     * Determine if the user can create staff.
     */
    public function create(User $user): bool
    {
        return $user->provider_id !== null;
    }

    /**
     * Determine if the user can update staff.
     */
    public function update(User $user, User $staff): bool
    {
        return $user->provider_id === $staff->provider_id;
    }

    /**
     * Determine if the user can delete staff.
     */
    public function delete(User $user, User $staff): bool
    {
        return $user->provider_id === $staff->provider_id;
    }
}