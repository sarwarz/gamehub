<?php

namespace App\Http\Controllers;

use App\Models\ProductRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductRegionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $regions = ProductRegion::query();

            return DataTables::of($regions)
                ->addIndexColumn()
                ->addColumn('checkbox', fn($row) =>
                    '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">'
                )
                ->addColumn('status_badge', fn($row) =>
                    '<span class="badge bg-'.($row->status === 'active' ? 'success' : 'secondary').'">'
                    .ucfirst($row->status).'</span>'
                )
                ->addColumn('actions', function ($row) {
                    $editUrl   = route('regions.edit', $row->id);
                    $deleteUrl = route('regions.destroy', $row->id);

                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="'.$deleteUrl.'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox','status_badge','actions'])
                ->make(true);
        }

        return view('content.products.regions.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_regions,slug',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            ProductRegion::create($validated);
            return redirect()->route('regions.index')->with('success', 'Product Region created successfully.');
        } catch (\Exception $e) {
            Log::error('Product Region create failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create product region. Please try again.');
        }
    }

    public function edit($id)
    {
        $region = ProductRegion::findOrFail($id);
        return view('content.products.regions.edit', compact('region'));
    }

    public function update(Request $request, $id)
    {
        $region = ProductRegion::findOrFail($id);

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_regions,slug,'.$region->id,
            'status' => 'required|in:active,inactive',
        ]);

        $region->update($validated);

        return redirect()->route('regions.index')->with('success', 'Product Region updated successfully.');
    }

    public function destroy($id)
    {
        $region = ProductRegion::findOrFail($id);
        $region->delete();

        return response()->json(['message' => 'Product Region deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No product regions selected'], 400);
        }

        ProductRegion::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Selected product regions deleted successfully']);
    }
}
