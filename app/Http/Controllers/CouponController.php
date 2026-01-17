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
            return DataTables::of(Coupon::query())
                ->addIndexColumn()

                ->addColumn('discount', function ($c) {
                    return $c->type === 'percent'
                        ? $c->value . '%'
                        : '$' . number_format($c->value, 2);
                })

                ->addColumn('usage', function ($c) {
                    return $c->used . ' / ' . ($c->usage_limit ?? '∞');
                })

                ->addColumn('expiry', function ($c) {
                    return $c->expires_at
                        ? $c->expires_at->format('Y-m-d')
                        : 'No expiry';
                })

                ->addColumn('status', function ($c) {
                    return $c->isActive()
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';
                })

                ->addColumn('actions', function ($c) {
                    return '
                        <div class="d-flex gap-2">
                            <a href="'.route('coupons.edit', $c->id).'"
                               class="btn btn-sm btn-primary">
                                Edit
                            </a>
                            <button class="btn btn-sm btn-danger btn-delete"
                                data-url="'.route('coupons.destroy', $c->id).'">
                                Delete
                            </button>
                        </div>
                    ';
                })

                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('content.coupons.index');
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
            'value' => 'required|numeric|min:0',

            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',

            'include_categories' => 'nullable|array',
            'include_categories.*' => 'exists:product_categories,id',

            'exclude_categories' => 'nullable|array',
            'exclude_categories.*' => 'exists:product_categories,id',

            'include_products' => 'nullable|array',
            'include_products.*' => 'exists:products,id',

            'exclude_products' => 'nullable|array',
            'exclude_products.*' => 'exists:products,id',

            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',

            'starts_at' => 'nullable|date',
            'expires_at'=> 'nullable|date|after_or_equal:starts_at',

            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

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
            'value' => 'required|numeric|min:0',

            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',

            'include_categories' => 'nullable|array',
            'include_categories.*' => 'exists:product_categories,id',

            'exclude_categories' => 'nullable|array',
            'exclude_categories.*' => 'exists:product_categories,id',

            'include_products' => 'nullable|array',
            'include_products.*' => 'exists:products,id',

            'exclude_products' => 'nullable|array',
            'exclude_products.*' => 'exists:products,id',

            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',

            'starts_at' => 'nullable|date',
            'expires_at'=> 'nullable|date|after_or_equal:starts_at',

            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $coupon->update($validated);

        return redirect()
            ->route('coupons.index')
            ->with('success', 'Coupon updated successfully.');
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
