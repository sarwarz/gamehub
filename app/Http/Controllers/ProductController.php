<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SellerOffer;
use Illuminate\Support\Str;
use App\Models\ProductLabel;
use Illuminate\Http\Request;
use App\Models\ProductRegion;
use App\Models\ProductWorksOn;
use App\Services\MediaService;
use App\Models\ProductCategory;
use App\Models\ProductLanguage;
use App\Models\ProductPlatform;
use App\Models\ProductDeveloper;
use App\Models\ProductPublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{

    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $products = Product::query()
                ->with([
                    'categories:id,name',
                    'platforms:id,name',
                    'types:id,name,commission',
                    'regions:id,name',
                    'languages:id,name',
                    'worksOn:id,name',
                    'developer:id,name',
                    'publisher:id,name',
                    'media:id,mediable_id,mediable_type,disk,directory,filename,is_primary',
                    'primaryImage:id,mediable_id,mediable_type,disk,directory,filename,is_primary',
                ]);

            /* ===============================
            | APPLY DYNAMIC FILTERS
            =============================== */
            foreach ($request->filters ?? [] as $filter) {

                if (
                    empty($filter['field']) ||
                    !array_key_exists('value', $filter) ||
                    $filter['value'] === ''
                ) {
                    continue;
                }

                $field    = $filter['field'];
                $operator = $filter['operator'] ?? '=';
                $value    = $filter['value'];

                switch ($field) {

                    /* ===============================
                    | BASIC FIELDS
                    =============================== */
                    case 'title':
                    case 'sku':
                        if ($operator === 'like') {
                            $products->where($field, 'LIKE', "%{$value}%");
                        } else {
                            $products->where($field, $operator, $value);
                        }
                        break;

                    case 'status':
                        $products->where('status', $value);
                        break;

                    case 'is_featured':
                        $products->where('is_featured', (bool) $value);
                        break;

                    case 'created_at':
                        $products->whereDate('created_at', $value);
                        break;

                    /* ===============================
                    | RELATION FILTERS
                    =============================== */
                    case 'category_id':
                        $products->whereHas('categories', fn ($q) =>
                            $q->where('product_categories.id', $value)
                        );
                        break;

                    case 'platform_id':
                        $products->whereHas('platforms', fn ($q) =>
                            $q->where('product_platforms.id', $value)
                        );
                        break;

                    case 'type_id':
                        $products->whereHas('types', fn ($q) =>
                            $q->where('product_types.id', $value)
                        );
                        break;

                    case 'region_id':
                        $products->whereHas('regions', fn ($q) =>
                            $q->where('product_regions.id', $value)
                        );
                        break;

                    case 'language_id':
                        $products->whereHas('languages', fn ($q) =>
                            $q->where('product_languages.id', $value)
                        );
                        break;

                    case 'works_on_id':
                        $products->whereHas('worksOn', fn ($q) =>
                            $q->where('product_works_on.id', $value)
                        );
                        break;

                    case 'developer_id':
                        $products->where('developer_id', $value);
                        break;

                    case 'publisher_id':
                        $products->where('publisher_id', $value);
                        break;
                }
            }

            return $this->productDataTable($products);
        }

        return view('content.products.index', [
            'categories' => ProductCategory::where('status', 'active')->get(),
            'platforms'  => ProductPlatform::where('status', 'active')->get(),
            'types'      => ProductType::where('status', 'active')->get(),
            'regions'    => ProductRegion::where('status', 'active')->get(),
            'languages'  => ProductLanguage::where('status', 'active')->get(),
            'worksOn'    => ProductWorksOn::where('status', 'active')->get(),
            'developers' => ProductDeveloper::where('status', 'active')->get(),
            'publishers' => ProductPublisher::where('status', 'active')->get(),
        ]);
    }





    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('content.products.create', [
            'categories' => ProductCategory::all(),
            'platforms'  => ProductPlatform::all(),
            'types'      => ProductType::all(),
            'regions'    => ProductRegion::all(),
            'languages'  => ProductLanguage::all(),
            'workson'    => ProductWorksOn::all(),
            'developers' => ProductDeveloper::all(),
            'publishers' => ProductPublisher::all(),
            'labels'     => ProductLabel::all(),
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request, MediaService $mediaService)
    {
        //  Validate
         $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug',
            'sku'         => 'nullable|string|max:255|unique:products,sku',
            'description' => 'nullable|string',

            // Many-to-many
            'category_ids'   => 'nullable|array',
            'category_ids.*' => 'exists:product_categories,id',
            'platform_ids'   => 'nullable|array',
            'platform_ids.*' => 'exists:product_platforms,id',
            'type_ids'   => 'nullable|array',
            'type_ids.*' => 'exists:product_types,id',
            'region_ids'   => 'nullable|array',
            'region_ids.*' => 'exists:product_regions,id',
            'language_ids'   => 'nullable|array',
            'language_ids.*' => 'exists:product_languages,id',
            'works_on_ids'   => 'nullable|array',
            'works_on_ids.*' => 'exists:product_works_on,id',

            'developer_id'=> 'nullable|exists:product_developers,id',
            'publisher_id'=> 'nullable|exists:product_publishers,id',
            'label_id'    => 'nullable|exists:product_labels,id',

            // Media
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'gallery.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'delivery_type' => 'required|in:instant,manual,email,link',
            'status'        => 'required|in:active,inactive',
            'is_featured'   => 'boolean',
            'sort_order'    => 'integer',

            'attributes'               => 'nullable|array',
            'attributes.*.key'         => 'nullable|string|max:255',
            'attributes.*.value'       => 'nullable|string|max:255',

            'system_requirements'              => 'nullable|array',
            'system_requirements.minimum'      => 'nullable|array',
            'system_requirements.minimum.*.key'=> 'nullable|string|max:255',
            'system_requirements.minimum.*.value'=> 'nullable|string|max:255',
            'system_requirements.recommended'  => 'nullable|array',
            'system_requirements.extra'        => 'nullable|array',

            'meta_title'       => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords'    => 'nullable|string|max:255',

        ]);

        // Clean attributes
        if (!empty($validated['attributes'])) {
            $validated['attributes'] = collect($validated['attributes'])
                ->filter(fn($attr) => !empty($attr['key']) && !empty($attr['value']))
                ->values()
                ->toArray();
        }

        // Clean system requirements
        if (!empty($validated['system_requirements'])) {
            $validated['system_requirements'] = collect($validated['system_requirements'])
                ->map(function ($group) {
                    return collect($group)
                        ->filter(fn($item) => !empty($item['key']) && !empty($item['value']))
                        ->values()
                        ->toArray();
                })
                ->toArray();
        }

        // Handle slug
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }


        // Save product
        $product = Product::create($validated);

         /* ============================
        | Cover Image (PRIMARY)
        ============================ */
        if ($request->hasFile('cover_image')) {
            $mediaService->upload(
                $request->file('cover_image'),
                $product,
                'uploads/products/cover',
                true // is primary
            );
        }


       /* ============================
        | Gallery Images
        ============================ */
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $index => $file) {
                $media = $mediaService->upload(
                    $file,
                    $product,
                    'uploads/products/gallery'
                );

                // set sort order
                $media->update(['sort_order' => $index + 1]);
            }
        }




        

        // Sync many-to-many
        $product->categories()->sync($request->input('category_ids', []));
        $product->platforms()->sync($request->input('platform_ids', []));
        $product->types()->sync($request->input('type_ids', []));
        $product->regions()->sync($request->input('region_ids', []));
        $product->languages()->sync($request->input('language_ids', []));
        $product->worksOn()->sync($request->input('works_on_ids', []));


        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }




    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        $product = Product::with([
            'primaryImage',
            'galleryImages',
            'categories:id,name',
            'platforms:id,name',
            'types:id,name',
            'regions:id,name',
            'languages:id,name',
            'worksOn:id,name',
            'developer:id,name',
            'publisher:id,name',
        ])->findOrFail($id);

        return view('content.products.edit', [
            'product'    => $product,
            'categories' => ProductCategory::all(),
            'platforms'  => ProductPlatform::all(),
            'types'      => ProductType::all(),
            'regions'    => ProductRegion::all(),
            'languages'  => ProductLanguage::all(),
            'workson'    => ProductWorksOn::all(),
            'developers' => ProductDeveloper::all(),
            'publishers' => ProductPublisher::all(),
            'labels'     => ProductLabel::all(),
        ]);
    }



    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // 1. Validate (let Laravel handle validation exceptions automatically)
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'sku'         => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',

            // Many-to-many
            'category_ids'   => 'nullable|array',
            'category_ids.*' => 'exists:product_categories,id',
            'platform_ids'   => 'nullable|array',
            'platform_ids.*' => 'exists:product_platforms,id',
            'type_ids'       => 'nullable|array',
            'type_ids.*'     => 'exists:product_types,id',
            'region_ids'     => 'nullable|array',
            'region_ids.*'   => 'exists:product_regions,id',
            'language_ids'   => 'nullable|array',
            'language_ids.*' => 'exists:product_languages,id',
            'works_on_ids'   => 'nullable|array',
            'works_on_ids.*' => 'exists:product_works_on,id',

            // Single relations
            'developer_id' => 'nullable|exists:product_developers,id',
            'publisher_id' => 'nullable|exists:product_publishers,id',
            'label_id'     => 'nullable|exists:product_labels,id',

            // Media
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'gallery.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // JSON fields
            'attributes'          => 'nullable|array',
            'system_requirements' => 'nullable|array',

            'delivery_type' => 'required|in:instant,manual,email,link',
            'status'        => 'required|in:active,inactive',
            'is_featured'   => 'boolean',
            'sort_order'    => 'integer',

            'meta_title'       => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords'    => 'nullable|string|max:255',
        ]);

        try {
            // Clean attributes
            if (!empty($validated['attributes'])) {
                $validated['attributes'] = collect($validated['attributes'])
                    ->filter(fn($attr) => !empty($attr['key']) && !empty($attr['value']))
                    ->values()
                    ->toArray();
            }

            // Clean system requirements
            if (!empty($validated['system_requirements'])) {
                $validated['system_requirements'] = collect($validated['system_requirements'])
                    ->map(fn($group) => collect($group)
                        ->filter(fn($item) => !empty($item['key']) && !empty($item['value']))
                        ->values()
                        ->toArray()
                    )
                    ->toArray();
            }

            // Slug
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);
            }


            // Update product
            $product->update($validated);

            // Cover image
           if ($request->hasFile('cover_image')) {

                // delete old primary image
                $product->media()->where('is_primary', true)->each(function ($media) {
                    Storage::disk($media->disk)->delete($media->path);
                    $media->delete();
                });

                $file = $request->file('cover_image');
                $path = $file->store('products/cover', 'public');

                $product->media()->create([
                    'disk'       => 'public',
                    'directory'  => dirname($path),
                    'filename'   => basename($path),
                    'type'       => 'image',
                    'is_primary' => true,
                ]);
            }



            // Gallery
            // ============================
            // Gallery (Media-based)
            // ============================
            if ($request->hasFile('gallery')) {

                // 1️⃣ Delete existing gallery media (non-primary images)
                $product->media()
                    ->where('type', 'image')
                    ->where('is_primary', false)
                    ->each(function ($media) {
                        Storage::disk($media->disk)->delete($media->path);
                        $media->delete();
                    });

                // 2️⃣ Upload new gallery images
                foreach ($request->file('gallery') as $index => $file) {

                    $path = $file->store('products/gallery', 'public');

                    $product->media()->create([
                        'disk'          => 'public',
                        'directory'     => dirname($path),
                        'filename'      => basename($path),
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getMimeType(),
                        'extension'     => $file->getClientOriginalExtension(),
                        'size'          => $file->getSize(),
                        'type'          => 'image',
                        'is_primary'    => false,
                        'sort_order'    => $index + 1,
                    ]);
                }
            }



            

            // Sync many-to-many
            $product->categories()->sync($request->input('category_ids', []));
            $product->platforms()->sync($request->input('platform_ids', []));
            $product->types()->sync($request->input('type_ids', []));
            $product->regions()->sync($request->input('region_ids', []));
            $product->languages()->sync($request->input('language_ids', []));
            $product->worksOn()->sync($request->input('works_on_ids', []));

            return redirect()->route('products.index')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Product update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update product. Please try again.');
        }
    }


    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            DB::transaction(function () use ($product) {

                // detach relations
                $product->categories()->detach();
                $product->platforms()->detach();
                $product->types()->detach();
                $product->regions()->detach();
                $product->languages()->detach();
                $product->worksOn()->detach();

                // delete media files + records
                $product->media->each(function ($media) {
                    Storage::disk($media->disk)->delete($media->path);
                    $media->delete();
                });

                $product->delete();
            });

            return response()->json(['message' => 'Product deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Product delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete product.'], 500);
        }
    }


    public function bulkStatus(Request $request)
    {
        Product::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }

    public function bulkFeatured(Request $request)
    {
        Product::whereIn('id', $request->ids)
            ->update(['is_featured' => (bool) $request->value]);

        return response()->json(['success' => true]);
    }

    /**
     * Bulk Delete products.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No products selected'], 400);
        }

        try {
            DB::transaction(function () use ($ids) {
                $products = Product::whereIn('id', $ids)->get();

                foreach ($products as $product) {
                    // Detach many-to-many
                    $product->categories()->detach();
                    $product->platforms()->detach();
                    $product->types()->detach();
                    $product->regions()->detach();
                    $product->languages()->detach();
                    $product->worksOn()->detach();

                    $product->media->each(function ($media) {
                        Storage::disk($media->disk)->delete($media->path);
                        $media->delete();
                    });

                    $product->delete();
                }
            });

            return response()->json(['message' => 'Selected products deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Bulk product delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete products'], 500);
        }
    }

    public function preview($id)
    {
        $product = Product::with(['regions', 'languages', 'platforms','types'])
            ->findOrFail($id);

        return response()->json([
            'id'       => $product->id,
            'title'    => $product->title,
            'cover'    => $product->primaryImage?->url ?? asset('assets/img/default-product.png'),
            'types'  => $product->types->pluck('name')->toArray(),
            'regions'  => $product->regions->pluck('name')->toArray(),
            'languages'=> $product->languages->pluck('name')->toArray(),
            'platforms'=> $product->platforms->pluck('name')->toArray(),
            'commission' => optional($product->types->first())->commission ?? 0,
        ]);
    }

    public function offers($productId){
        $product = Product::with('types')->findOrFail($productId);
        $commissionRate = $product->types->max('commission') ?? 0.00;

        $offers = $product->offers()
            ->with('seller:id,store_name')
            ->get()
            ->map(function($offer) use ($commissionRate) {
                return [
                    'id'          => $offer->id,
                    'seller'      => $offer->seller->store_name,
                    'retail_price'=> $offer->retail_price,
                    'commission'  => $commissionRate,
                ];
            });

        return response()->json($offers);
    }



    private function productDataTable($products)
    {
        return DataTables::of($products)
            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">';
            })

            ->addColumn('product_column', function ($row) {
                $image = $row->primaryImage
                    ? $row->primaryImage->url
                    : asset('assets/img/default-product.png');

                $title = e($row->title);
                $developer = $row->developer?->name ?? 'Unknown Dev';
                $publisher = $row->publisher?->name ?? 'Unknown Pub';

                return '
                    <div class="d-flex align-items-center">
                        <img src="'.$image.'" class="rounded me-2" width="40" height="40">
                        <div>
                            <strong>'.$title.'</strong><br>
                            <small class="badge bg-label-primary">Dev: '.$developer.'</small>
                            <small class="badge bg-label-info">Pub: '.$publisher.'</small>
                        </div>
                    </div>
                ';
            })

            ->addColumn('categories', fn($row) =>
                $row->categories->pluck('name')->implode(', ') ?: '-'
            )

            ->addColumn('types', fn($row) =>
                $row->types->pluck('name')->implode(', ') ?: '-'
            )

            ->addColumn('regions', fn($row) =>
                $row->regions->pluck('name')->implode(', ') ?: '-'
            )

            ->addColumn('status_badge', function ($row) {
                $map = [
                    'active'   => 'success',
                    'inactive' => 'secondary',
                    'draft'    => 'warning',
                    'archived' => 'dark',
                ];
                return '<span class="badge bg-'.$map[$row->status].'">'.ucfirst($row->status).'</span>';
            })

            ->addColumn('actions', function ($row) {
                return '
                    <a href="'.route('products.edit', $row->id).'" class="btn btn-sm btn-warning">Edit</a>
                    <button class="btn btn-sm btn-danger btn-delete"
                        data-url="'.route('products.destroy', $row->id).'">
                        Delete
                    </button>
                ';
            })

            ->rawColumns(['checkbox', 'product_column', 'status_badge', 'actions'])
            ->make(true);
    }

   





}
