<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ShopDetail;
use App\Models\RentalDetail;
use App\Models\WholesaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    private function getProvider(): Provider
    {
        $provider = Auth::user()->ownProvider();
        if (!$provider) {
            abort(403, 'No provider account.');
        }
        return $provider;
    }

    public function index(Request $request)
    {
        $provider = $this->getProvider();
        $type = $request->query('type');

        $query = Product::where('provider_id', $provider->id)
            ->with(['shopDetail', 'rentalDetail', 'wholesaleDetail'])
            ->latest();

        if (in_array($type, ['shop', 'rental', 'wholesale'])) {
            $query->where('product_type', $type);
        }

        $products = $query->paginate(15)->withQueryString();

        $counts = [
            'all'       => Product::where('provider_id', $provider->id)->count(),
            'shop'      => Product::where('provider_id', $provider->id)->shop()->count(),
            'rental'    => Product::where('provider_id', $provider->id)->rental()->count(),
            'wholesale' => Product::where('provider_id', $provider->id)->wholesale()->count(),
        ];

        $maxProducts   = $provider->max_products;
        $canCreateMore = $maxProducts === -1 || $counts['all'] < $maxProducts;

        return view('provider.products.index', compact(
            'products', 'type', 'counts', 'maxProducts', 'canCreateMore'
        ));
    }

    public function create(Request $request)
    {
        $provider = $this->getProvider();

        $count = Product::where('provider_id', $provider->id)->count();
        if ($provider->max_products !== -1 && $count >= $provider->max_products) {
            return redirect()
                ->route('provider.products.index')
                ->with('error', __('messages.product_limit_reached', ['max' => $provider->max_products]));
        }

        $type = $request->query('type', 'shop');
        if (!in_array($type, ['shop', 'rental', 'wholesale'])) {
            $type = 'shop';
        }

        return view('provider.products.create', compact('type', 'provider'));
    }

    public function store(Request $request)
    {
        $provider = $this->getProvider();

        $type = $request->input('product_type');
        if (!in_array($type, ['shop', 'rental', 'wholesale'])) {
            throw ValidationException::withMessages([
                'product_type' => 'Invalid product type.',
            ]);
        }

        $rules = [
            'product_type' => 'required|in:shop,rental,wholesale',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:5000',
            'price'        => 'required|numeric|min:0',
            'currency'     => 'required|string|size:3',
            'cover_image'  => 'nullable|string|max:255',
            'status'       => 'required|in:active,inactive',
            'location_id'  => 'nullable|exists:locations,id',
        ];

        $rules = array_merge($rules, match ($type) {
            'shop' => [
                'stock_count' => 'nullable|integer|min:0',
                'sku'         => 'nullable|string|max:100',
            ],
            'rental' => [
                'rental_price_per_day' => 'required|numeric|min:0',
                'rental_deposit'       => 'nullable|numeric|min:0',
                'rental_condition'     => 'nullable|string|max:50',
                'rental_min_days'      => 'nullable|integer|min:1',
                'rental_max_days'      => 'nullable|integer|min:1|gte:rental_min_days',
                'late_fee_per_day'     => 'nullable|numeric|min:0',
                'damage_deposit_pct'   => 'nullable|integer|min:0|max:100',
                'lost_deposit_pct'     => 'nullable|integer|min:0|max:100',
            ],
            'wholesale' => [
                'min_order_qty' => 'required|integer|min:1',
                'bulk_pricing'  => 'nullable|array',
            ],
        });

        $validated = $request->validate($rules);

        DB::transaction(function () use ($provider, $validated, $type) {
            $locked = Provider::lockForUpdate()->find($provider->id);

            $currentCount = Product::where('provider_id', $locked->id)->count();
            $max = $locked->max_products;

            if ($max !== -1 && $currentCount >= $max) {
                throw ValidationException::withMessages([
                    'limit' => __('messages.product_limit_reached', ['max' => $max]),
                ]);
            }

            $product = Product::create([
                'provider_id'  => $locked->id,
                'product_type' => $type,
                'name'         => $validated['name'],
                'slug'         => Str::slug($validated['name']) . '-' . Str::random(6),
                'description'  => $validated['description'] ?? null,
                'price'        => $validated['price'],
                'currency'     => $validated['currency'],
                'cover_image'  => $validated['cover_image'] ?? null,
                'status'       => $validated['status'],
                'location_id'  => $validated['location_id'] ?? null,
            ]);

            match ($type) {
                'shop' => ShopDetail::create([
                    'product_id'  => $product->id,
                    'stock_count' => $validated['stock_count'] ?? null,
                    'sku'         => $validated['sku'] ?? null,
                ]),
                'rental' => RentalDetail::create([
                    'product_id'           => $product->id,
                    'rental_price_per_day' => $validated['rental_price_per_day'],
                    'rental_deposit'       => $validated['rental_deposit'] ?? null,
                    'rental_condition'     => $validated['rental_condition'] ?? null,
                    'rental_min_days'      => $validated['rental_min_days'] ?? null,
                    'rental_max_days'      => $validated['rental_max_days'] ?? null,
                    'late_fee_per_day'     => $validated['late_fee_per_day'] ?? 0,
                    'damage_deposit_pct'   => $validated['damage_deposit_pct'] ?? 100,
                    'lost_deposit_pct'     => $validated['lost_deposit_pct'] ?? 100,
                ]),
                'wholesale' => WholesaleDetail::create([
                    'product_id'    => $product->id,
                    'min_order_qty' => $validated['min_order_qty'],
                    'bulk_pricing'  => $validated['bulk_pricing'] ?? null,
                ]),
            };
        });

        return redirect()
            ->route('provider.products.index')
            ->with('success', __('messages.product_created'));
    }

    public function edit(Product $product)
    {
        $provider = $this->getProvider();
        if ($product->provider_id !== $provider->id) {
            abort(403);
        }

        $product->load(['shopDetail', 'rentalDetail', 'wholesaleDetail']);
        $type = $product->product_type;

        return view('provider.products.edit', compact('product', 'type', 'provider'));
    }

    public function update(Request $request, Product $product)
    {
        $provider = $this->getProvider();
        if ($product->provider_id !== $provider->id) {
            abort(403);
        }

        $type = $product->product_type;

        $rules = [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price'       => 'required|numeric|min:0',
            'currency'    => 'required|string|size:3',
            'cover_image' => 'nullable|string|max:255',
            'status'      => 'required|in:active,inactive',
            'location_id' => 'nullable|exists:locations,id',
        ];

        $rules = array_merge($rules, match ($type) {
            'shop' => [
                'stock_count' => 'nullable|integer|min:0',
                'sku'         => 'nullable|string|max:100',
            ],
            'rental' => [
                'rental_price_per_day' => 'required|numeric|min:0',
                'rental_deposit'       => 'nullable|numeric|min:0',
                'rental_condition'     => 'nullable|string|max:50',
                'rental_min_days'      => 'nullable|integer|min:1',
                'rental_max_days'      => 'nullable|integer|min:1|gte:rental_min_days',
                'late_fee_per_day'     => 'nullable|numeric|min:0',
                'damage_deposit_pct'   => 'nullable|integer|min:0|max:100',
                'lost_deposit_pct'     => 'nullable|integer|min:0|max:100',
            ],
            'wholesale' => [
                'min_order_qty' => 'required|integer|min:1',
                'bulk_pricing'  => 'nullable|array',
            ],
        });

        $validated = $request->validate($rules);

        DB::transaction(function () use ($product, $validated, $type) {
            $product->update([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price'       => $validated['price'],
                'currency'    => $validated['currency'],
                'cover_image' => $validated['cover_image'] ?? null,
                'status'      => $validated['status'],
                'location_id' => $validated['location_id'] ?? null,
            ]);

            match ($type) {
                'shop' => ShopDetail::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'stock_count' => $validated['stock_count'] ?? null,
                        'sku'         => $validated['sku'] ?? null,
                    ]
                ),
                'rental' => RentalDetail::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'rental_price_per_day' => $validated['rental_price_per_day'],
                        'rental_deposit'       => $validated['rental_deposit'] ?? null,
                        'rental_condition'     => $validated['rental_condition'] ?? null,
                        'rental_min_days'      => $validated['rental_min_days'] ?? null,
                        'rental_max_days'      => $validated['rental_max_days'] ?? null,
                        'late_fee_per_day'     => $validated['late_fee_per_day'] ?? 0,
                        'damage_deposit_pct'   => $validated['damage_deposit_pct'] ?? 100,
                        'lost_deposit_pct'     => $validated['lost_deposit_pct'] ?? 100,
                    ]
                ),
                'wholesale' => WholesaleDetail::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'min_order_qty' => $validated['min_order_qty'],
                        'bulk_pricing'  => $validated['bulk_pricing'] ?? null,
                    ]
                ),
            };
        });

        return redirect()
            ->route('provider.products.index')
            ->with('success', __('messages.product_updated'));
    }

    public function destroy(Product $product)
    {
        $provider = $this->getProvider();
        if ($product->provider_id !== $provider->id) {
            abort(403);
        }

        $product->delete();

        return redirect()
            ->route('provider.products.index')
            ->with('success', __('messages.product_deleted'));
    }
}