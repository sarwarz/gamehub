<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;

/**
 * @group Menus
 *
 * Public navigation menu endpoints.
 * Returns hierarchical menu data for header, footer, and sidebar navigation.
 *
 * @unauthenticated
 */
class MenuApiController extends Controller
{
    /**
     * Get all active menus
     *
     * Returns all active menus with their nested items.
     * Each menu includes its location (header/footer/sidebar) and hierarchical items.
     *
     * @queryParam location string Filter by location: header, footer, sidebar. Example: header
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "Menus fetched successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Main Navigation",
     *       "slug": "main-navigation",
     *       "location": "header",
     *       "items": [
     *         {
     *           "id": 1,
     *           "title": "Home",
     *           "type": "link",
     *           "url": "/",
     *           "icon": null,
     *           "target": "_self",
     *           "children": []
     *         },
     *         {
     *           "id": 2,
     *           "title": "Shop",
     *           "type": "megamenu",
     *           "url": "/shop",
     *           "icon": null,
     *           "target": "_self",
     *           "columns": 4,
     *           "children": [
     *             {
     *               "id": 3,
     *               "title": "Games",
     *               "type": "link",
     *               "url": "/shop/games",
     *               "icon": null,
     *               "target": "_self",
     *               "children": [
     *                 {
     *                   "id": 4,
     *                   "title": "Productivity",
     *                   "type": "heading",
     *                   "url": "#",
     *                   "icon": null,
     *                   "target": "_self",
     *                   "children": [
     *                     { "id": 5, "title": "Office Suites", "type": "link", "url": "/shop/office", "icon": null, "target": "_self", "children": [] }
     *                   ]
     *                 }
     *               ]
     *             }
     *           ]
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        try {
            $query = Menu::active();

            if (request()->filled('location')) {
                $query->where('location', request('location'));
            }

            $menus = $query->with(['items' => function ($q) {
                $q->whereNull('parent_id')->where('is_active', true)->orderBy('position');
            }, 'items.allActiveChildren'])->get();

            $data = $menus->map(fn($menu) => [
                'id'       => $menu->id,
                'name'     => $menu->name,
                'slug'     => $menu->slug,
                'location' => $menu->location,
                'items'    => $this->transformItems($menu->items),
            ]);

            return $this->success($data, 'Menus fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch menus');
        }
    }

    /**
     * Get menu by location
     *
     * Returns a single menu by its location. If multiple menus exist
     * for a location, the first active one is returned.
     *
     * @urlParam location string required The menu location. Example: header
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "Menu fetched successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Main Navigation",
     *     "slug": "main-navigation",
     *     "location": "header",
     *     "items": []
     *   }
     * }
     * @response 404 scenario="not found" {
     *   "status": false,
     *   "message": "Menu not found."
     * }
     */
    public function byLocation(string $location): JsonResponse
    {
        try {
            $menu = Menu::active()
                ->where('location', $location)
                ->with(['items' => function ($q) {
                    $q->whereNull('parent_id')->where('is_active', true)->orderBy('position');
                }, 'items.allActiveChildren'])->first();

            if (!$menu) {
                return $this->error('Menu not found.', 404);
            }

            return $this->success([
                'id'       => $menu->id,
                'name'     => $menu->name,
                'slug'     => $menu->slug,
                'location' => $menu->location,
                'items'    => $this->transformItems($menu->items),
            ], 'Menu fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch menu');
        }
    }

    private function transformItems($items): array
    {
        return $items->map(function ($item) {
            $data = [
                'id'       => $item->id,
                'title'    => $item->title,
                'type'     => $item->type,
                'url'      => $item->url,
                'icon'     => $item->icon,
                'target'   => $item->target,
                'children' => $item->allActiveChildren
                    ? $this->transformItems($item->allActiveChildren)
                    : [],
            ];

            if ($item->type === 'megamenu') {
                $data['columns'] = (int) $item->columns;
            }

            if ($item->badge_text) {
                $data['badge'] = [
                    'text'  => $item->badge_text,
                    'color' => $item->badge_color,
                ];
            }

            return $data;
        })->values()->toArray();
    }
}
