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
        // ✅ Phase 1 Services
        $this->app->singleton(\App\Services\Safety\RiskScoringService::class);
        $this->app->singleton(\App\Services\Safety\SafetyStatusService::class);

        // ✅ Phase 2 Services (Source Fetching & Parsing)
        $this->app->singleton(\App\Services\Safety\SourceFetchService::class);
        $this->app->singleton(\App\Services\Safety\IncidentDetectionService::class);
        $this->app->singleton(\App\Services\Safety\LocationResolutionService::class);
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