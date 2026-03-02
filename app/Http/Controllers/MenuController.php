<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\FaqCategory;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDeveloper;
use App\Models\ProductLanguage;
use App\Models\ProductPlatform;
use App\Models\ProductPublisher;
use App\Models\ProductRegion;
use App\Models\ProductType;
use App\Models\ProductWorksOn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::withCount('allItems')->orderBy('location')->get();
        return view('content.menus.index', compact('menus'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'required|in:header,footer,sidebar',
        ]);

        Menu::create([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'location'  => $request->location,
            'is_active' => true,
        ]);

        return redirect()->route('menus.index')->with('success', 'Menu created.');
    }

    public function edit(Menu $menu)
    {
        $menu->load(['allItems' => fn($q) => $q->orderBy('position')]);
        $items = $this->buildTree($menu->allItems);
        return view('content.menus.edit', compact('menu', 'items'));
    }

    public function update(Request $request, Menu $menu)
    {

        $menu->update([
            'name'      => $request->input('name', $menu->name),
            'slug'      => Str::slug($request->input('name', $menu->name)),
            'location'  => $request->input('location', $menu->location),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $menu->allItems()->delete();

        $items = json_decode($request->input('items', '[]'), true);
        if (is_array($items)) {
            $this->saveMenuItems($menu, $items);
        }

        return redirect()->route('menus.edit', $menu->id)
            ->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu)
    {

        $menu->delete();
        return response()->json(['success' => true]);
    }

    protected function saveMenuItems(Menu $menu, array $items, $parentId = null, &$pos = 0): void
    {
        foreach ($items as $item) {
            $menuItem = MenuItem::create([
                'menu_id'     => $menu->id,
                'parent_id'   => $parentId,
                'title'       => $item['title'] ?? 'Untitled',
                'type'        => $item['type'] ?? 'link',
                'columns'     => $item['columns'] ?? 4,
                'url'         => $item['url'] ?? '#',
                'icon'        => $item['icon'] ?? null,
                'badge_text'  => $item['badge_text'] ?? null,
                'badge_color' => $item['badge_color'] ?? null,
                'target'      => $item['target'] ?? '_self',
                'position'    => $pos++,
                'is_active'   => $item['is_active'] ?? true,
            ]);
            if (!empty($item['children'])) {
                $this->saveMenuItems($menu, $item['children'], $menuItem->id, $pos);
            }
        }
    }

    public function linkableItems(Request $request): JsonResponse
    {
        $source = $request->input('source');
        $search = $request->input('q', '');
        $items  = [];

        $sourceMap = [
            'categories'  => ['model' => ProductCategory::class,  'label' => 'name', 'prefix' => '/shop/category/'],
            'platforms'   => ['model' => ProductPlatform::class,   'label' => 'name', 'prefix' => '/shop/platform/'],
            'types'       => ['model' => ProductType::class,       'label' => 'name', 'prefix' => '/shop/type/'],
            'regions'     => ['model' => ProductRegion::class,     'label' => 'name', 'prefix' => '/shop/region/'],
            'languages'   => ['model' => ProductLanguage::class,   'label' => 'name', 'prefix' => '/shop/language/'],
            'works-on'    => ['model' => ProductWorksOn::class,    'label' => 'name', 'prefix' => '/shop/works-on/'],
            'developers'  => ['model' => ProductDeveloper::class,  'label' => 'name', 'prefix' => '/developer/'],
            'publishers'  => ['model' => ProductPublisher::class,  'label' => 'name', 'prefix' => '/publisher/'],
            'products'    => ['model' => Product::class,           'label' => 'title', 'prefix' => '/product/'],
            'pages'       => ['model' => Page::class,              'label' => 'title', 'prefix' => '/page/', 'active' => 'is_active'],
            'blogs'       => ['model' => Blog::class,              'label' => 'title', 'prefix' => '/blog/', 'active' => 'is_published'],
            'blog-categories' => ['model' => BlogCategory::class,  'label' => 'name', 'prefix' => '/blog/category/', 'active' => 'is_active'],
            'faq-categories'  => ['model' => FaqCategory::class,   'label' => 'name', 'prefix' => '/faq/', 'active' => 'is_active'],
        ];

        if ($source === 'all') {
            $groups = [];
            foreach ($sourceMap as $key => $cfg) {
                $query = $cfg['model']::query();
                $activeField = $cfg['active'] ?? 'status';
                if ($activeField === 'status') {
                    $query->where('status', 'active');
                } elseif ($activeField === 'is_published') {
                    $query->where('is_published', true);
                } else {
                    $query->where($activeField, true);
                }
                $groups[$key] = [
                    'label' => ucwords(str_replace('-', ' ', $key)),
                    'count' => $query->count(),
                ];
            }
            return response()->json(['success' => true, 'groups' => $groups]);
        }

        if (!isset($sourceMap[$source])) {
            return response()->json(['success' => false, 'message' => 'Invalid source.'], 422);
        }

        $cfg = $sourceMap[$source];
        $labelField = $cfg['label'];
        $query = $cfg['model']::query();

        $activeField = $cfg['active'] ?? 'status';
        if ($activeField === 'status') {
            $query->where('status', 'active');
        } elseif ($activeField === 'is_published') {
            $query->where('is_published', true);
        } else {
            $query->where($activeField, true);
        }

        if ($search) {
            $query->where($labelField, 'like', "%{$search}%");
        }

        $records = $query->orderBy($labelField)->limit(100)->get();

        foreach ($records as $record) {
            $items[] = [
                'id'    => $record->id,
                'title' => $record->{$labelField},
                'slug'  => $record->slug,
                'url'   => $cfg['prefix'] . $record->slug,
            ];
        }

        return response()->json(['success' => true, 'items' => $items]);
    }

    protected function buildTree($items, $parentId = null): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item->parent_id == $parentId) {
                $node = $item->toArray();
                $node['children'] = $this->buildTree($items, $item->id);
                $tree[] = $node;
            }
        }
        return $tree;
    }
}
