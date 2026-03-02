<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Yajra\DataTables\Facades\DataTables;

class CouponController extends Controller
{
    /**
     * List coupons (DataTable)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Coupon::with('seller.user');

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($request->status === 'expired') {
                    $query->where('expires_at', '<', now());
                }
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('scope')) {
                if ($request->scope === 'global') {
                    $query->whereNull('seller_id');
                } elseif ($request->scope === 'seller') {
                    $query->whereNotNull('seller_id');
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('checkbox', fn ($c) =>
                    '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$c->id.'">'
                )

                ->addColumn('code_col', function ($c) {
                    $badge = '';
                    if ($c->seller_id) {
                        $sellerName = $c->seller?->user?->name ?? 'Seller #'.$c->seller_id;
                        $badge = '<br><span class="badge bg-label-warning mt-1"><i class="ti tabler-building-store ti-xs me-1"></i>'.e($sellerName).'</span>';
                    }
                    return '<code class="fw-bold">'.e($c->code).'</code>'.$badge;
                })

                ->addColumn('discount', function ($c) {
                    $val = $c->type === 'percent'
                        ? '<span class="fw-semibold text-primary">'.$c->value.'%</span> <small class="text-muted">off</small>'
                        : '<span class="fw-semibold text-primary">$'.number_format($c->value, 2).'</span> <small class="text-muted">flat</small>';
                    if ($c->max_discount_amount) {
                        $val .= '<br><small class="text-muted">cap: $'.number_format($c->max_discount_amount, 2).'</small>';
                    }
                    return $val;
                })

                ->addColumn('usage', function ($c) {
                    $limit = $c->usage_limit ?? '∞';
                    $pct = $c->usage_limit ? round(($c->used / $c->usage_limit) * 100) : 0;
                    return '<span class="fw-semibold">'.$c->used.'</span> / '.$limit
                         . ($c->usage_limit ? '<div class="progress mt-1" style="height:4px"><div class="progress-bar" style="width:'.$pct.'%"></div></div>' : '');
                })

                ->addColumn('restrictions', function ($c) {
                    $parts = [];
                    if ($c->min_order_amount) $parts[] = 'Min: $'.number_format($c->min_order_amount, 2);
                    if ($c->max_order_amount) $parts[] = 'Max: $'.number_format($c->max_order_amount, 2);
                    return $parts ? '<span class="small text-muted">'.implode(' · ', $parts).'</span>' : '<span class="text-muted">—</span>';
                })

                ->addColumn('expiry', function ($c) {
                    if (!$c->expires_at) return '<span class="text-muted small">No expiry</span>';
                    $isExpired = $c->expires_at->isPast();
                    return '<span class="small '.($isExpired ? 'text-danger' : '').'">'.$c->expires_at->format('M d, Y').'</span>';
                })

                ->addColumn('status', function ($c) {
                    return $c->isActive()
                        ? '<span class="badge bg-label-success">Active</span>'
                        : '<span class="badge bg-label-secondary">Inactive</span>';
                })

                ->addColumn('actions', fn ($c) =>
                    '<div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="'.route('coupons.edit', $c->id).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                            <i class="ti tabler-pencil ti-xs"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-url="'.route('coupons.destroy', $c->id).'" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>
                    </div>'
                )

                ->rawColumns(['checkbox', 'code_col', 'discount', 'usage', 'restrictions', 'expiry', 'status', 'actions'])
                ->make(true);
        }

        $stats = [
            'total'   => Coupon::count(),
            'active'  => Coupon::where('is_active', true)->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->count(),
            'expired' => Coupon::where('expires_at', '<', now())->count(),
            'seller'  => Coupon::whereNotNull('seller_id')->count(),
        ];

        return view('content.coupons.index', compact('stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('content.coupons.create', [
            'categories' => ProductCategory::orderBy('name')->get(),
            'products'   => Product::select('id', 'title')->orderBy('title')->get(),
        ]);
    }

    /**
     * Store coupon
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'  => 'required|string|max:50|unique:coupons,code',
            'type'  => 'required|in:fixed,percent',
            'value' => ['required', 'numeric', 'min:0', function ($attr, $value, $fail) use ($request) {
                if ($request->type === 'percent' && $value > 100) {
                    $fail('Percent discount cannot exceed 100.');
                }
            }],
            'max_discount_amount' => 'nullable|numeric|min:0.01',
            'description'         => 'nullable|string|max:255',

            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',

            'include_categories'   => 'nullable|array',
            'include_categories.*' => 'exists:product_categories,id',
            'exclude_categories'   => 'nullable|array',
            'exclude_categories.*' => 'exists:product_categories,id',
            'include_products'     => 'nullable|array',
            'include_products.*'   => 'exists:products,id',
            'exclude_products'     => 'nullable|array',
            'exclude_products.*'   => 'exists:products,id',

            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',

            'starts_at'  => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',

            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['code'] = strtoupper($validated['code']);

        Coupon::create($validated);

        return redirect()
            ->route('coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Coupon $coupon)
    {
        $coupon->load('seller.user');

        return view('content.coupons.edit', [
            'coupon'     => $coupon,
            'categories' => ProductCategory::orderBy('name')->get(),
            'products'   => Product::select('id', 'title')->orderBy('title')->get(),
        ]);
    }

    /**
     * Update coupon
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code'  => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'type'  => 'required|in:fixed,percent',
            'value' => ['required', 'numeric', 'min:0', function ($attr, $value, $fail) use ($request) {
                if ($request->type === 'percent' && $value > 100) {
                    $fail('Percent discount cannot exceed 100.');
                }
            }],
            'max_discount_amount' => 'nullable|numeric|min:0.01',
            'description'         => 'nullable|string|max:255',

            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',

            'include_categories'   => 'nullable|array',
            'include_categories.*' => 'exists:product_categories,id',
            'exclude_categories'   => 'nullable|array',
            'exclude_categories.*' => 'exists:product_categories,id',
            'include_products'     => 'nullable|array',
            'include_products.*'   => 'exists:products,id',
            'exclude_products'     => 'nullable|array',
            'exclude_products.*'   => 'exists:products,id',

            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',

            'starts_at'  => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',

            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['code'] = strtoupper($validated['code']);

        $coupon->update($validated);

        return redirect()
            ->route('coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    /**
     * Bulk delete coupons
     */
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:coupons,id']);
        Coupon::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Coupons deleted successfully.']);
    }

    /**
     * Delete coupon
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json([
            'message' => 'Coupon deleted successfully.'
        ]);
    }
}
