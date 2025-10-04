<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = ProductCategory::select('product_categories.*');

            return datatables()->of($categories)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">';
                })
                ->addColumn('status_badge', function ($row) {
                    $class = $row->status === 'active' ? 'success' : 'secondary';
                    return '<span class="badge bg-' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl   = route('categories.edit', $row->id);
                    $deleteUrl = route('categories.destroy', $row->id);

                    return '
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="'.$deleteUrl.'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox', 'status_badge', 'actions'])
                ->make(true);

        }


        return view('content.products.category.index');
    }


    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $categories = ProductCategory::with('children')->whereNull('parent_id')->get();
        return view('content.products.category.create', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'slug'        => 'required|string|max:255|unique:product_categories,slug',
                'parent_id'   => 'nullable|exists:product_categories,id',
                'description' => 'nullable|string',
                'status'      => 'required|in:active,inactive',
                'attachment'  => 'nullable|image|max:2048'
            ]);

            if ($request->hasFile('attachment')) {
                $validated['attachment'] = $request->file('attachment')->store('categories', 'public');
            }

            ProductCategory::create($validated);

            return redirect()->route('categories.index')->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            Log::error('Category create failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create category. Please try again.');
        }
    }

    /**
     * Display a single category details.
     */
    public function show($id)
    {
        $category = ProductCategory::with('children')->findOrFail($id);
        return view('content.products.category.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        $category   = ProductCategory::findOrFail($id);

        return view('content.products.category.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'slug'        => 'required|string|max:255|unique:product_categories,slug,' . $category->id,
                'parent_id'   => 'nullable|exists:product_categories,id|not_in:' . $id, // prevent self-parenting
                'description' => 'nullable|string',
                'status'      => 'required|in:active,inactive',
                'attachment'  => 'nullable|image|max:2048'
            ]);

            if ($request->hasFile('attachment')) {
                $validated['attachment'] = $request->file('attachment')->store('categories', 'public');
            }

            $category->update($validated);

            return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            Log::error('Category update failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to update category. Please try again.');
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        try {
            $category = ProductCategory::findOrFail($id);
            $category->delete();

            return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Category delete failed: '.$e->getMessage());
            return redirect()->route('categories.index')->with('error', 'Failed to delete category.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if(!$ids || !is_array($ids)){
            return response()->json(['message' => 'No categories selected'], 400);
        }

        try {
            ProductCategory::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Selected categories deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Bulk delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete categories'], 500);
        }
    }

}
