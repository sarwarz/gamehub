<?php

namespace App\Http\Controllers;

use App\Models\ProductPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductPublisherController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $publishers = ProductPublisher::query();

            return DataTables::of($publishers)
                ->addIndexColumn()
                ->addColumn('checkbox', fn($row) =>
                    '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">'
                )
                ->addColumn('status_badge', fn($row) =>
                    '<span class="badge bg-'.($row->status === 'active' ? 'success' : 'secondary').'">'
                    .ucfirst($row->status).'</span>'
                )
                ->addColumn('actions', function ($row) {
                    $editUrl   = route('publishers.edit', $row->id);
                    $deleteUrl = route('publishers.destroy', $row->id);

                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="'.$deleteUrl.'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox','status_badge','actions'])
                ->make(true);
        }

        return view('content.products.publishers.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_publishers,slug',
            'website'=> 'nullable|url|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            ProductPublisher::create($validated);
            return redirect()->route('publishers.index')->with('success', 'Publisher created successfully.');
        } catch (\Exception $e) {
            Log::error('Publisher create failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create publisher. Please try again.');
        }
    }

    public function edit($id)
    {
        $publisher = ProductPublisher::findOrFail($id);
        return view('content.products.publishers.edit', compact('publisher'));
    }

    public function update(Request $request, $id)
    {
        $publisher = ProductPublisher::findOrFail($id);

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_publishers,slug,'.$publisher->id,
            'website'=> 'nullable|url|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $publisher->update($validated);

        return redirect()->route('publishers.index')->with('success', 'Publisher updated successfully.');
    }

    public function destroy($id)
    {
        $publisher = ProductPublisher::findOrFail($id);
        $publisher->delete();

        return response()->json(['message' => 'Publisher deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No publishers selected'], 400);
        }

        ProductPublisher::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Selected publishers deleted successfully']);
    }
}
