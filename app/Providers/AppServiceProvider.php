<?php

namespace App\Providers;

use App\Models\Route;
use App\Observers\RouteObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::observe(RouteObserver::class);
    }
}