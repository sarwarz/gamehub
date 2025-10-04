<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $types = ProductType::query();

            return DataTables::of($types)
                ->addIndexColumn()
                ->addColumn('checkbox', fn($row) =>
                    '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">'
                )
                ->addColumn('commission', fn($row) =>
                    '<span class="badge bg-info">'.number_format($row->commission, 2).'%</span>'
                )
                ->addColumn('status_badge', fn($row) =>
                    '<span class="badge bg-'.($row->status === 'active' ? 'success' : 'secondary').'">'
                    .ucfirst($row->status).'</span>'
                )
                ->addColumn('actions', function ($row) {
                    $editUrl   = route('types.edit', $row->id);
                    $deleteUrl = route('types.destroy', $row->id);

                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="'.$deleteUrl.'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox','commission','status_badge','actions'])
                ->make(true);
        }

        return view('content.products.types.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:product_types,slug',
            'commission' => 'required|numeric|min:0|max:100',
            'status'     => 'required|in:active,inactive',
        ]);

        try {
            ProductType::create($validated);
            return redirect()->route('types.index')->with('success', 'Product Type created successfully.');
        } catch (\Exception $e) {
            Log::error('Product Type create failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create product type. Please try again.');
        }
    }

    public function edit($id)
    {
        $type = ProductType::findOrFail($id);
        return view('content.products.types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = ProductType::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:product_types,slug,'.$type->id,
            'commission' => 'required|numeric|min:0|max:100',
            'status'     => 'required|in:active,inactive',
        ]);

        $type->update($validated);

        return redirect()->route('types.index')->with('success', 'Product Type updated successfully.');
    }

    public function destroy($id)
    {
        $type = ProductType::findOrFail($id);
        $type->delete();

        return response()->json(['message' => 'Product Type deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No product types selected'], 400);
        }

        ProductType::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Selected product types deleted successfully']);
    }
}
