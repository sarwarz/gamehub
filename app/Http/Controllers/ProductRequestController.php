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

            $requests = ProductRequest::query()
                ->with([
                    'user',
                    'category',
                    'platform',
                    'type',
                    'region',
                    'language',
                    'worksOn',
                ]);

            /*
            |--------------------------------------------------------------------------
            | Apply Dynamic Filters (Orders / Products Style)
            |--------------------------------------------------------------------------
            */
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
                    * BASIC FIELDS
                    * =============================== */

                    case 'title':
                        if ($operator === 'like') {
                            $requests->where('title', 'LIKE', "%{$value}%");
                        } else {
                            $requests->where('title', $operator, $value);
                        }
                        break;

                    case 'status':
                        $requests->where('status', $value);
                        break;

                    case 'created_at':
                        $requests->whereDate('created_at', $value);
                        break;

                    /* ===============================
                    * RELATION (FK) FIELDS
                    * =============================== */

                    case 'category_id':
                        $requests->where('category_id', $value);
                        break;

                    case 'platform_id':
                        $requests->where('platform_id', $value);
                        break;

                    case 'type_id':
                        $requests->where('type_id', $value);
                        break;

                    case 'region_id':
                        $requests->where('region_id', $value);
                        break;

                    case 'language_id':
                        $requests->where('language_id', $value);
                        break;

                    case 'works_on_id':
                        $requests->where('works_on_id', $value);
                        break;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | DataTable Response
            |--------------------------------------------------------------------------
            */
            return DataTables::of($requests)
                ->addIndexColumn()

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox"
                                class="bulk-checkbox form-check-input"
                                value="'.$row->id.'">';
                })

                ->addColumn('request_info', function ($row) {
                    return '
                        <strong>'.e($row->title).'</strong><br>
                        <small class="text-muted">
                            By: '.e($row->user->name).'
                        </small>
                    ';
                })

                ->addColumn('meta', function ($row) {
                    return implode('<br>', [
                        'Category: '.e($row->category->name),
                        'Platform: '.e($row->platform->name),
                        'Type: '.e($row->type->name),
                        'Region: '.e($row->region->name),
                    ]);
                })

                ->addColumn('source', function ($row) {
                    return $row->source_url
                        ? '<a href="'.e($row->source_url).'"
                            target="_blank"
                            class="text-primary">View</a>'
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
                    return '<div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="'.route('product-requests.edit', $row->id).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                            <i class="ti tabler-pencil ti-xs"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger btn-delete" data-url="'.route('product-requests.destroy', $row->id).'" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>
                    </div>';
                })

                ->rawColumns([
                    'checkbox',
                    'request_info',
                    'meta',
                    'source',
                    'status_badge',
                    'actions'
                ])
                ->make(true);
        }

        /*
        |--------------------------------------------------------------------------
        | Normal Page Load
        |--------------------------------------------------------------------------
        */
        $stats = [
            'total'     => ProductRequest::count(),
            'pending'   => ProductRequest::where('status', 'pending')->count(),
            'approved'  => ProductRequest::where('status', 'approved')->count(),
            'rejected'  => ProductRequest::where('status', 'rejected')->count(),
            'completed' => ProductRequest::where('status', 'completed')->count(),
        ];

        return view('content.product_requests.index', [
            'stats'      => $stats,
            'categories' => ProductCategory::all(),
            'platforms'  => ProductPlatform::all(),
            'types'      => ProductType::all(),
            'regions'    => ProductRegion::all(),
            'languages'  => ProductLanguage::all(),
            'workson'    => ProductWorksOn::all(),
        ]);
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

        $oldStatus = $productRequest->status;

        $productRequest->update($validated);

        if ($oldStatus !== $productRequest->status && in_array($productRequest->status, ['approved', 'rejected', 'completed'])) {
            try {
                if (\App\Models\Setting::get('notifications', 'product_request_status', true)) {
                    $productRequest->load('user');
                    if ($productRequest->user) {
                        $productRequest->user->notify(new \App\Notifications\ProductRequestStatusNotification($productRequest, $productRequest->status));
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Product request notification failed: ' . $e->getMessage());
            }
        }

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

    // BULK STATUS
    public function bulkStatus(Request $request)
    {
        $requests = \App\Models\ProductRequest::with('user')
            ->whereIn('id', $request->ids)
            ->where('status', '!=', $request->status)
            ->get();

        \App\Models\ProductRequest::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        if (in_array($request->status, ['approved', 'rejected', 'completed']) && \App\Models\Setting::get('notifications', 'product_request_status', true)) {
            foreach ($requests as $req) {
                try {
                    if ($req->user) {
                        $req->user->notify(new \App\Notifications\ProductRequestStatusNotification($req, $request->status));
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Product request bulk notification failed: ' . $e->getMessage());
                }
            }
        }

        return response()->json(['success' => true]);
    }

    // BULK DELETE
    public function bulkDelete(Request $request)
    {

        ProductRequest::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true]);
    }

}
