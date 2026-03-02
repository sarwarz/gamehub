<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Models\Seller;
use Yajra\DataTables\Facades\DataTables;

class ProductReviewController extends Controller
{
    /**
     * List product reviews (DataTable)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $reviews = ProductReview::query()
                ->with([
                    'product:id,title',
                    'user:id,name,email'
                ]);

            /*
            |--------------------------------------------------------------------------
            | APPLY DYNAMIC FILTERS (Orders / Products Style)
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

                    case 'status':
                        $reviews->where('status', $value);
                        break;

                    case 'rating':
                        $reviews->where('rating', $operator, $value);
                        break;

                    case 'is_verified_purchase':
                        $reviews->where('is_verified_purchase', (bool) $value);
                        break;

                    case 'created_at':
                        $reviews->whereDate('created_at', $value);
                        break;

                    /* ===============================
                    * RELATION FIELDS
                    * =============================== */

                    case 'product_id':
                        $reviews->where('product_id', $value);
                        break;

                    case 'user_id':
                        $reviews->where('user_id', $value);
                        break;

                    case 'product_title':
                        if ($operator === 'like') {
                            $reviews->whereHas('product', function ($q) use ($value) {
                                $q->where('title', 'LIKE', "%{$value}%");
                            });
                        }
                        break;

                    case 'user_name':
                        if ($operator === 'like') {
                            $reviews->whereHas('user', function ($q) use ($value) {
                                $q->where('name', 'LIKE', "%{$value}%");
                            });
                        }
                        break;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | DATATABLE RESPONSE
            |--------------------------------------------------------------------------
            */
            return DataTables::of($reviews)
                ->addIndexColumn()

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox"
                        class="bulk-checkbox form-check-input"
                        value="'.$row->id.'">';
                })


                ->addColumn('review_info', function ($row) {
                    $stars = str_repeat('⭐', (int) $row->rating);

                    return '
                        <strong>'.e($row->product->title).'</strong><br>
                        <small>By: '.e($row->user->name).'</small><br>
                        <span class="text-warning">'.$stars.'</span>
                    ';
                })

                ->addColumn('ip_address', function ($row) {
                    return $row->ip_address ?: '-';
                })

                ->addColumn('verified', function ($row) {
                    return $row->is_verified_purchase
                        ? '<span class="badge bg-success">Verified</span>'
                        : '<span class="badge bg-secondary">Unverified</span>';
                })

                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    ];

                    return '<span class="badge bg-'.$map[$row->status].'">'
                        .ucfirst($row->status).
                    '</span>';
                })

                ->addColumn('actions', function ($row) {
                    $approveBtn = $row->status !== 'approved'
                        ? '<button type="button" class="btn btn-icon btn-sm btn-label-success btn-approve" data-url="'.route('product-reviews.approve', $row->id).'" title="Approve">
                            <i class="ti tabler-check ti-xs"></i>
                        </button>'
                        : '';

                    $rejectBtn = $row->status !== 'rejected'
                        ? '<button type="button" class="btn btn-icon btn-sm btn-label-warning btn-reject" data-url="'.route('product-reviews.reject', $row->id).'" title="Reject">
                            <i class="ti tabler-x ti-xs"></i>
                        </button>'
                        : '';

                    return '<div class="d-flex align-items-center justify-content-center gap-1">
                        <button type="button" class="btn btn-icon btn-sm btn-label-info btn-view" data-url="'.route('product-reviews.show', $row->id).'" title="View">
                            <i class="ti tabler-eye ti-xs"></i>
                        </button>
                        '.$approveBtn.'
                        '.$rejectBtn.'
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger btn-delete" data-url="'.route('product-reviews.destroy', $row->id).'" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>
                    </div>';
                })

                ->rawColumns([
                    'checkbox',
                    'review_info',
                    'verified',
                    'status_badge',
                    'actions'
                ])
                ->make(true);
        }

        /*
        |--------------------------------------------------------------------------
        | NORMAL PAGE LOAD
        |--------------------------------------------------------------------------
        */
        $stats = [
            'total'    => ProductReview::count(),
            'approved' => ProductReview::where('status', 'approved')->count(),
            'pending'  => ProductReview::where('status', 'pending')->count(),
            'rejected' => ProductReview::where('status', 'rejected')->count(),
            'avg'      => round(ProductReview::where('status', 'approved')->avg('rating') ?? 0, 1),
        ];

        return view('content.product_reviews.index', [
            'products' => Product::select('id', 'title')->orderBy('title')->get(),
            'users'    => User::select('id', 'name')->orderBy('name')->get(),
            'stats'    => $stats,
        ]);


    }


    /**
     * Show full review details (AJAX)
     */
    public function show($id)
    {
        $review = ProductReview::with(['product', 'user'])->findOrFail($id);

        return response()->json([
            'product'   => $review->product->title,
            'user'      => $review->user->name,
            'email'     => $review->user->email,
            'rating'    => $review->rating,
            'title'     => $review->title,
            'review'    => $review->review,
            'status'    => $review->status,
            'verified'  => $review->is_verified_purchase,
            'ip'        => $review->ip_address,
            'agent'     => $review->user_agent,
            'created_at' => $review->created_at->toDateTimeString(),
        ]);
    }

    /**
     * Approve review
     */
    public function approve($id)
    {
        ProductReview::findOrFail($id)->update(['status' => 'approved']);

        return response()->json(['message' => 'Review approved successfully']);
    }

    /**
     * Reject review
     */
    public function reject($id)
    {
        ProductReview::findOrFail($id)->update(['status' => 'rejected']);

        return response()->json(['message' => 'Review rejected successfully']);
    }

    /**
     * Delete review
     */
    public function destroy($id)
    {

        ProductReview::findOrFail($id)->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    public function bulkStatus(Request $request)
    {
        $productIds = ProductReview::whereIn('id', $request->ids)->pluck('product_id')->unique();

        ProductReview::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        foreach ($productIds as $pid) {
            Seller::recalculateRatingsForProduct($pid);
        }

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $productIds = ProductReview::whereIn('id', $request->ids)->pluck('product_id')->unique();

        ProductReview::whereIn('id', $request->ids)->delete();

        foreach ($productIds as $pid) {
            Seller::recalculateRatingsForProduct($pid);
        }

        return response()->json(['success' => true]);
    }


}
