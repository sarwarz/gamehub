<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Search
 *
 * Lightweight search and autocomplete APIs
 * for the storefront search bar.
 */
class SearchController extends Controller
{
    /**
     * Autocomplete
     *
     * Fast autocomplete endpoint for the search bar.
     * Returns matching products and sellers with minimal data.
     *
     * @unauthenticated
     *
     * @queryParam q string required Search query (min 2 chars). Example: win
     * @queryParam limit integer Max results per type (default 5). Example: 8
     *
     * @response 200 {"status":true,"message":"Results fetched","data":{"products":[],"sellers":[]}}
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        try {
            $q = $request->q;
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $q);
            $limit = min($request->input('limit', 5), 10);

            $products = Product::where('status', 'active')
                ->where(function ($query) use ($escaped) {
                    $query->where('title', 'like', "%{$escaped}%")
                          ->orWhere('slug', 'like', "%{$escaped}%");
                })
                ->select('id', 'title', 'slug', 'image')
                ->withMin(['offers as best_price' => fn ($o) => $o->where('status', 'active')], 'retail_price')
                ->limit($limit)
                ->get()
                ->map(fn ($p) => [
                    'id'         => $p->id,
                    'title'      => $p->title,
                    'slug'       => $p->slug,
                    'image'      => $p->image,
                    'best_price' => $p->best_price,
                ]);

            $sellers = Seller::active()
                ->where('store_name', 'like', "%{$escaped}%")
                ->select('id', 'store_name', 'slug', 'logo', 'rating', 'is_verified')
                ->limit($limit)
                ->get();

            return $this->success([
                'products' => $products,
                'sellers'  => $sellers,
            ], 'Results fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch autocomplete results');
        }
    }

    /**
     * Global search
     *
     * Full search with pagination, filters and sorting.
     *
     * @unauthenticated
     *
     * @queryParam q string required Search query. Example: windows 11
     * @queryParam category_id integer Filter by category. Example: 1
     * @queryParam platform_id integer Filter by platform. Example: 2
     * @queryParam min_price numeric Minimum price. Example: 5
     * @queryParam max_price numeric Maximum price. Example: 100
     * @queryParam sort string Sort: relevance, price_asc, price_desc, newest, rating. Example: price_asc
     * @queryParam per_page integer Results per page (default 12). Example: 24
     *
     * @response 200 {"status":true,"message":"Search results","data":{"current_page":1,"data":[],"total":0}}
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1|max:200',
        ]);

        try {
            $q = $request->q;
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $q);

            $query = Product::where('status', 'active')
                ->where(function ($qb) use ($escaped) {
                    $qb->where('title', 'like', "%{$escaped}%")
                        ->orWhere('short_description', 'like', "%{$escaped}%")
                        ->orWhere('slug', 'like', "%{$escaped}%");
                })
                ->with([
                    'categories:id,name,slug',
                    'platforms:id,name',
                ])
                ->withMin(['offers as best_price' => fn ($o) => $o->where('status', 'active')], 'retail_price')
                ->withCount(['offers as sellers_count' => fn ($o) => $o->where('status', 'active')]);

            if ($request->filled('category_id')) {
                $query->whereHas('categories', fn ($cq) => $cq->where('product_categories.id', $request->category_id));
            }

            if ($request->filled('platform_id')) {
                $query->whereHas('platforms', fn ($pq) => $pq->where('product_platforms.id', $request->platform_id));
            }

            if ($request->filled('min_price') || $request->filled('max_price')) {
                $query->whereHas('offers', function ($oq) use ($request) {
                    $oq->where('status', 'active');
                    if ($request->filled('min_price')) {
                        $oq->where('retail_price', '>=', $request->min_price);
                    }
                    if ($request->filled('max_price')) {
                        $oq->where('retail_price', '<=', $request->max_price);
                    }
                });
            }

            $query = match ($request->input('sort')) {
                'price_asc'  => $query->orderBy('best_price', 'asc'),
                'price_desc' => $query->orderByDesc('best_price'),
                'newest'     => $query->latest(),
                'rating'     => $query->orderByDesc('sort_order'),
                default      => $query->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", ["{$escaped}%"])->latest(),
            };

            $products = $query->paginate(min($request->integer('per_page', 12), 50));

            $products->getCollection()->transform(fn ($p) => [
                'id'                => $p->id,
                'title'             => $p->title,
                'slug'              => $p->slug,
                'image'             => $p->image,
                'short_description' => $p->short_description,
                'best_price'        => $p->best_price,
                'sellers_count'     => $p->sellers_count,
                'categories'        => $p->categories->map(fn ($c) => $c->only(['id', 'name', 'slug'])),
                'platforms'         => $p->platforms->pluck('name'),
                'is_featured'       => $p->is_featured,
            ]);

            return $this->success($products, 'Search results');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch search results');
        }
    }
}
