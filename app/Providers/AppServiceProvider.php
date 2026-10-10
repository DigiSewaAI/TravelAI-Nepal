<?php

namespace App\Providers;

use App\Models\Route;
use App\Observers\RouteObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;

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

        // PROVIDER-ITINERARY-09B-04: Public booking throttle
        RateLimiter::for('booking-public', fn (Request $request) =>
            Limit::perMinute(10)->by($request->ip())
        );

        // ✅ Phase 2A: Provider SOS sidebar badge count
        View::composer('layouts.provider', function ($view) {
            $count = 0;

            if (auth()->check()) {
                $user     = auth()->user();
                $provider = $user->provider
                    ?? \App\Models\Provider::where('user_id', $user->id)->first();

                if ($provider) {
                    $count = Cache::remember(
                        'provider_sos_unread_' . $provider->id,
                        60,
                        fn () => \App\Models\SosAlert::where('provider_id', $provider->id)
                            ->whereIn('status', ['pending', 'sent'])
                            ->count()
                    );
                }
            }

            $view->with('unreadSosCount', $count);
        });

        // ✅ Global Reply-To for all outgoing emails
        // Ensures customer replies reach admin@travelainepal.com
        // instead of the unmonitored noreply@ address.
        if (config('mail.reply_to.address')) {
            Mail::alwaysReplyTo(
                config('mail.reply_to.address'),
                config('mail.reply_to.name', config('mail.from.name', 'Example'))
            );

            // Provider-specific reply-to override (fires AFTER global alwaysReplyTo)
            Event::listen(MessageSending::class, function (MessageSending $event) {
                $providerReplyTo = $event->data['__provider_reply_to'] ?? null;
                if (!empty($providerReplyTo['address'])) {
                    $headers = $event->message->getHeaders();
                    $headers->remove('Reply-To');
                    $event->message->replyTo(
                        new \Symfony\Component\Mime\Address(
                            $providerReplyTo['address'],
                            $providerReplyTo['name'] ?? ''
                        )
                    );
                }
            });
        }
    }
}