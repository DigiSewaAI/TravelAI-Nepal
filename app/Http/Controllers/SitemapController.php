<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Provider;
use App\Models\Product;
use App\Models\ServiceCategory;

class SitemapController extends Controller
{
    public function index()
    {
        $services = Service::where('status', 'active')
            ->select('id', 'slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(10000)
            ->get();

        $providers = Provider::where('is_active', true)
            ->select('id', 'slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get();

        // NEW: Products (shop/rental/wholesale)
        $products = Product::where('status', 'active')
            ->select('id', 'slug', 'updated_at', 'product_type')
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get();

        // NEW: Categories
        $categories = ServiceCategory::select('id', 'slug', 'updated_at')
            ->orderBy('slug')
            ->get();

        return response()
            ->view('sitemap', compact(
                'services',
                'providers',
                'products',
                'categories'
            ))
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}