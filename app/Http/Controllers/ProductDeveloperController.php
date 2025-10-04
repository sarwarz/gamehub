<?php

namespace App\Http\Controllers;

use App\Models\ProductDeveloper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductDeveloperController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $developers = ProductDeveloper::query();

            return DataTables::of($developers)
                ->addIndexColumn()
                ->addColumn('checkbox', fn($row) =>
                    '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">'
                )
                ->addColumn('status_badge', fn($row) =>
                    '<span class="badge bg-'.($row->status === 'active' ? 'success' : 'secondary').'">'
                    .ucfirst($row->status).'</span>'
                )
                ->addColumn('actions', function ($row) {
                    $editUrl   = route('developers.edit', $row->id);
                    $deleteUrl = route('developers.destroy', $row->id);

                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="'.$deleteUrl.'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox','status_badge','actions'])
                ->make(true);
        }

        return view('content.products.developers.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_developers,slug',
            'website'=> 'nullable|url|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            ProductDeveloper::create($validated);
            return redirect()->route('developers.index')->with('success', 'Developer created successfully.');
        } catch (\Exception $e) {
            Log::error('Developer create failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create developer. Please try again.');
        }
    }

    public function edit($id)
    {
        $developer = ProductDeveloper::findOrFail($id);
        return view('content.products.developers.edit', compact('developer'));
    }

    public function update(Request $request, $id)
    {
        $developer = ProductDeveloper::findOrFail($id);

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_developers,slug,'.$developer->id,
            'website'=> 'nullable|url|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $developer->update($validated);

        return redirect()->route('developers.index')->with('success', 'Developer updated successfully.');
    }

    public function destroy($id)
    {
        $developer = ProductDeveloper::findOrFail($id);
        $developer->delete();

        return response()->json(['message' => 'Developer deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No developers selected'], 400);
        }

        ProductDeveloper::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Selected developers deleted successfully']);
    }
}
