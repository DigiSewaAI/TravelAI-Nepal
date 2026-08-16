<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice)
    {
        // Provider can view own invoices
        if ($user->isProviderOwner() && $user->provider_id === $invoice->provider_id) {
            return true;
        }
        // Admin can view all
        if ($user->isSuperAdmin()) {
            return true;
        }
        return false;
    }

    public function viewAny(User $user)
    {
        return $user->isSuperAdmin() || $user->isProviderOwner();
    }
}