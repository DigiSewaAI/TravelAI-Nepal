<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Provider;

class SitemapController extends Controller
{
    public function index()
    {
        $services = Service::where('status', 'active')
            ->select('id', 'slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(10000)              // ← थपियो
            ->get();

        $providers = Provider::where('is_active', true)
            ->select('id', 'slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(5000)               // ← थपियो
            ->get();

        return response()
            ->view('sitemap', compact('services', 'providers'))
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');  // ← 1 hour cache
    }
}