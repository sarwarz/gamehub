<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Seller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TaxController extends Controller
{
    /**
     * List taxes (DataTable)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $taxes = Tax::with('seller:id,store_name');

            return DataTables::of($taxes)
                ->addIndexColumn()

                ->addColumn('scope', function ($row) {
                    return $row->seller
                        ? '<span class="badge bg-info">'.$row->seller->store_name.'</span>'
                        : '<span class="badge bg-primary">Global</span>';
                })

                ->addColumn('location', function ($row) {
                    return collect([$row->country, $row->state, $row->city])
                        ->filter()
                        ->implode(', ') ?: '-';
                })

                ->addColumn('rate_display', function ($row) {
                    return $row->type === 'percent'
                        ? $row->rate . '%'
                        : '$' . number_format($row->rate, 2);
                })

                ->addColumn('status', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';
                })

                ->addColumn('actions', function ($row) {
                    return '
                        <div class="d-flex gap-2">
                            <a href="'.route('taxes.edit', $row->id).'"
                               class="btn btn-sm btn-primary">Edit</a>

                            <button class="btn btn-sm btn-danger btn-delete"
                                data-url="'.route('taxes.destroy', $row->id).'">
                                Delete
                            </button>
                        </div>
                    ';
                })

                ->rawColumns(['scope', 'status', 'actions'])
                ->make(true);
        }

        return view('content.taxes.index');
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('content.taxes.create', [
            'sellers' => Seller::select('id', 'store_name')->orderBy('store_name')->get(),
        ]);
    }

    /**
     * Store tax
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:50',

            'seller_id' => 'nullable|exists:sellers,id',

            'country'   => 'nullable|string|max:2',
            'state'     => 'nullable|string|max:50',
            'city'      => 'nullable|string|max:100',

            'type'      => 'required|in:percent,fixed',
            'rate'      => 'required|numeric|min:0',

            'priority'  => 'nullable|integer|min:1',
            'is_compound' => 'boolean',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active']   = $request->has('is_active');
        $validated['is_compound'] = $request->has('is_compound');

        Tax::create($validated);

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Tax rule created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Tax $tax)
    {
        return view('content.taxes.edit', [
            'tax'     => $tax,
            'sellers' => Seller::select('id', 'store_name')->orderBy('store_name')->get(),
        ]);
    }

    /**
     * Update tax
     */
    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:50',

            'seller_id' => 'nullable|exists:sellers,id',

            'country'   => 'nullable|string|max:2',
            'state'     => 'nullable|string|max:50',
            'city'      => 'nullable|string|max:100',

            'type'      => 'required|in:percent,fixed',
            'rate'      => 'required|numeric|min:0',

            'priority'  => 'nullable|integer|min:1',
            'is_compound' => 'boolean',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active']   = $request->has('is_active');
        $validated['is_compound'] = $request->has('is_compound');

        $tax->update($validated);

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Tax rule updated successfully.');
    }

    /**
     * Delete tax
     */
    public function destroy(Tax $tax)
    {
        $tax->delete();

        return response()->json([
            'message' => 'Tax rule deleted successfully.'
        ]);
    }
}
