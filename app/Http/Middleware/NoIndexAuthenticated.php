<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoIndexAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Any authenticated request = noindex
        if (auth()->check()) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        // Sensitive routes always noindex (even if guest)
        $path = $request->path();
        if (
            str_starts_with($path, 'admin/') ||
            str_starts_with($path, 'traveler/') ||
            str_starts_with($path, 'provider/')
        ) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        return $response;
    }
}