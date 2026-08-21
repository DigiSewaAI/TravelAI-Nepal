<?php

namespace App\Observers;

use App\Models\Route;

class RouteObserver
{
    /**
     * Handle the Route "deleting" event (Soft Delete).
     */
    public function deleting(Route $route): void
    {
        // Soft delete related segments and costs
        $route->segments()->delete();
        $route->costs()->delete();
    }

    /**
     * Handle the Route "forceDeleting" event (Permanent Delete).
     */
    public function forceDeleting(Route $route): void
    {
        // Permanently delete related records
        $route->segments()->forceDelete();
        $route->costs()->forceDelete();
    }
}