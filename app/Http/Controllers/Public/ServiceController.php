<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Provider;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of services with filters.
     */
    public function index(Request $request)
{
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $rate = $currencyService->getExchangeRate();

    $query = Service::with(['provider', 'category', 'trekDetail'])
        ->where('status', 'active');

    // Category filter
    $categorySlug = $request->input('category', 'all');
    if ($categorySlug !== 'all') {
        $category = ServiceCategory::where('slug', $categorySlug)->first();
        if ($category) {
            $query->where('service_category_id', $category->id);
        }
    }

    // Search
    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    // Price filters with mixed currency support
    if ($request->filled('min_price')) {
        $min = (float) $request->min_price;
        $query->where(function ($q) use ($min, $displayCurrency, $rate) {
            $q->where('currency', $displayCurrency)
              ->where('price', '>=', $min);
            if ($displayCurrency === 'USD') {
                $q->orWhere('currency', 'NPR')
                  ->where('price', '>=', $min * $rate);
            } else {
                $q->orWhere('currency', 'USD')
                  ->where('price', '>=', $min / $rate);
            }
        });
    }

    if ($request->filled('max_price')) {
        $max = (float) $request->max_price;
        $query->where(function ($q) use ($max, $displayCurrency, $rate) {
            $q->where('currency', $displayCurrency)
              ->where('price', '<=', $max);
            if ($displayCurrency === 'USD') {
                $q->orWhere('currency', 'NPR')
                  ->where('price', '<=', $max * $rate);
            } else {
                $q->orWhere('currency', 'USD')
                  ->where('price', '<=', $max / $rate);
            }
        });
    }

    // Duration filters
    if ($request->filled('min_days')) {
        $query->whereHas('trekDetail', function ($q) use ($request) {
            $q->where('duration_days', '>=', (int) $request->min_days);
        });
    }

    if ($request->filled('max_days')) {
        $query->whereHas('trekDetail', function ($q) use ($request) {
            $q->where('duration_days', '<=', (int) $request->max_days);
        });
    }

    // Difficulty filter (only for treks)
    if ($request->filled('difficulty')) {
        $query->whereHas('trekDetail', function ($q) use ($request) {
            $q->where('difficulty', $request->difficulty);
        });
    }

    // Sorting
    $sort = $request->input('sort', 'latest');
    if ($sort === 'latest') {
        $query->latest();
    } elseif ($sort === 'price_asc') {
        $query->orderBy('price', 'asc');
    } elseif ($sort === 'price_desc') {
        $query->orderBy('price', 'desc');
    }

    $services = $query->paginate(12)->appends($request->query());
    $categories = ServiceCategory::all();

    return view('public.services.index', compact('services', 'categories', 'categorySlug'));
}

    /**
     * Display a single service detail.
     */
    public function show($slug)
    {
        $service = Service::with([
            'provider',
            'category',
            'trekDetail',
            'tourDetail',
            'hotelDetail',
            'location'
        ])->where('slug', $slug)
          ->where('status', 'active')
          ->firstOrFail();

        // Get related services from same provider
        $relatedServices = Service::where('provider_id', $service->provider_id)
            ->where('id', '!=', $service->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        return view('public.services.show', compact('service', 'relatedServices'));
    }

    /**
     * Display a provider's profile page.
     */
    public function providerProfile($slug)
    {
        $provider = Provider::with(['services' => function ($query) {
            $query->where('status', 'active');
        }, 'types'])
        ->where('slug', $slug)
        ->firstOrFail();

        return view('public.providers.show', compact('provider'));
    }

    /**
     * Display services by category.
     */
    public function category($slug)
    {
        $category = ServiceCategory::where('slug', $slug)->firstOrFail();

        $services = Service::with(['provider', 'category', 'trekDetail'])
            ->where('service_category_id', $category->id)
            ->where('status', 'active')
            ->latest()
            ->paginate(12);

        $categories = ServiceCategory::all();

        return view('public.services.category', compact('services', 'category', 'categories'));
    }
}