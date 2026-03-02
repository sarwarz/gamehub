<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SellerOffer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Wholesale
 *
 * APIs for browsing wholesale offers, comparing bulk prices,
 * and calculating tier-based pricing for bulk purchases.
 */
class WholesaleController extends Controller
{
    /**
     * Wholesale products
     *
     * Browse products that have active wholesale offers.
     * Only shows products where at least one seller has wholesale pricing.
     *
     * @unauthenticated
     *
     * @queryParam search string Search by product title. Example: Windows
     * @queryParam category_id integer Filter by category. Example: 1
     * @queryParam platform_id integer Filter by platform. Example: 2
     * @queryParam min_price numeric Minimum wholesale price. Example: 5
     * @queryParam max_price numeric Maximum wholesale price. Example: 50
     * @queryParam sort string Sort: price_asc, price_desc, newest, popular. Example: price_asc
     * @queryParam per_page integer Results per page (default 12). Example: 24
     *
     * @response 200 {"status":true,"message":"Wholesale products fetched","data":{"current_page":1,"data":[{"id":5,"title":"Windows 11 Pro","slug":"windows-11-pro","image":"uploads/products/win11.jpg","best_wholesale_price":"20.00","offers_count":3}],"total":15}}
     */
    public function products(Request $request): JsonResponse
    {
        try {
            $query = Product::where('status', 'active')
                ->whereHas('offers', function ($q) {
                    $q->where('status', 'active')
                      ->whereIn('sale_mode', ['wholesale', 'both'])
                      ->where(function ($wq) {
                          $wq->whereNotNull('wholesale_10_99_price')
                             ->orWhereNotNull('wholesale_100_plus_price');
                      });
                })
                ->with(['categories:id,name,slug', 'platforms:id,name'])
                ->withMin([
                    'offers as best_wholesale_price' => fn ($q) => $q->where('status', 'active')
                        ->whereIn('sale_mode', ['wholesale', 'both'])
                        ->whereNotNull('wholesale_10_99_price'),
                ], 'wholesale_10_99_price')
                ->withMin([
                    'offers as best_retail_price' => fn ($q) => $q->where('status', 'active'),
                ], 'retail_price')
                ->withCount([
                    'offers as wholesale_offers_count' => fn ($q) => $q->where('status', 'active')
                        ->whereIn('sale_mode', ['wholesale', 'both']),
                ]);

            if ($request->search) {
                $query->where('title', 'like', "%{$request->search}%");
            }

            if ($request->category_id) {
                $query->whereHas('categories', fn ($q) => $q->where('product_categories.id', $request->category_id));
            }

            if ($request->platform_id) {
                $query->whereHas('platforms', fn ($q) => $q->where('product_platforms.id', $request->platform_id));
            }

            if ($request->filled('min_price')) {
                $query->having('best_wholesale_price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->having('best_wholesale_price', '<=', $request->max_price);
            }

            $query = match ($request->input('sort')) {
                'price_asc'  => $query->orderBy('best_wholesale_price', 'asc'),
                'price_desc' => $query->orderByDesc('best_wholesale_price'),
                'popular'    => $query->orderByDesc('wholesale_offers_count'),
                default      => $query->latest(),
            };

            $products = $query->paginate(min($request->integer('per_page', 12), 50));

            $products->getCollection()->transform(fn ($p) => [
                'id'                    => $p->id,
                'title'                 => $p->title,
                'slug'                  => $p->slug,
                'image'                 => $p->image,
                'short_description'     => $p->short_description,
                'best_retail_price'     => $p->best_retail_price,
                'best_wholesale_price'  => $p->best_wholesale_price,
                'savings_pct'           => $p->best_retail_price > 0 && $p->best_wholesale_price > 0
                    ? round((1 - $p->best_wholesale_price / $p->best_retail_price) * 100, 1)
                    : 0,
                'wholesale_offers_count' => $p->wholesale_offers_count,
                'categories'            => $p->categories->map(fn ($c) => $c->only(['id', 'name', 'slug'])),
                'platforms'             => $p->platforms->pluck('name'),
            ]);

            return $this->success($products, 'Wholesale products fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch wholesale products');
        }
    }

    /**
     * Product wholesale offers
     *
     * Get all wholesale offers for a specific product with full price tier breakdown.
     * Each offer shows retail vs wholesale pricing with savings percentage.
     *
     * @unauthenticated
     *
     * @urlParam productId integer required Product ID. Example: 5
     *
     * @queryParam sort string Sort offers: price_asc, price_desc, rating. Example: price_asc
     *
     * @response 200 {"status":true,"message":"Wholesale offers fetched","data":{"product":{"id":5,"title":"Windows 11 Pro"},"offers":[{"offer_id":1,"seller":{"id":1,"store_name":"GameHub Store","rating":4.5,"is_verified":true},"pricing":{"retail":{"price":"29.99","min_qty":1},"wholesale_10_99":{"price":"25.00","min_qty":10,"savings_pct":16.6},"wholesale_100_plus":{"price":"20.00","min_qty":100,"savings_pct":33.3}},"available_stock":250}]}}
     * @response 404 {"status":false,"message":"Product not found"}
     */
    public function productOffers(Request $request, int $productId): JsonResponse
    {
        try {
            $product = Product::where('status', 'active')->find($productId);
            if (!$product) {
                return $this->error('Product not found', 404);
            }

            $offersQuery = SellerOffer::where('product_id', $productId)
                ->where('status', 'active')
                ->whereIn('sale_mode', ['wholesale', 'both'])
                ->where(function ($q) {
                    $q->whereNotNull('wholesale_10_99_price')
                      ->orWhereNotNull('wholesale_100_plus_price');
                })
                ->with('seller:id,store_name,slug,rating,is_verified')
                ->withCount(['keys as available_stock' => fn ($q) => $q->where('status', 'available')]);

            $offersQuery = match ($request->input('sort')) {
                'price_asc'  => $offersQuery->orderBy('wholesale_10_99_price', 'asc'),
                'price_desc' => $offersQuery->orderByDesc('wholesale_10_99_price'),
                'rating'     => $offersQuery->orderByDesc(
                    \Illuminate\Support\Facades\DB::raw('(SELECT rating FROM sellers WHERE sellers.id = seller_offers.seller_id)')
                ),
                default      => $offersQuery->orderBy('wholesale_10_99_price', 'asc'),
            };

            $offers = $offersQuery->get()->map(function ($offer) {
                $retail = (float) $offer->retail_price;

                $tiers = [
                    'retail' => [
                        'price'   => $offer->retail_price,
                        'min_qty' => 1,
                        'max_qty' => 9,
                    ],
                ];

                if ($offer->wholesale_10_99_price) {
                    $wsPrice = (float) $offer->wholesale_10_99_price;
                    $tiers['wholesale_10_99'] = [
                        'price'       => $offer->wholesale_10_99_price,
                        'min_qty'     => 10,
                        'max_qty'     => 99,
                        'savings_pct' => $retail > 0 ? round((1 - $wsPrice / $retail) * 100, 1) : 0,
                    ];
                }

                if ($offer->wholesale_100_plus_price) {
                    $wsPrice = (float) $offer->wholesale_100_plus_price;
                    $tiers['wholesale_100_plus'] = [
                        'price'       => $offer->wholesale_100_plus_price,
                        'min_qty'     => 100,
                        'max_qty'     => null,
                        'savings_pct' => $retail > 0 ? round((1 - $wsPrice / $retail) * 100, 1) : 0,
                    ];
                }

                return [
                    'offer_id'        => $offer->id,
                    'seller'          => $offer->seller?->only(['id', 'store_name', 'slug', 'rating', 'is_verified']),
                    'pricing'         => $tiers,
                    'available_stock' => $offer->available_stock,
                    'sale_mode'       => $offer->sale_mode,
                ];
            });

            return $this->success([
                'product' => $product->only(['id', 'title', 'slug', 'image', 'short_description']),
                'offers'  => $offers,
            ], 'Wholesale offers fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch wholesale offers');
        }
    }

    /**
     * Calculate bulk price
     *
     * Calculate the total price for a bulk purchase based on quantity.
     * Automatically applies the correct wholesale tier pricing.
     * Validates stock availability.
     *
     * @unauthenticated
     *
     * @bodyParam seller_offer_id integer required Seller offer ID. Example: 1
     * @bodyParam quantity integer required Number of units (min 1). Example: 50
     *
     * @response 200 {"status":true,"message":"Price calculated","data":{"offer_id":1,"product":{"id":5,"title":"Windows 11 Pro"},"quantity":50,"tier":"wholesale_10_99","unit_price":"25.00","retail_price":"29.99","total":"1250.00","retail_total":"1499.50","savings":"249.50","savings_pct":16.6,"available_stock":250,"in_stock":true}}
     * @response 404 {"status":false,"message":"Offer not found or not available for wholesale"}
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'seller_offer_id' => 'required|integer',
            'quantity'        => 'required|integer|min:1',
        ]);

        try {
            $offer = SellerOffer::where('status', 'active')
                ->with('product:id,title,slug,image')
                ->withCount(['keys as available_stock' => fn ($q) => $q->where('status', 'available')])
                ->find($request->seller_offer_id);

            if (!$offer) {
                return $this->error('Offer not found or inactive', 404);
            }

            $qty = $request->quantity;
            $tier = self::resolveTier($offer, $qty);
            $unitPrice = $tier['price'];
            $retailPrice = (float) $offer->retail_price;
            $total = round($unitPrice * $qty, 2);
            $retailTotal = round($retailPrice * $qty, 2);

            return $this->success([
                'offer_id'        => $offer->id,
                'product'         => $offer->product?->only(['id', 'title', 'slug', 'image']),
                'quantity'        => $qty,
                'tier'            => $tier['name'],
                'unit_price'      => number_format($unitPrice, 2, '.', ''),
                'retail_price'    => $offer->retail_price,
                'total'           => number_format($total, 2, '.', ''),
                'retail_total'    => number_format($retailTotal, 2, '.', ''),
                'savings'         => number_format($retailTotal - $total, 2, '.', ''),
                'savings_pct'     => $retailPrice > 0 ? round((1 - $unitPrice / $retailPrice) * 100, 1) : 0,
                'available_stock' => $offer->available_stock,
                'in_stock'        => $offer->available_stock >= $qty,
            ], 'Price calculated');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to calculate price');
        }
    }

    /**
     * Resolve the pricing tier for a given offer and quantity.
     */
    public static function resolveTier(SellerOffer $offer, int $qty): array
    {
        $canWholesale = in_array($offer->sale_mode, ['wholesale', 'both']);

        if ($canWholesale && $qty >= 100 && $offer->wholesale_100_plus_price) {
            return ['name' => 'wholesale_100_plus', 'price' => (float) $offer->wholesale_100_plus_price];
        }

        if ($canWholesale && $qty >= 10 && $offer->wholesale_10_99_price) {
            return ['name' => 'wholesale_10_99', 'price' => (float) $offer->wholesale_10_99_price];
        }

        return ['name' => 'retail', 'price' => (float) $offer->retail_price];
    }
}
