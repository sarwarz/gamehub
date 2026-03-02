<?php

namespace App\Http\Controllers;

use App\Models\ProductWorksOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductWorksOnController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $worksOn = ProductWorksOn::query();

            return DataTables::of($worksOn)
                ->addIndexColumn()
                ->addColumn('checkbox', fn($row) =>
                    '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">'
                )
                ->addColumn('status_badge', fn($row) =>
                    '<span class="badge bg-'.($row->status === 'active' ? 'success' : 'secondary').'">'
                    .ucfirst($row->status).'</span>'
                )
                ->addColumn('actions', function ($row) {
                    $editUrl = route('workson.edit', $row->id);
                    $deleteUrl = route('workson.destroy', $row->id);
                    return '
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <a href="'.$editUrl.'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                                <i class="ti tabler-pencil ti-xs"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn"
                                    data-url="'.$deleteUrl.'" title="Delete">
                                <i class="ti tabler-trash ti-xs"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['checkbox','status_badge','actions'])
                ->make(true);
        }

        $stats = ['total' => ProductWorksOn::count()];

        return view('content.products.workson.index', compact('stats'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_works_on,slug',
            'icon'   => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            ProductWorksOn::create($validated);
            return redirect()->route('workson.index')->with('success', 'Work On option created successfully.');
        } catch (\Exception $e) {
            Log::error('Work On create failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Work On option. Please try again.');
        }
    }

    public function edit($id)
    {
        $workOn = ProductWorksOn::findOrFail($id);
        return view('content.products.workson.edit', compact('workOn'));
    }

    public function update(Request $request, $id)
    {

        $workOn = ProductWorksOn::findOrFail($id);

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_works_on,slug,'.$workOn->id,
            'icon'   => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $workOn->update($validated);

        return redirect()->route('workson.index')->with('success', 'Work On option updated successfully.');
    }

    public function destroy($id)
    {

        $workOn = ProductWorksOn::findOrFail($id);
        $workOn->delete();

        return response()->json(['message' => 'Work On option deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No options selected'], 400);
        }

        ProductWorksOn::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Selected options deleted successfully']);
    }
}
