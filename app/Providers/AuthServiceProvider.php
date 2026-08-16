<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Import Models
use App\Models\Service;
use App\Models\Booking;
use App\Models\Invoice;          // <-- Added

// Import Policies
use App\Policies\ServicePolicy;
use App\Policies\BookingPolicy;
use App\Policies\InvoicePolicy;  // <-- Added

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Service::class => ServicePolicy::class,
        Booking::class => BookingPolicy::class,
        Invoice::class => InvoicePolicy::class, // <-- Added
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // (Optional) Custom gates
        // Gate::define('manage-provider', function ($user) {
        //     return $user->isProviderOwner() || $user->isSuperAdmin();
        // });
    }
}