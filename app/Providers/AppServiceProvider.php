<?php

namespace App\Providers;

use App\Models\Route;
use App\Observers\RouteObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        // F8-03: Force HTTPS scheme in production (behind TLS-terminating proxy)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register Route Observer
        Route::observe(RouteObserver::class);

        // ✅ Force Translation Loader to use `lang/` folder with namespace
        $this->loadTranslationsFrom(base_path('lang'), 'messages');

        // FIX-11: Named rate limiters
        RateLimiter::for('api', fn (Request $request) =>
            Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())
        );

        RateLimiter::for('ai', fn (Request $request) =>
            Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())
        );

        RateLimiter::for('auth', fn (Request $request) =>
            Limit::perMinute(5)->by($request->ip())
        );

        RateLimiter::for('sos', fn (Request $request) =>
            Limit::perMinute(3)->by($request->ip())
        );
    }
}