<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',  // ✅ यो लाइन थप्नुहोस्
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CSRF अपवाद यहाँ राख्नुपर्दैन, किनकि API मा CSRF हुँदैन
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();