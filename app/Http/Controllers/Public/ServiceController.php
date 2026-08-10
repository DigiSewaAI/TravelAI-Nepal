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
        $categorySlug = $request->get('category', 'trek');
        $category = ServiceCategory::where('slug', $categorySlug)->first();

        $query = Service::with(['provider', 'category', 'trekDetail'])
            ->where('status', 'active');

        if ($category) {
            $query->where('service_category_id', $category->id);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by duration (for treks/tours)
        if ($request->filled('min_days')) {
            $query->whereHas('trekDetail', function ($q) use ($request) {
                $q->where('duration_days', '>=', $request->min_days);
            });
        }
        if ($request->filled('max_days')) {
            $query->whereHas('trekDetail', function ($q) use ($request) {
                $q->where('duration_days', '<=', $request->max_days);
            });
        }

        // Filter by difficulty (for treks)
        if ($request->filled('difficulty')) {
            $query->whereHas('trekDetail', function ($q) use ($request) {
                $q->where('difficulty', $request->difficulty);
            });
        }

        $services = $query->latest()->paginate(12)->appends($request->query());

        // Get all categories for filter tabs
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