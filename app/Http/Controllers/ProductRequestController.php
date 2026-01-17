<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Models\ProductRegion;
use App\Models\ProductRequest;
use App\Models\ProductWorksOn;
use App\Models\ProductCategory;
use App\Models\ProductLanguage;
use App\Models\ProductPlatform;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class ProductRequestController extends Controller
{
    /**
     * List product requests (DataTable)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $requests = ProductRequest::with([
                'user',
                'category',
                'platform',
                'type',
                'region',
                'language',
                'worksOn',
            ]);

            return DataTables::of($requests)
                ->addIndexColumn()

                ->addColumn('request_info', function ($row) {
                    return '
                        <strong>'.$row->title.'</strong><br>
                        <small class="text-muted">
                            By: '.$row->user->name.'
                        </small>
                    ';
                })

                ->addColumn('meta', function ($row) {
                    return implode('<br>', [
                        'Category: '.$row->category->name,
                        'Platform: '.$row->platform->name,
                        'Type: '.$row->type->name,
                        'Region: '.$row->region->name,
                    ]);
                })

                ->addColumn('source', function ($row) {
                    return $row->source_url
                        ? '<a href="'.$row->source_url.'" target="_blank" class="text-primary">View</a>'
                        : '-';
                })

                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'pending'   => 'warning',
                        'approved'  => 'success',
                        'rejected'  => 'danger',
                        'completed' => 'primary',
                    ];

                    return '<span class="badge bg-'.$map[$row->status].'">'
                        .ucfirst($row->status).
                    '</span>';
                })

                ->addColumn('actions', function ($row) {
                    return '
                        <a href="'.route('product-requests.edit', $row->id).'"
                           class="btn btn-sm btn-warning">Edit</a>

                        <button class="btn btn-sm btn-danger btn-delete"
                            data-url="'.route('product-requests.destroy', $row->id).'">
                            Delete
                        </button>
                    ';
                })

                ->rawColumns([
                    'request_info',
                    'meta',
                    'source',
                    'status_badge',
                    'actions'
                ])
                ->make(true);
        }

        return view('content.product_requests.index');
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('content.product_requests.create', [
            'categories' => ProductCategory::all(),
            'platforms'  => ProductPlatform::all(),
            'types'      => ProductType::all(),
            'regions'    => ProductRegion::all(),
            'languages'  => ProductLanguage::all(),
            'workson'    => ProductWorksOn::all(),
        ]);
    }

    /**
     * Store new request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'source_url'   => 'nullable|url|max:255',
            'status'       => 'required|in:pending,approved,rejected,completed',

            'category_id'  => 'required|exists:product_categories,id',
            'platform_id'  => 'required|exists:product_platforms,id',
            'type_id'      => 'required|exists:product_types,id',
            'region_id'    => 'required|exists:product_regions,id',
            'language_id'  => 'required|exists:product_languages,id',
            'works_on_id'  => 'required|exists:product_works_on,id',
        ]);

        $validated['user_id'] = auth()->id();

        ProductRequest::create($validated);

        return redirect()
            ->route('product-requests.index')
            ->with('success', 'Product request created successfully.');
    }

    /**
     * Edit request
     */
    public function edit($id)
    {
        $request = ProductRequest::findOrFail($id);

        return view('content.product_requests.edit', [
            'request'    => $request,
            'categories' => ProductCategory::all(),
            'platforms'  => ProductPlatform::all(),
            'types'      => ProductType::all(),
            'regions'    => ProductRegion::all(),
            'languages'  => ProductLanguage::all(),
            'workson'    => ProductWorksOn::all(),
        ]);
    }

    /**
     * Update request
     */
    public function update(Request $request, $id)
    {
        $productRequest = ProductRequest::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'source_url'   => 'nullable|url|max:255',
            'status'       => 'required|in:pending,approved,rejected,completed',
        ]);

        $productRequest->update($validated);

        return redirect()
            ->route('product-requests.index')
            ->with('success', 'Product request updated successfully.');
    }

    /**
     * Delete request
     */
    public function destroy($id)
    {
        ProductRequest::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Product request deleted successfully'
        ]);
    }
}
