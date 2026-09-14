<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Provider;
use App\Models\Page;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $services  = Service::where('status', 'active')
            ->select('id', 'slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->get();

        $providers = Provider::where('is_active', true)
            ->select('id', 'slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->get();

        return response()
            ->view('sitemap', compact('services', 'providers'))
            ->header('Content-Type', 'application/xml');
    }
}