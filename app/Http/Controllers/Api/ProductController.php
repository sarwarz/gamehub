<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ProductResource;

/**
 * @group Products
 *
 * Public APIs for browsing products, search, details, related and trending.
 * All endpoints are unauthenticated.
 *
 * @unauthenticated
 */
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
     * Returns paginated products with filters, sorting, price range and lowest price.
     *
     * @queryParam search string Search by product title. Example: Windows
     * @queryParam category_id integer Filter by category ID. Example: 1
     * @queryParam platform_id integer Filter by platform ID. Example: 2
     * @queryParam type_id integer Filter by type ID. Example: 3
     * @queryParam region_id integer Filter by region ID. Example: 4
     * @queryParam language_id integer Filter by language ID. Example: 5
     * @queryParam works_on_id integer Filter by OS. Example: 1
     * @queryParam min_price number Optional. Minimum price (lowest offer). Example: 5.00
     * @queryParam max_price number Optional. Maximum price (lowest offer). Example: 99.99
     * @queryParam sort string Sort order: newest, price_asc, price_desc, title_asc, title_desc. Example: price_asc
     * @queryParam per_page integer Items per page. Example: 12
     * @queryParam page integer Page number. Example: 1
     *
     * @response 200 {"status":"success","message":"Products fetched successfully","data":{"products":[],"currency":{"code":"USD","symbol":"$"},"pagination":{"total":120,"current_page":1,"last_page":10}}}
     */
    public function index(Request $request)
    {
        try {
            $showOutOfStock = (bool) \App\Models\Setting::get('store', 'show_out_of_stock', true);

            $query = Product::with([
                'categories:id,name,slug',
                'platforms:id,name,slug',
                'types:id,name,slug',
                'regions:id,name,slug',
                'languages:id,name,slug',
                'worksOn:id,name,slug',
                'developer:id,name,slug',
                'publisher:id,name,slug',
                'label:id,name,bg_color,text_color',
                'offers.seller:id,store_name,rating',
            ])->active();

            if (!$showOutOfStock) {
                $query->whereHas('offers', function ($q) {
                    $q->where('status', 'active')
                      ->whereHas('keys', fn ($k) => $k->where('status', 'available'));
                });
            }

            if ($request->filled('search')) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
                $query->where('title', 'like', '%' . $escaped . '%');
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

            if ($request->filled('min_price') && is_numeric($request->min_price)) {
                $query->whereHas('offers', function ($q) use ($request) {
                    $q->where('status', 'active')->where('retail_price', '>=', $request->min_price);
                });
            }
            if ($request->filled('max_price') && is_numeric($request->max_price)) {
                $query->whereHas('offers', function ($q) use ($request) {
                    $q->where('status', 'active')->where('retail_price', '<=', $request->max_price);
                });
            }

            $sort = $request->get('sort', 'newest');
            if (in_array($sort, ['price_asc', 'price_desc'], true)) {
                $query->withMin(['offers as min_retail' => fn ($q) => $q->where('seller_offers.status', 'active')], 'retail_price')
                    ->orderBy('min_retail', $sort === 'price_asc' ? 'asc' : 'desc');
            } elseif ($sort === 'title_asc') {
                $query->orderBy('title', 'asc');
            } elseif ($sort === 'title_desc') {
                $query->orderBy('title', 'desc');
            } else {
                $query->latest();
            }

            $perPage = min((int) $request->input('per_page', 15), 50);
            $products = $query->paginate($perPage);

            $defaultCurrency = Currency::where('is_default', true)->first();
            $currencyCode   = $defaultCurrency->code   ?? 'USD';
            $currencySymbol = $defaultCurrency->symbol ?? '$';
            $currencyRate   = $defaultCurrency->rate   ?? 1;

            $productsData = $products->map(function ($product) use ($currencyCode, $currencySymbol, $currencyRate) {
                $lowestOffer = $product->offers->sortBy('retail_price')->first();

                return [
                    'id'           => $product->id,
                    'title'        => $product->title,
                    'slug'         => $product->slug,
                    'image'        => asset($product->image),
                    'developer'    => $product->developer,
                    'publisher'    => $product->publisher,
                    'categories'   => $product->categories,
                    'platforms'    => $product->platforms,
                    'types'        => $product->types,
                    'regions'      => $product->regions,
                    'languages'    => $product->languages,
                    'works_on'     => $product->worksOn,
                    'label'        => $product->label,
                    'lowest_price' => $lowestOffer ? [
                        'price_name' => $currencyCode,
                        'symbol'     => $currencySymbol,
                        'price'      => round($lowestOffer->retail_price * $currencyRate, 2),
                    ] : null,
                ];
            });

            return $this->success([
                'products'   => $productsData,
                'currency'   => $defaultCurrency,
                'pagination' => [
                    'total'        => $products->total(),
                    'per_page'     => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                ],
            ], 'Products fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch products');
        }
    }

    /**
     * Get product details by ID
     *
     * Returns full product details including seller offers and multi-currency prices.
     *
     * @urlParam id integer required Product ID. Example: 25
     *
     * @response 200 {"status":"success","message":"Product details fetched successfully","data":{"product":{},"offers":[]}}
     * @response 404 {"message":"No query results for model [App\\Models\\Product]."}
     */
    public function show($id)
    {
        try {
            $product = Product::with([
                'categories:id,name,slug',
                'platforms:id,name,slug',
                'types:id,name,slug,commission',
                'regions:id,name,slug',
                'languages:id,name,slug',
                'worksOn:id,name,slug',
                'developer:id,name,slug',
                'publisher:id,name,slug',
                'label:id,name,bg_color,text_color',
                'offers.seller:id,store_name,slug,logo,is_verified,rating,total_sales,created_at',
            ])->active()->findOrFail($id);

            return $this->productDetailResponse($product);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Product not found', 404);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch product details');
        }
    }

    /**
     * Get product details by slug
     *
     * Same as show by ID but uses URL-friendly slug. Useful for SEO and frontend routes.
     *
     * @urlParam slug string required Product slug. Example: windows-11-pro
     *
     * @response 200 {"status":"success","message":"Product details fetched successfully","data":{"product":{},"offers":[]}}
     * @response 404 {"status":false,"message":"Product not found."}
     */
    public function showBySlug(string $slug)
    {
        try {
            $product = Product::with([
                'categories:id,name,slug',
                'platforms:id,name,slug',
                'types:id,name,slug,commission',
                'regions:id,name,slug',
                'languages:id,name,slug',
                'worksOn:id,name,slug',
                'developer:id,name,slug',
                'publisher:id,name,slug',
                'label:id,name,bg_color,text_color',
                'offers.seller:id,store_name,slug,logo,is_verified,rating,total_sales,created_at',
            ])->active()->where('slug', $slug)->first();

            if (!$product) {
                return $this->error('Product not found.', 404);
            }

            return $this->productDetailResponse($product);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch product details');
        }
    }

    protected function productDetailResponse(Product $product)
    {
        $currencies = Currency::where('is_active', true)->get();

        $offers = $product->offers
            ->sortBy('retail_price')
            ->map(function ($offer) use ($currencies) {
                return [
                    'id'     => $offer->id,
                    'seller' => $offer->seller,
                    'prices' => $currencies->mapWithKeys(fn ($currency) => [
                        $currency->code => [
                            'symbol' => $currency->symbol,
                            'price'  => round($offer->retail_price * $currency->rate, 2),
                        ]
                    ]),
                    'stock'    => $offer->keys()->where('status', 'available')->count(),
                    'promoted' => $offer->is_promoted,
                ];
            })->values();

        return $this->success([
            'product' => new ProductResource($product),
            'offers'  => $offers,
        ], 'Product details fetched successfully');
    }


    /**
     * Live product search
     *
     * Lightweight autocomplete: full-text search on title and SKU, cached, limited results.
     *
     * @queryParam q string required Search keyword (min 2 characters). Example: Windows
     *
     * @response 200 {"status":"success","message":"Search results fetched successfully","data":{"query":"windows","count":2,"results":[{"id":25,"title":"Windows 11 Pro","slug":"windows-11-pro","image":"/storage/products/windows-11.jpg","price":12.99}]}}
     * @response 422 {"message":"The q field is required."}
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        try {
            $q = trim($request->q);

            $cacheKey = 'product_search:' . md5($q);

            $results = Cache::remember(
                $cacheKey,
                config('cache.ttl.product_search', 30),
                function () use ($q) {
                    $mapFn = fn ($p) => [
                        'id'    => $p->id,
                        'title' => $p->title,
                        'slug'  => $p->slug,
                        'image' => asset($p->image),
                        'price' => $p->price ? round($p->price, 2) : null,
                    ];

                    /**
                     * Prefer FULLTEXT, fallback to LIKE
                     */
                    try {
                        return Product::query()
                            ->select([
                                'products.id',
                                'products.title',
                                'products.slug',
                            ])
                            ->where('products.status', 'active')
                            ->whereRaw(
                                "MATCH(products.title, products.sku) AGAINST (? IN BOOLEAN MODE)",
                                [$q . '*']
                            )
                            ->withMin(['offers as price' => function ($q) {
                                $q->where('status', 'active');
                            }], 'retail_price')
                            ->orderBy('price')
                            ->limit(10)
                            ->get()
                            ->map($mapFn);
                    } catch (\Throwable $e) {
                        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $q);
                        return Product::query()
                            ->select([
                                'products.id',
                                'products.title',
                                'products.slug',
                            ])
                            ->where('products.status', 'active')
                            ->where(function ($q2) use ($escaped) {
                                $q2->where('products.title', 'like', "%{$escaped}%")
                                    ->orWhere('products.sku', 'like', "%{$escaped}%");
                            })
                            ->withMin(['offers as price' => function ($q) {
                                $q->where('status', 'active');
                            }], 'retail_price')
                            ->orderBy('price')
                            ->limit(10)
                            ->get()
                            ->map($mapFn);
                    }
                }
            );

            return $this->success([
                'query'   => $q,
                'count'   => $results->count(),
                'results' => $results,
            ], 'Search results fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch search results');
        }
    }


    /**
     * Get related products
     *
     * Products similar to the given one (shared categories, platforms, or types). For "You may also like".
     *
     * @urlParam id integer required Product ID. Example: 25
     * @queryParam limit integer Number to return (default 6). Example: 6
     *
     * @response 200 {"status":"success","message":"Related products fetched successfully","data":[{"id":30,"title":"Windows 10 Pro","slug":"windows-10-pro","image":"/storage/products/windows-10.jpg","lowest_price":9.99}]}
     * @response 404 {"message":"No query results for model [App\\Models\\Product]."}
     */
    public function related(Request $request, int $id)
    {
        try {
            $limit = min((int) $request->input('limit', 8), 30);

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
                ->get()
                ->map(fn ($p) => [
                    'id'           => $p->id,
                    'title'        => $p->title,
                    'slug'         => $p->slug,
                    'image'        => asset($p->image),
                    'lowest_price' => $p->lowest_price
                        ? round($p->lowest_price, 2)
                        : null,
                ]);

            return $this->success($products, 'Related products fetched successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Product not found', 404);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch related products');
        }
    }


    /**
     * Get trending products
     *
     * Featured products for homepage "Trending now". Sorted by sort_order then lowest price.
     *
     * @queryParam limit integer Number to return (default 10). Example: 10
     *
     * @response 200 {"status":"success","message":"Trending products fetched successfully","data":[{"id":18,"title":"Office 2021 Professional Plus","slug":"office-2021-pro-plus","image":"/storage/products/office-2021.jpg","lowest_price":14.50}]}
     */
    public function trending(Request $request)
    {
        try {
            $limit = min((int) $request->input('limit', 8), 30);

            $products = Product::query()
                ->active()
                ->where('is_featured', true)
                ->withMin(['offers as lowest_price' => function ($q) {
                    $q->where('status', 'active');
                }], 'retail_price')
                ->orderByDesc('sort_order')
                ->orderBy('lowest_price')
                ->limit($limit)
                ->get()
                ->map(fn ($p) => [
                    'id'           => $p->id,
                    'title'        => $p->title,
                    'slug'         => $p->slug,
                    'image'        => asset($p->image),
                    'lowest_price' => $p->lowest_price
                        ? round($p->lowest_price, 2)
                        : null,
                ]);

            return $this->success($products, 'Trending products fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch trending products');
        }
    }
}
