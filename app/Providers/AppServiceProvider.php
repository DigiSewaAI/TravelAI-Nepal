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
        // ✅ Register Safety Services as singletons
        $this->app->singleton(\App\Services\Safety\RiskScoringService::class);
        $this->app->singleton(\App\Services\Safety\SafetyStatusService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Route Observer
        Route::observe(RouteObserver::class);

        // ✅ Force Translation Loader to use `lang/` folder with namespace
        $this->loadTranslationsFrom(base_path('lang'), 'messages');
    }
}