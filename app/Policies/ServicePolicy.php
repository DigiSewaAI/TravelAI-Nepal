<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isProviderOwner() || $user->isSuperAdmin();
    }

    public function view(User $user, Service $service): bool
    {
        return $user->isSuperAdmin() || $service->provider_id === $user->ownProvider()?->id;
    }

    public function create(User $user): bool
    {
        return $user->isProviderOwner() || $user->isSuperAdmin();
    }

    public function update(User $user, Service $service): bool
    {
        return $user->isSuperAdmin() || $service->provider_id === $user->ownProvider()?->id;
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->isSuperAdmin() || $service->provider_id === $user->ownProvider()?->id;
    }
}