<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Services\CurrencyService;

class ProductController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Product listing (paginated + filters)
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

        // Search filter
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // 🏷 Taxonomy filters
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

        // Get default currency
        $defaultCurrency = \App\Models\Currency::where('is_default', true)->first();

        // Transform products
        $productsData = $products->map(function ($product) use ($defaultCurrency) {
            // Get lowest offer
            $lowestOffer = $product->offers->sortBy('retail_price')->first();

            return [
                'id'          => $product->id,
                'title'       => $product->title,
                'slug'        => $product->slug,
                'cover_image' => $product->cover_image,
                'developer'   => $product->developer,
                'publisher'   => $product->publisher,
                'categories'  => $product->categories,
                'platforms'   => $product->platforms,
                'types'       => $product->types,
                'regions'     => $product->regions,
                'languages'   => $product->languages,
                'works_on'    => $product->worksOn,
                //  Lowest price only
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
     * Single product detail
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

        //  Sort offers by retail_price before transforming
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
            ->values(); // reindex after sorting

        return response()->json([
            'status'  => 'success',
            'message' => 'Product details fetched successfully',
            'data'    => [
                'id'                => $product->id,
                'title'             => $product->title,
                'slug'              => $product->slug,
                'sku'               => $product->sku,
                'short_description' => $product->short_description,
                'description'       => $product->description,
                'cover_image'       => $product->cover_image,
                'gallery'           => $product->gallery ?? [],
                'developer'         => $product->developer,
                'publisher'         => $product->publisher,
                'categories'        => $product->categories,
                'platforms'         => $product->platforms,
                'types'             => $product->types,
                'regions'           => $product->regions,
                'languages'         => $product->languages,
                'works_on'          => $product->worksOn,
                'system_requirements'=> $product->system_requirements,
                'offers'            => $offers,
                'promoted_offers'   => $offers->where('promoted', true)->values(),
                'currencies'        => $currencies,
                'meta' => [
                    'title'       => $product->meta_title,
                    'description' => $product->meta_description,
                    'keywords'    => $product->meta_keywords,
                    'is_featured' => $product->is_featured,
                    'sort_order'  => $product->sort_order,
                    'delivery_type'=> $product->delivery_type,
                ],
            ],
        ]);
    }




}
