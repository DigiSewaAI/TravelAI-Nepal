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

        // ═══════════════════════════════════════════════════════
        // BASE QUERY
        // ═══════════════════════════════════════════════════════
        $query = Service::query()
            ->with([
                'provider:id,name,slug',
                'category:id,name,slug',
                'trekDetail',
            ])
            ->where('services.status', 'active');

        // ═══════════════════════════════════════════════════════
        // CATEGORY FILTER
        // ═══════════════════════════════════════════════════════
        $categorySlug = $request->input('category', 'all');
        if ($categorySlug !== 'all') {
            $category = ServiceCategory::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('services.service_category_id', $category->id);
            } else {
                // Invalid category — reset to all
                $categorySlug = 'all';
            }
        }

        // ═══════════════════════════════════════════════════════
        // SEARCH (name + description + singular/plural + category)
        // ═══════════════════════════════════════════════════════
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $singular = rtrim($searchTerm, 's');
            $plural = $searchTerm . 's';

            $query->where(function ($q) use ($searchTerm, $singular, $plural) {
                $q->where('services.name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('services.description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('services.name', 'like', '%' . $singular . '%')
                  ->orWhere('services.name', 'like', '%' . $plural . '%')
                  ->orWhereHas('category', function ($cq) use ($searchTerm, $singular) {
                      $cq->where('name', 'like', '%' . $searchTerm . '%')
                         ->orWhere('name', 'like', '%' . $singular . '%')
                         ->orWhere('slug', 'like', '%' . strtolower($singular) . '%');
                  });
            });
        }

        // ═══════════════════════════════════════════════════════
        // PRICE FILTERS (mixed currency support)
        // ═══════════════════════════════════════════════════════
        if ($request->filled('min_price')) {
            $min = (float) $request->min_price;
            $query->where(function ($q) use ($min, $displayCurrency, $rate) {
                $q->where('services.currency', $displayCurrency)
                  ->where('services.price', '>=', $min);
                if ($displayCurrency === 'USD') {
                    $q->orWhere(function ($qq) use ($min, $rate) {
                        $qq->where('services.currency', 'NPR')
                           ->where('services.price', '>=', $min * $rate);
                    });
                } else {
                    $q->orWhere(function ($qq) use ($min, $rate) {
                        $qq->where('services.currency', 'USD')
                           ->where('services.price', '>=', $min / $rate);
                    });
                }
            });
        }

        if ($request->filled('max_price')) {
            $max = (float) $request->max_price;
            $query->where(function ($q) use ($max, $displayCurrency, $rate) {
                $q->where('services.currency', $displayCurrency)
                  ->where('services.price', '<=', $max);
                if ($displayCurrency === 'USD') {
                    $q->orWhere(function ($qq) use ($max, $rate) {
                        $qq->where('services.currency', 'NPR')
                           ->where('services.price', '<=', $max * $rate);
                    });
                } else {
                    $q->orWhere(function ($qq) use ($max, $rate) {
                        $qq->where('services.currency', 'USD')
                           ->where('services.price', '<=', $max / $rate);
                    });
                }
            });
        }

        // ═══════════════════════════════════════════════════════
        // DURATION FILTERS
        // ═══════════════════════════════════════════════════════
        if ($request->filled('min_days')) {
            $minDays = (int) $request->min_days;
            $query->whereHas('trekDetail', function ($q) use ($minDays) {
                $q->where('duration_days', '>=', $minDays);
            });
        }

        if ($request->filled('max_days')) {
            $maxDays = (int) $request->max_days;
            $query->whereHas('trekDetail', function ($q) use ($maxDays) {
                $q->where('duration_days', '<=', $maxDays);
            });
        }

        // ═══════════════════════════════════════════════════════
        // DIFFICULTY FILTER
        // ═══════════════════════════════════════════════════════
        if ($request->filled('difficulty')) {
            $difficulty = $request->difficulty;
            if (in_array($difficulty, ['easy', 'moderate', 'hard'])) {
                $query->whereHas('trekDetail', function ($q) use ($difficulty) {
                    $q->where('difficulty', $difficulty);
                });
            }
        }

        // ═══════════════════════════════════════════════════════
        // SORTING
        // ═══════════════════════════════════════════════════════
        $sort = $request->input('sort', 'popular');

        switch ($sort) {
            case 'price-low':
            case 'price_asc':
                $query->orderBy('services.price', 'asc');
                break;

            case 'price-high':
            case 'price_desc':
                $query->orderBy('services.price', 'desc');
                break;

            case 'duration':
                // Longest duration first
                $query->leftJoin('trek_details as sort_trek', 'services.id', '=', 'sort_trek.service_id')
                      ->orderByRaw('sort_trek.duration_days IS NULL, sort_trek.duration_days DESC')
                      ->select('services.*');
                break;

            case 'altitude':
                // Highest altitude first
                $query->leftJoin('trek_details as sort_trek', 'services.id', '=', 'sort_trek.service_id')
                      ->orderByRaw('sort_trek.max_altitude IS NULL, sort_trek.max_altitude DESC')
                      ->select('services.*');
                break;

            case 'popular':
            default:
                // Priority order: Treks → Tours → Hotels → Activities → Experience → Guides → Transport
                $query->leftJoin('service_categories as sort_cats', 'services.service_category_id', '=', 'sort_cats.id')
                      ->orderByRaw("
                          CASE LOWER(sort_cats.slug)
                              WHEN 'trek'       THEN 1
                              WHEN 'trekking'   THEN 1
                              WHEN 'tour'       THEN 2
                              WHEN 'tours'      THEN 2
                              WHEN 'hotel'      THEN 3
                              WHEN 'hotels'     THEN 3
                              WHEN 'activity'   THEN 4
                              WHEN 'activities' THEN 4
                              WHEN 'experience' THEN 5
                              WHEN 'guide'      THEN 6
                              WHEN 'guides'     THEN 6
                              WHEN 'transport'  THEN 7
                              ELSE 8
                          END ASC
                      ")
                      ->select('services.*')
                      ->orderBy('services.created_at', 'desc');
                break;
        }

        // ═══════════════════════════════════════════════════════
        // PAGINATE
        // ═══════════════════════════════════════════════════════
        $services = $query->paginate(12)->appends($request->query());

        // ═══════════════════════════════════════════════════════
        // SIDEBAR DATA (cached in production)
        // ═══════════════════════════════════════════════════════
        $categories = ServiceCategory::orderBy('name')->get();

        $categoryCounts = Service::where('status', 'active')
            ->selectRaw('service_category_id, count(*) as cnt')
            ->groupBy('service_category_id')
            ->pluck('cnt', 'service_category_id')
            ->toArray();

        $totalCount = array_sum($categoryCounts);

        return view('public.services.index', compact(
            'services',
            'categories',
            'categorySlug',
            'categoryCounts',
            'totalCount'
        ));
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
            'location',
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        // Related services from same provider (limit 4)
        $relatedServices = Service::with(['provider:id,name,slug', 'category:id,name,slug'])
            ->where('provider_id', $service->provider_id)
            ->where('id', '!=', $service->id)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        return view('public.services.show', compact('service', 'relatedServices'));
    }

    /**
     * Display a provider's profile page.
     */
    public function providerProfile($slug)
    {
        $provider = Provider::with([
            'services' => function ($query) {
                $query->where('status', 'active')
                      ->orderByDesc('created_at');
            },
            'types',
        ])
        ->where('slug', $slug)
        ->firstOrFail();

        return view('public.providers.show', compact('provider'));
    }

    /**
     * Display services by category (with search + priority sort).
     */
    public function category(Request $request, $slug)
    {
        $category = ServiceCategory::where('slug', $slug)->firstOrFail();

        $currencyService = app(\App\Services\CurrencyService::class);
        $displayCurrency = $currencyService->getDisplayCurrency();
        $rate = $currencyService->getExchangeRate();

        $query = Service::query()
            ->with(['provider:id,name,slug', 'category:id,name,slug', 'trekDetail'])
            ->where('services.status', 'active')
            ->where('services.service_category_id', $category->id);

        // Search within category
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $singular = rtrim($searchTerm, 's');
            $plural = $searchTerm . 's';

            $query->where(function ($q) use ($searchTerm, $singular, $plural) {
                $q->where('services.name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('services.description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('services.name', 'like', '%' . $singular . '%')
                  ->orWhere('services.name', 'like', '%' . $plural . '%');
            });
        }

        // Price filters
        if ($request->filled('min_price')) {
            $min = (float) $request->min_price;
            $query->where(function ($q) use ($min, $displayCurrency, $rate) {
                $q->where('services.currency', $displayCurrency)
                  ->where('services.price', '>=', $min);
                if ($displayCurrency === 'USD') {
                    $q->orWhere(function ($qq) use ($min, $rate) {
                        $qq->where('services.currency', 'NPR')
                           ->where('services.price', '>=', $min * $rate);
                    });
                } else {
                    $q->orWhere(function ($qq) use ($min, $rate) {
                        $qq->where('services.currency', 'USD')
                           ->where('services.price', '>=', $min / $rate);
                    });
                }
            });
        }

        if ($request->filled('max_price')) {
            $max = (float) $request->max_price;
            $query->where(function ($q) use ($max, $displayCurrency, $rate) {
                $q->where('services.currency', $displayCurrency)
                  ->where('services.price', '<=', $max);
                if ($displayCurrency === 'USD') {
                    $q->orWhere(function ($qq) use ($max, $rate) {
                        $qq->where('services.currency', 'NPR')
                           ->where('services.price', '<=', $max * $rate);
                    });
                } else {
                    $q->orWhere(function ($qq) use ($max, $rate) {
                        $qq->where('services.currency', 'USD')
                           ->where('services.price', '<=', $max / $rate);
                    });
                }
            });
        }

        // Difficulty filter
        if ($request->filled('difficulty')) {
            $difficulty = $request->difficulty;
            if (in_array($difficulty, ['easy', 'moderate', 'hard'])) {
                $query->whereHas('trekDetail', function ($q) use ($difficulty) {
                    $q->where('difficulty', $difficulty);
                });
            }
        }

        // Sort within category
        $sort = $request->input('sort', 'latest');

        switch ($sort) {
            case 'price-low':
                $query->orderBy('services.price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('services.price', 'desc');
                break;
            case 'duration':
                $query->leftJoin('trek_details as sort_trek', 'services.id', '=', 'sort_trek.service_id')
                      ->orderByRaw('sort_trek.duration_days IS NULL, sort_trek.duration_days DESC')
                      ->select('services.*');
                break;
            case 'altitude':
                $query->leftJoin('trek_details as sort_trek', 'services.id', '=', 'sort_trek.service_id')
                      ->orderByRaw('sort_trek.max_altitude IS NULL, sort_trek.max_altitude DESC')
                      ->select('services.*');
                break;
            case 'latest':
            default:
                $query->orderByDesc('services.created_at');
                break;
        }

        $services = $query->paginate(12)->appends($request->query());
        $categories = ServiceCategory::orderBy('name')->get();

        return view('public.services.category', compact('services', 'category', 'categories'));
    }
}
