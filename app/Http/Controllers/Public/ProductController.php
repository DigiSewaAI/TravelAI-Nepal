<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function shop(Request $request)
    {
        return $this->renderList($request, 'shop');
    }

    public function rental(Request $request)
    {
        return $this->renderList($request, 'rental');
    }

    public function wholesale(Request $request)
    {
        return $this->renderList($request, 'wholesale');
    }

    private function renderList(Request $request, string $type)
    {
        $query = Product::query()
            ->with([
                'provider:id,name,slug',
                'location:id,city',
                'shopDetail',
                'rentalDetail',
                'wholesaleDetail',
            ])
            ->where('product_type', $type)
            ->where('status', 'active');

        if ($request->filled('location')) {
            $query->where('location_id', $request->input('location'));
        }

        if ($request->filled('min_price') && is_numeric($request->min_price)) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name'       => $query->orderBy('name', 'asc'),
            default      => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $locations = Location::whereNotNull('city')
            ->where('city', '!=', '')
            ->orderBy('city')
            ->get(['id', 'city']);

        return view('public.products.index', compact('products', 'type', 'locations', 'sort'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with([
                'provider:id,name,slug,contact_email,contact_phone,address,logo_url',
                'location:id,city',
                'shopDetail',
                'rentalDetail',
                'wholesaleDetail',
            ])
            ->firstOrFail();

        return view('public.products.show', compact('product'));
    }
}