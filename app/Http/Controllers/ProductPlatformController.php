<?php

namespace App\Http\Controllers;

use App\Models\ProductPlatform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductPlatformController extends Controller
{
    /**
     * Display a listing of the platforms.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $platforms = ProductPlatform::query();

            return DataTables::of($platforms)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">';
                })
                ->addColumn('status_badge', function ($row) {
                    $class = $row->status === 'active' ? 'success' : 'secondary';
                    return '<span class="badge bg-' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl   = route('platforms.edit', $row->id);
                    $deleteUrl = route('platforms.destroy', $row->id);

                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="'.$deleteUrl.'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('content.products.platform.index');
    }

    /**
     * Store a newly created platform in storage.
     */
    public function store(Request $request)
    {
        
        try {
            $validated = $request->validate([
                'name'   => 'required|string|max:255',
                'slug'   => 'required|string|max:255|unique:product_platforms,slug',
                'icon'   => 'nullable|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            ProductPlatform::create($validated);

            return redirect()->route('platforms.index')
                ->with('success', 'Platform created successfully.');
        } catch (\Exception $e) {
            Log::error('Platform create failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create platform. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified platform.
     */
    public function edit($id)
    {
        $platform = ProductPlatform::findOrFail($id);
        return view('content.products.platform.edit', compact('platform'));
    }

    /**
     * Update the specified platform in storage.
     */
    public function update(Request $request, $id)
    {
        $platform = ProductPlatform::findOrFail($id);

        try {
            $validated = $request->validate([
                'name'   => 'required|string|max:255',
                'slug'   => 'required|string|max:255|unique:product_platforms,slug,' . $platform->id,
                'icon'   => 'nullable|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            $platform->update($validated);

            return redirect()->route('platforms.index')
                ->with('success', 'Platform updated successfully.');
        } catch (\Exception $e) {
            Log::error('Platform update failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to update platform. Please try again.');
        }
    }

    /**
     * Remove the specified platform from storage.
     */
    public function destroy($id)
    {
        try {
            $platform = ProductPlatform::findOrFail($id);
            $platform->delete();

            return response()->json(['message' => 'Platform deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Platform delete failed: '.$e->getMessage());
            return response()->json(['message' => 'Failed to delete platform.'], 500);
        }
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if(!$ids || !is_array($ids)){
            return response()->json(['message' => 'No platforms selected'], 400);
        }

        try {
            ProductPlatform::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Selected platforms deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Bulk platform delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete platforms'], 500);
        }
    }
}
