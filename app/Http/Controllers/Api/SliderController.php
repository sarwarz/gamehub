<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;

/**
 * @group Sliders
 *
 * APIs for homepage sliders, banners, and promotional content.
 * Supports hero sliders, banners, promotional, and product spotlight types.
 * All endpoints are public and do not require authentication.
 *
 * @unauthenticated
 */
class SliderController extends Controller
{
    /**
     * List active sliders
     *
     * Retrieve all currently live sliders ordered by position.
     * Only returns sliders that are active and within their scheduled date range.
     *
     * @queryParam type string Filter by type: hero, banner, promotional, product_spotlight. Example: hero
     * @queryParam position int Filter by specific position. Example: 1
     * @queryParam limit int Limit results (1-50, default: all). Example: 5
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Sliders fetched successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "type": "hero",
     *       "title": "Summer Sale",
     *       "subtitle": "Up to 70% off",
     *       "image": "https://example.com/uploads/sliders/hero.jpg",
     *       "badge": { "text": "SALE", "color": "#ea5455" },
     *       "button": { "text": "Shop Now", "url": "https://example.com/products/summer-sale" },
     *       "appearance": { "overlay_color": "rgba(0,0,0,0.4)", "text_color": "light", "text_position": "left" },
     *       "position": 1,
     *       "product": { "id": 5, "title": "Windows 11 Pro", "slug": "windows-11-pro", "price": 29.99 }
     *     }
     *   ],
     *   "meta": { "total": 1 }
     * }
     */
    public function index(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $query = Slider::with('product')
                ->scheduled()
                ->ordered();

            if ($request->filled('type')) {
                $query->ofType($request->type);
            }
            if ($request->filled('position')) {
                $query->where('position', $request->position);
            }
            if ($request->filled('limit')) {
                $query->limit((int) $request->limit);
            }

            $sliders = $query->get();

            rescue(fn () => $sliders->each->incrementViews());

            return $this->success(
                $sliders->map(fn ($s) => $this->transform($s)),
                'Sliders fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch sliders', 500);
        }
    }

    /**
     * Get slider details
     *
     * Retrieve a single active slider by ID with full details.
     *
     * @urlParam id int required Slider ID. Example: 3
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Slider details fetched",
     *   "data": {}
     * }
     * @response 404 {
     *   "status": false,
     *   "message": "Slider not found"
     * }
     */
    public function show($id)
    {
        try {
            $slider = Slider::with('product')
                ->scheduled()
                ->find($id);

            if (!$slider) {
                return $this->error('Slider not found', 404);
            }

            $slider->incrementViews();

            return $this->success(
                $this->transform($slider),
                'Slider details fetched'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch slider details', 500);
        }
    }

    /**
     * Track slider click
     *
     * Record a click event for analytics tracking.
     *
     * @urlParam id int required Slider ID. Example: 3
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Click recorded"
     * }
     */
    public function trackClick($id)
    {
        try {
            $slider = Slider::find($id);

            if (!$slider) {
                return $this->error('Slider not found', 404);
            }

            $slider->incrementClicks();

            return $this->success(null, 'Click recorded');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to record click', 500);
        }
    }

    /**
     * List sliders by type
     *
     * Convenience endpoint to get sliders filtered by type.
     *
     * @urlParam type string required Slider type. Example: hero
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Sliders fetched",
     *   "data": []
     * }
     */
    public function byType(string $type)
    {
        try {
            $valid = ['hero', 'banner', 'promotional', 'product_spotlight'];
            if (!in_array($type, $valid)) {
                return $this->error('Invalid slider type. Use: ' . implode(', ', $valid), 422);
            }

            $sliders = Slider::with('product')
                ->scheduled()
                ->ofType($type)
                ->ordered()
                ->get();

            rescue(fn () => $sliders->each->incrementViews());

            return $this->success(
                $sliders->map(fn ($s) => $this->transform($s)),
                'Sliders fetched'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch sliders', 500);
        }
    }

    /* ── Transform ──────────────────────────────────── */

    protected function transform(Slider $slider): array
    {
        $data = [
            'id'         => $slider->id,
            'type'       => $slider->type,
            'title'      => $slider->display_title,
            'subtitle'   => $slider->display_subtitle,
            'image'      => $slider->image_url,
            'badge'      => $slider->badge_text ? [
                'text'  => $slider->badge_text,
                'color' => $slider->badge_color,
            ] : null,
            'button'     => [
                'text' => $slider->button_text ?? 'View',
                'url'  => $slider->display_url,
            ],
            'appearance' => [
                'overlay_color' => $slider->overlay_color,
                'text_color'    => $slider->text_color,
                'text_position' => $slider->text_position,
            ],
            'position'   => $slider->position,
            'product'    => null,
        ];

        if ($slider->product) {
            $data['product'] = [
                'id'    => $slider->product->id,
                'title' => $slider->product->title,
                'slug'  => $slider->product->slug,
                'price' => $slider->product->price ?? null,
                'image' => $slider->product->image ? asset($slider->product->image) : null,
            ];
        }

        return $data;
    }
}
