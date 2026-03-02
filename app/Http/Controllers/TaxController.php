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

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$row->id.'">';
                })

                ->addColumn('name_col', function ($row) {
                    return '<span class="fw-semibold">'.e($row->name).'</span>'
                         . ($row->code ? '<div class="text-muted small">Code: '.e($row->code).'</div>' : '');
                })

                ->addColumn('scope', function ($row) {
                    return $row->seller
                        ? '<span class="badge bg-label-info"><i class="ti tabler-building-store ti-xs me-1"></i>'.e($row->seller->store_name).'</span>'
                        : '<span class="badge bg-label-primary"><i class="ti tabler-world ti-xs me-1"></i>Global</span>';
                })

                ->addColumn('location', function ($row) {
                    $loc = collect([$row->country, $row->state, $row->city])->filter()->implode(', ');
                    return $loc ? '<span class="small">'.$loc.'</span>' : '<span class="text-muted">—</span>';
                })

                ->addColumn('rate_display', function ($row) {
                    return $row->type === 'percent'
                        ? '<span class="fw-semibold">'.$row->rate.'%</span>'
                        : '<span class="fw-semibold">$'.number_format($row->rate, 2).'</span>';
                })

                ->addColumn('flags', function ($row) {
                    $html = '';
                    if ($row->is_compound) $html .= '<span class="badge bg-label-warning me-1" style="font-size:.65rem">Compound</span>';
                    $html .= '<span class="small text-muted">Priority: '.$row->priority.'</span>';
                    return $html;
                })

                ->addColumn('status', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-label-success">Active</span>'
                        : '<span class="badge bg-label-secondary">Inactive</span>';
                })

                ->addColumn('actions', fn ($row) =>
                    '<div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="'.route('taxes.edit', $row->id).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                            <i class="ti tabler-pencil ti-xs"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-url="'.route('taxes.destroy', $row->id).'" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>
                    </div>'
                )

                ->rawColumns(['checkbox', 'name_col', 'scope', 'location', 'rate_display', 'flags', 'status', 'actions'])
                ->make(true);
        }

        $stats = [
            'total'  => Tax::count(),
            'active' => Tax::where('is_active', true)->count(),
        ];

        return view('content.taxes.index', compact('stats'));
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

    /**
     * Bulk delete tax rules.
     */
    public function bulkDelete(Request $request)
    {

        $ids = $request->ids;
        Tax::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Selected tax rules deleted successfully.']);
    }
}
