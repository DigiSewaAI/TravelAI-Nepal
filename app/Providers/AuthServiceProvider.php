<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Import Models
use App\Models\Service;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;          // <-- Added for Staff Policy

// Import Policies
use App\Policies\ServicePolicy;
use App\Policies\BookingPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\ProviderPolicy;  // <-- Added for User/Staff

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
        Invoice::class => InvoicePolicy::class,
        User::class    => ProviderPolicy::class, // <-- Staff/Provider authorization
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // (Optional) Custom gates – यहाँ आवश्यक भएमा थप्न सक्नुहुन्छ
        // Gate::define('manage-provider', function ($user) {
        //     return $user->isProviderOwner() || $user->isSuperAdmin();
        // });
    }
}