<?php

namespace App\Http\Controllers;

use App\Models\ProductLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductLanguageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $languages = ProductLanguage::query();

            return DataTables::of($languages)
                ->addIndexColumn()
                ->addColumn('checkbox', fn($row) =>
                    '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">'
                )
                ->addColumn('status_badge', fn($row) =>
                    '<span class="badge bg-'.($row->status === 'active' ? 'success' : 'secondary').'">'
                    .ucfirst($row->status).'</span>'
                )
                ->addColumn('actions', function ($row) {
                    $editUrl = route('languages.edit', $row->id);
                    $deleteUrl = route('languages.destroy', $row->id);
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

        $stats = ['total' => ProductLanguage::count()];

        return view('content.products.languages.index', compact('stats'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_languages,slug',
            'code'   => 'nullable|string|max:10|unique:product_languages,code',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            ProductLanguage::create($validated);
            return redirect()->route('languages.index')->with('success', 'Product Language created successfully.');
        } catch (\Exception $e) {
            Log::error('Product Language create failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create product language. Please try again.');
        }
    }

    public function edit($id)
    {
        $language = ProductLanguage::findOrFail($id);
        return view('content.products.languages.edit', compact('language'));
    }

    public function update(Request $request, $id)
    {

        $language = ProductLanguage::findOrFail($id);

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:product_languages,slug,'.$language->id,
            'code'   => 'nullable|string|max:10|unique:product_languages,code,'.$language->id,
            'status' => 'required|in:active,inactive',
        ]);

        $language->update($validated);

        return redirect()->route('languages.index')->with('success', 'Product Language updated successfully.');
    }

    public function destroy($id)
    {

        $language = ProductLanguage::findOrFail($id);
        $language->delete();

        return response()->json(['message' => 'Product Language deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No product languages selected'], 400);
        }

        ProductLanguage::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Selected product languages deleted successfully']);
    }
}
