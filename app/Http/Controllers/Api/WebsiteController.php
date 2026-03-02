<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Slider;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Website
 *
 * Public website page configuration APIs.
 * Returns fully structured data for rendering the homepage, shop page, and footer.
 * All endpoints are public and do not require authentication.
 *
 * @unauthenticated
 */
class WebsiteController extends Controller
{
    /**
     * Get homepage data
     *
     * Returns the complete homepage configuration with all section data,
     * ordered according to admin settings. Each section includes its
     * configuration and actual content data (products, categories, etc).
     *
     * @queryParam sections string Comma-separated list of specific sections to load. If omitted, all enabled sections are returned. Example: featured_products,new_arrivals,hot_deals
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "Homepage data fetched successfully",
     *   "data": {
     *     "sections_order": ["hero_slider", "category_bar", "featured_products"],
     *     "sections": {
     *       "hero_slider": {
     *         "enabled": true,
     *         "config": {"autoplay": true, "speed": 5000, "type": "hero"},
     *         "data": {"sliders": []}
     *       },
     *       "featured_products": {
     *         "enabled": true,
     *         "config": {"title": "Featured Products", "subtitle": "...", "limit": 8},
     *         "data": {"products": []}
     *       }
     *     }
     *   }
     * }
     */
    public function homepage(Request $request): JsonResponse
    {
        try {
            $allSettings = Setting::group('homepage');
            $order = $allSettings['sections_order'] ?? [
                'hero_slider', 'category_bar', 'featured_products',
                'promotional_banner', 'new_arrivals', 'categories_grid',
                'stats_counter', 'hot_deals', 'blog_section', 'newsletter',
            ];

            $requestedSections = $request->query('sections')
                ? explode(',', $request->query('sections'))
                : null;

            $sections = [];
            foreach ($order as $sectionKey) {
                if ($requestedSections && !in_array($sectionKey, $requestedSections)) {
                    continue;
                }

                $config = $allSettings[$sectionKey] ?? [];
                $enabled = filter_var($config['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);

                if (!$enabled && !$requestedSections) {
                    continue;
                }

                $data = $this->loadSectionData($sectionKey, $config);

                $sections[$sectionKey] = [
                    'enabled' => $enabled,
                    'config'  => $this->cleanConfig($config),
                    'data'    => $data,
                ];
            }

            return $this->success([
                'sections_order' => $requestedSections
                    ? array_values(array_intersect($order, $requestedSections))
                    : array_values(array_filter($order, fn ($k) => isset($sections[$k]))),
                'sections'       => $sections,
            ], 'Homepage data fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch homepage data', 500);
        }
    }

    /**
     * Get homepage section
     *
     * Returns data for a single homepage section by key.
     * Useful for lazy-loading individual sections.
     *
     * @urlParam section string required The section key. Example: featured_products
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "Section fetched successfully",
     *   "data": {
     *     "enabled": true,
     *     "config": {"title": "Featured Products", "limit": 8},
     *     "data": {"products": []}
     *   }
     * }
     * @response 404 scenario="not found" {
     *   "status": false,
     *   "message": "Section not found."
     * }
     */
    public function homepageSection(string $section): JsonResponse
    {
        try {
            $config = Setting::get('homepage', $section);

            if (!$config) {
                return $this->error('Section not found.', 404);
            }

            $data = $this->loadSectionData($section, $config);

            return $this->success([
                'enabled' => filter_var($config['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'config'  => $this->cleanConfig($config),
                'data'    => $data,
            ], 'Section fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch section', 500);
        }
    }

    /**
     * Get shop page configuration
     *
     * Returns the shop page layout settings, filter visibility,
     * banner configuration, and SEO metadata.
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "Shop page configuration fetched successfully",
     *   "data": {
     *     "layout": {
     *       "default_view": "grid",
     *       "products_per_page": 12,
     *       "default_sort": "featured",
     *       "sidebar_position": "left",
     *       "columns": 4
     *     },
     *     "filters": {
     *       "price_range": true,
     *       "categories": true,
     *       "platforms": true
     *     },
     *     "banner": {
     *       "enabled": false,
     *       "title": "",
     *       "image": "",
     *       "url": ""
     *     },
     *     "seo": {
     *       "title": "All Products",
     *       "description": "..."
     *     }
     *   }
     * }
     */
    public function shoppage(): JsonResponse
    {
        try {
            $settings = Setting::group('shoppage');

            $filters = $settings['filters'] ?? [];
            foreach ($filters as &$v) {
                $v = filter_var($v, FILTER_VALIDATE_BOOLEAN);
            }
            $settings['filters'] = $filters;

            return $this->success($settings, 'Shop page configuration fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch shop page configuration', 500);
        }
    }

    /**
     * Get footer configuration
     *
     * Returns the complete footer configuration including about section,
     * column layout with links, bottom bar, payment icons, and social links.
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "Footer configuration fetched successfully",
     *   "data": {
     *     "about": {
     *       "show_logo": true,
     *       "description": "Your trusted marketplace...",
     *       "show_social": true
     *     },
     *     "columns": [],
     *     "bottom_bar": {
     *       "copyright": "© 2026 GameHub. All rights reserved.",
     *       "links": []
     *     },
     *     "payment_icons": {
     *       "enabled": true,
     *       "methods": ["visa", "mastercard", "paypal"]
     *     },
     *     "social": {}
     *   }
     * }
     */
    public function footer(): JsonResponse
    {
        try {
            $settings = Setting::group('footer');

            $bottomBar = $settings['bottom_bar'] ?? [];
            if (isset($bottomBar['copyright'])) {
                $bottomBar['copyright'] = str_replace('{year}', date('Y'), $bottomBar['copyright']);
            }
            $settings['bottom_bar'] = $bottomBar;

            $social = Setting::group('social');
            $settings['social'] = $social;

            return $this->success($settings, 'Footer configuration fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch footer configuration', 500);
        }
    }

    /* ── Private helpers ── */

    private function loadSectionData(string $key, array $config): array
    {
        $limit = (int) ($config['limit'] ?? 8);

        switch ($key) {
            case 'hero_slider':
                $sliders = Slider::active()
                    ->scheduled()
                    ->ofType($config['type'] ?? 'hero')
                    ->ordered()
                    ->get()
                    ->map(fn ($s) => [
                        'id'            => $s->id,
                        'title'         => $s->title,
                        'subtitle'      => $s->subtitle,
                        'badge_text'    => $s->badge_text,
                        'badge_color'   => $s->badge_color,
                        'image'         => $s->image_url,
                        'button_text'   => $s->button_text,
                        'button_url'    => $s->button_url,
                        'overlay_color' => $s->overlay_color,
                        'text_color'    => $s->text_color,
                        'text_position' => $s->text_position,
                    ]);
                return ['sliders' => $sliders];

            case 'category_bar':
            case 'categories_grid':
                $categories = ProductCategory::where('status', 'active')
                    ->limit($limit)
                    ->get()
                    ->map(fn ($c) => [
                        'id'   => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                    ]);
                return ['categories' => $categories];

            case 'featured_products':
                $query = Product::where('status', 'active')->with('offers');
                $sort = $config['sort'] ?? 'featured';
                $query = $this->applyProductSort($query, $sort);
                $products = $query->limit($limit)->get()->map(fn ($p) => $this->transformProduct($p));
                return ['products' => $products];

            case 'new_arrivals':
                $products = Product::where('status', 'active')
                    ->with('offers')
                    ->orderByDesc('created_at')
                    ->limit($limit)
                    ->get()
                    ->map(fn ($p) => $this->transformProduct($p));
                return ['products' => $products];

            case 'hot_deals':
                $products = Product::where('status', 'active')
                    ->with('offers')
                    ->whereHas('offers', fn ($q) => $q->where('status', 'active'))
                    ->orderByDesc('is_featured')
                    ->limit($limit)
                    ->get()
                    ->map(fn ($p) => $this->transformProduct($p));
                return ['products' => $products];

            case 'blog_section':
                $posts = Blog::where('is_published', true)
                    ->orderByDesc('published_at')
                    ->limit($limit)
                    ->get()
                    ->map(fn ($b) => [
                        'id'         => $b->id,
                        'title'      => $b->title,
                        'slug'       => $b->slug,
                        'excerpt'    => \Illuminate\Support\Str::limit(strip_tags($b->content), 120),
                        'image'      => $b->featured_image,
                        'created_at' => $b->created_at?->toISOString(),
                    ]);
                return ['posts' => $posts];

            case 'stats_counter':
                return ['items' => $config['items'] ?? []];

            case 'promotional_banner':
            case 'newsletter':
                return [];

            default:
                return [];
        }
    }

    private function applyProductSort($query, string $sort)
    {
        return match ($sort) {
            'newest'    => $query->orderByDesc('created_at'),
            'price_low' => $query->withMin(['offers as min_price' => fn ($q) => $q->where('status', 'active')], 'retail_price')
                                 ->orderBy('min_price'),
            'price_high' => $query->withMin(['offers as min_price' => fn ($q) => $q->where('status', 'active')], 'retail_price')
                                  ->orderByDesc('min_price'),
            default     => $query->orderByDesc('is_featured')->orderByDesc('created_at'),
        };
    }

    private function transformProduct($product): array
    {
        $lowestOffer = $product->offers
            ->where('status', 'active')
            ->sortBy('retail_price')
            ->first();

        return [
            'id'          => $product->id,
            'title'       => $product->title,
            'slug'        => $product->slug,
            'image'       => $product->image ? asset($product->image) : null,
            'price'       => $lowestOffer ? (float) $lowestOffer->retail_price : null,
            'is_featured' => $product->is_featured,
            'label'       => $product->label?->name ?? null,
            'badges'      => $this->getProductBadges($product),
        ];
    }

    private function getProductBadges($product): array
    {
        $badges = [];
        if ($product->is_featured) $badges[] = 'Featured';
        if ($product->created_at >= now()->subDays(7)) $badges[] = 'New';
        return $badges;
    }

    private function cleanConfig(array $config): array
    {
        unset($config['enabled']);
        return $config;
    }
}
