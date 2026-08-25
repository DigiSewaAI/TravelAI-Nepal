<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ✅ Register admin and localize middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'localize' => \App\Http\Middleware\Localization::class,
        ]);

        // ✅ WEB GROUP मा Localization Middleware थप्नुहोस् (सबै web routes मा apply हुन्छ)
        $middleware->web(append: [
            \App\Http\Middleware\Localization::class,
        ]);

        // CSRF अपवाद यहाँ राख्नुपर्दैन, किनकि API मा CSRF हुँदैन
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();