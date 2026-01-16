<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Services\CurrencyService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * List products
     *
     * Returns paginated products with filters and lowest price.
     *
     * @group Products
     *
     * @queryParam search string Search by product title. Example: Windows
     * @queryParam category_id integer Filter by category ID. Example: 1
     * @queryParam platform_id integer Filter by platform ID. Example: 2
     * @queryParam type_id integer Filter by type ID. Example: 3
     * @queryParam region_id integer Filter by region ID. Example: 4
     * @queryParam language_id integer Filter by language ID. Example: 5
     * @queryParam works_on_id integer Filter by OS. Example: 1
     * @queryParam per_page integer Items per page. Example: 12
     * @queryParam page integer Page number. Example: 1
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Products fetched successfully",
     *   "data": {
     *     "products": [],
     *     "currency": {
     *       "code": "USD",
     *       "symbol": "$"
     *     },
     *     "pagination": {
     *       "total": 120,
     *       "current_page": 1,
     *       "last_page": 10
     *     }
     *   }
     * }
     */
    public function index(Request $request)
    {
        $query = Product::with([
            'categories:id,name,slug',
            'platforms:id,name,slug',
            'types:id,name,slug',
            'regions:id,name,slug',
            'languages:id,name,slug',
            'worksOn:id,name,slug',
            'developer:id,name,slug',
            'publisher:id,name,slug',
            'offers.seller:id,store_name,rating',
        ])->active();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $taxonomyFilters = [
            'category_id' => ['relation' => 'categories', 'table' => 'product_categories'],
            'platform_id' => ['relation' => 'platforms', 'table' => 'product_platforms'],
            'type_id'     => ['relation' => 'types',     'table' => 'product_types'],
            'region_id'   => ['relation' => 'regions',   'table' => 'product_regions'],
            'language_id' => ['relation' => 'languages', 'table' => 'product_languages'],
            'works_on_id' => ['relation' => 'worksOn',   'table' => 'product_works_on'],
        ];

        foreach ($taxonomyFilters as $param => $config) {
            if ($request->filled($param)) {
                $query->whereHas($config['relation'], function ($q) use ($request, $param, $config) {
                    $q->where("{$config['table']}.id", $request->get($param));
                });
            }
        }

        $products = $query->paginate($request->get('per_page', 12));

        $defaultCurrency = Currency::where('is_default', true)->first();

        $productsData = $products->map(function ($product) use ($defaultCurrency) {
            $lowestOffer = $product->offers->sortBy('retail_price')->first();

            return [
                'id'           => $product->id,
                'title'        => $product->title,
                'slug'         => $product->slug,
                'cover_image'  => $product->cover_image,
                'developer'    => $product->developer,
                'publisher'    => $product->publisher,
                'categories'   => $product->categories,
                'platforms'    => $product->platforms,
                'types'        => $product->types,
                'regions'      => $product->regions,
                'languages'    => $product->languages,
                'works_on'     => $product->worksOn,
                'lowest_price' => $lowestOffer ? [
                    'price_name' => $defaultCurrency->code,
                    'symbol'     => $defaultCurrency->symbol,
                    'price'      => round($lowestOffer->retail_price * $defaultCurrency->rate, 2),
                ] : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Products fetched successfully',
            'data'    => [
                'products'   => $productsData,
                'currency'   => $defaultCurrency,
                'pagination' => [
                    'total'        => $products->total(),
                    'per_page'     => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Get product details
     *
     * Returns full product details including offers and multi-currency prices.
     *
     * @group Products
     *
     * @urlParam id integer required Product ID. Example: 25
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Product details fetched successfully"
     * }
     *
     * @response 404 {
     *   "message": "Not found"
     * }
     */
    public function show($id)
    {
        $product = Product::with([
            'categories:id,name,slug',
            'platforms:id,name,slug',
            'types:id,name,slug,commission',
            'regions:id,name,slug',
            'languages:id,name,slug',
            'worksOn:id,name,slug',
            'developer:id,name,slug',
            'publisher:id,name,slug',
            'offers.seller:id,store_name,slug,logo,is_verified,rating,total_sales,created_at',
        ])->active()->findOrFail($id);

        $currencies = Currency::where('is_active', true)->get();

        $offers = $product->offers
            ->sortBy('retail_price')
            ->map(function ($offer) use ($currencies) {
                return [
                    'id'     => $offer->id,
                    'seller' => [
                        'id'          => $offer->seller->id,
                        'store_name'  => $offer->seller->store_name,
                        'slug'        => $offer->seller->slug,
                        'logo'        => $offer->seller->logo,
                        'is_verified' => $offer->seller->is_verified,
                        'rating'      => $offer->seller->rating,
                        'total_sales' => $offer->seller->total_sales,
                        'created_at'  => $offer->seller->created_at,
                    ],
                    'prices' => $currencies->mapWithKeys(function ($currency) use ($offer) {
                        return [
                            $currency->code => [
                                'symbol' => $currency->symbol,
                                'price'  => round($offer->retail_price * $currency->rate, 2),
                            ]
                        ];
                    }),
                    'stock'    => $offer->keys()->where('status', 'available')->count(),
                    'promoted' => $offer->is_promoted,
                ];
            })
            ->values();

        return response()->json([
            'status'  => 'success',
            'message' => 'Product details fetched successfully',
            'data'    => [
                'id'          => $product->id,
                'title'       => $product->title,
                'slug'        => $product->slug,
                'sku'         => $product->sku,
                'description' => $product->description,
                'offers'      => $offers,
                'currencies'  => $currencies,
            ],
        ]);
    }

    /**
     * Live product search
     *
     * Lightweight endpoint for live search and autocomplete.
     * Returns minimal product data sorted by lowest available price.
     *
     * Features:
     * - Full-text search on product title and SKU
     * - Cached for performance
     * - Limited result set for fast responses
     *
     * @group Products
     *
     * @queryParam q string required Search keyword (minimum 2 characters). Example: Windows
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Search results fetched successfully",
     *   "data": {
     *     "query": "windows",
     *     "count": 2,
     *     "results": [
     *       {
     *         "id": 25,
     *         "title": "Windows 11 Pro",
     *         "slug": "windows-11-pro",
     *         "image": "/storage/products/windows-11.jpg",
     *         "price": 12.99
     *       }
     *     ]
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The q field is required."
     * }
     */


    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $q = trim($request->q);

        $cacheKey = 'product_search:' . md5($q);

        $results = Cache::remember(
            $cacheKey,
            config('cache.ttl.product_search', 30),
            function () use ($q) {

                $query = Product::query()
                    ->select([
                        'products.id',
                        'products.title',
                        'products.slug',
                        'products.cover_image',
                    ])
                    ->where('products.status', 'active');

                /** 
                 * Prefer FULLTEXT, fallback to LIKE
                 */
                try {
                    $query->whereRaw(
                        "MATCH(products.title, products.sku) AGAINST (? IN BOOLEAN MODE)",
                        [$q . '*']
                    );
                } catch (\Throwable $e) {
                    $query->where(function ($q2) use ($q) {
                        $q2->where('products.title', 'like', "%{$q}%")
                        ->orWhere('products.sku', 'like', "%{$q}%");
                    });
                }

                return $query
                    ->withMin(['offers as price' => function ($q) {
                        $q->where('status', 'active');
                    }], 'retail_price')
                    ->orderBy('price')
                    ->limit(10)
                    ->get()
                    ->map(fn ($p) => [
                        'id'    => $p->id,
                        'title' => $p->title,
                        'slug'  => $p->slug,
                        'image' => $p->cover_image,
                        'price' => $p->price ? round($p->price, 2) : null,
                    ]);
            }
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Search results fetched successfully',
            'data'    => [
                'query'   => $q,
                'count'   => $results->count(),
                'results' => $results,
            ],
        ]);
    }


        /**
     * Get related products
     *
     * Returns products related to the given product based on shared
     * categories, platforms, or types.
     *
     * Used for "You may also like" or "Similar products" sections.
     *
     * @group Products
     *
     * @urlParam id integer required Product ID. Example: 25
     * @queryParam limit integer Number of related products to return. Default: 6 Example: 6
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Related products fetched successfully",
     *   "data": [
     *     {
     *       "id": 30,
     *       "title": "Windows 10 Pro",
     *       "slug": "windows-10-pro",
     *       "cover_image": "/storage/products/windows-10.jpg",
     *       "lowest_price": 9.99
     *     }
     *   ]
     * }
     *
     * @response 404 {
     *   "message": "Product not found"
     * }
     */
    public function related(Request $request, int $id)
    {
        $limit = (int) $request->get('limit', 6);

        $product = Product::active()->findOrFail($id);

        $categoryIds = $product->categories()->pluck('product_categories.id');
        $platformIds = $product->platforms()->pluck('product_platforms.id');
        $typeIds     = $product->types()->pluck('product_types.id');


        $products = Product::query()
        ->active()
        ->where('products.id', '!=', $product->id)
        ->where(function ($q) use ($categoryIds, $platformIds, $typeIds) {

            if ($categoryIds->isNotEmpty()) {
                $q->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('product_categories.id', $categoryIds);
                });
            }

            if ($platformIds->isNotEmpty()) {
                $q->orWhereHas('platforms', function ($q) use ($platformIds) {
                    $q->whereIn('product_platforms.id', $platformIds);
                });
            }

            if ($typeIds->isNotEmpty()) {
                $q->orWhereHas('types', function ($q) use ($typeIds) {
                    $q->whereIn('product_types.id', $typeIds);
                });
            }
        })
        ->withMin(['offers as lowest_price' => function ($q) {
            $q->where('seller_offers.status', 'active');
        }], 'retail_price')
        ->orderBy('lowest_price')
        ->limit($limit)
        ->get([
            'products.id',
            'products.title',
            'products.slug',
            'products.cover_image',
        ]);


        return response()->json([
            'status'  => 'success',
            'message' => 'Related products fetched successfully',
            'data'    => $products,
        ]);
    }

        /**
     * Get trending products
     *
     * Returns trending or popular products based on featured flag,
     * promotion, or sorting priority.
     *
     * Used for homepage sections like "Trending now".
     *
     * @group Products
     *
     * @queryParam limit integer Number of products to return. Default: 10 Example: 10
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Trending products fetched successfully",
     *   "data": [
     *     {
     *       "id": 18,
     *       "title": "Office 2021 Professional Plus",
     *       "slug": "office-2021-pro-plus",
     *       "cover_image": "/storage/products/office-2021.jpg",
     *       "lowest_price": 14.50
     *     }
     *   ]
     * }
     */
    public function trending(Request $request)
    {
        $limit = (int) $request->get('limit', 10);

        $products = Product::query()
            ->active()
            ->where('is_featured', true)
            ->withMin(['offers as lowest_price' => function ($q) {
                $q->where('status', 'active');
            }], 'retail_price')
            ->orderByDesc('sort_order')
            ->orderBy('lowest_price')
            ->limit($limit)
            ->get([
                'id',
                'title',
                'slug',
                'cover_image',
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Trending products fetched successfully',
            'data'    => $products,
        ]);
    }






}
