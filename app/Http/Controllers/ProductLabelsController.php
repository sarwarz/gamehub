<?php

namespace App\Http\Controllers;

use App\Models\ProductLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductLabelsController extends Controller
{
    /**
     * Display a listing of product labels.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $labels = ProductLabel::query();

            return datatables()->of($labels)
                ->addIndexColumn()

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">';
                })

                ->addColumn('label_preview', function ($row) {
                    return '
                        <span class="badge"
                            style="background-color: '.$row->bg_color.';
                                   color: '.$row->text_color.';">
                            '.$row->name.'
                        </span>
                    ';
                })

                ->addColumn('status_badge', function ($row) {
                    $class = $row->status === 'active' ? 'success' : 'secondary';
                    return '<span class="badge bg-'.$class.'">'.ucfirst($row->status).'</span>';
                })

                ->addColumn('actions', function ($row) {
                    $editUrl = route('labels.edit', $row->id);
                    $deleteUrl = route('labels.destroy', $row->id);
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

                ->rawColumns(['checkbox', 'label_preview', 'status_badge', 'actions'])
                ->make(true);
        }

        $stats = ['total' => ProductLabel::count()];

        return view('content.products.labels.index', compact('stats'));
    }

    /**
     * Show the form for creating a new label.
     */
    public function create()
    {
        return view('content.products.labels.create');
    }

    /**
     * Store a newly created label.
     */
    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'name'       => 'required|string|max:255|unique:product_labels,name',
                'bg_color'   => 'required|string|max:20',
                'text_color' => 'required|string|max:20',
                'status'     => 'required|in:active,inactive',
            ]);

            ProductLabel::create($validated);

            return redirect()
                ->route('labels.index')
                ->with('success', 'Product label created successfully.');
        } catch (\Exception $e) {
            Log::error('Product label create failed: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to create product label.');
        }
    }

    /**
     * Show the form for editing the specified label.
     */
    public function edit($id)
    {
        $label = ProductLabel::findOrFail($id);

        return view('content.products.labels.edit', compact('label'));
    }

    /**
     * Update the specified label.
     */
    public function update(Request $request, $id)
    {

        $label = ProductLabel::findOrFail($id);

        try {
            $validated = $request->validate([
                'name'       => 'required|string|max:255|unique:product_labels,name,' . $label->id,
                'bg_color'   => 'required|string|max:20',
                'text_color' => 'required|string|max:20',
                'status'     => 'required|in:active,inactive',
            ]);

            $label->update($validated);

            return redirect()
                ->route('labels.index')
                ->with('success', 'Product label updated successfully.');
        } catch (\Exception $e) {
            Log::error('Product label update failed: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update product label.');
        }
    }

    /**
     * Remove the specified label.
     */
    public function destroy($id)
    {

        try {
            $label = ProductLabel::findOrFail($id);
            $label->delete();

            return response()->json([
                'message' => 'Product label deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Product label delete failed: '.$e->getMessage());

            return response()->json([
                'message' => 'Failed to delete product label'
            ], 500);
        }
    }

    /**
     * Bulk delete product labels.
     */
    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'No labels selected'], 400);
        }

        try {
            ProductLabel::whereIn('id', $ids)->delete();

            return response()->json([
                'message' => 'Selected product labels deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Product label bulk delete failed: '.$e->getMessage());

            return response()->json([
                'message' => 'Failed to delete product labels'
            ], 500);
        }
    }
}
