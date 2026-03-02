<?php

namespace App\Http\Controllers;

use App\Models\CannedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CannedResponseController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = CannedResponse::query();

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            return DataTables::of($query)
                ->addColumn('title_col', function ($r) {
                    $shortcut = $r->shortcut ? '<code class="text-primary ms-2">' . e($r->shortcut) . '</code>' : '';
                    return '<span class="fw-semibold">' . e($r->title) . '</span>' . $shortcut;
                })
                ->addColumn('body_col', fn ($r) => '<span class="text-muted" style="font-size:.82rem">' . e(Str::limit($r->body, 80)) . '</span>')
                ->addColumn('category_badge', function ($r) {
                    $colors = [
                        'greeting' => 'primary', 'order' => 'info', 'payment' => 'warning',
                        'refund' => 'danger', 'shipping' => 'success', 'product' => 'secondary',
                        'account' => 'dark', 'closing' => 'primary', 'general' => 'secondary',
                    ];
                    return '<span class="badge bg-label-' . ($colors[$r->category] ?? 'secondary') . '">' . ucfirst($r->category) . '</span>';
                })
                ->addColumn('status_col', fn ($r) => $r->is_active
                    ? '<span class="badge bg-label-success">Active</span>'
                    : '<span class="badge bg-label-secondary">Inactive</span>')
                ->addColumn('actions', function ($r) {
                    return '<div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-icon btn-text-primary rounded-pill btn-edit" data-id="' . $r->id . '"><i class="ti tabler-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill btn-delete" data-id="' . $r->id . '"><i class="ti tabler-trash"></i></button>
                    </div>';
                })
                ->rawColumns(['title_col', 'body_col', 'category_badge', 'status_col', 'actions'])
                ->make(true);
        }

        return view('content.support-tickets.canned-responses');
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string|max:5000',
            'category'   => 'required|in:' . implode(',', CannedResponse::CATEGORIES),
            'shortcut'   => 'nullable|string|max:50|unique:canned_responses,shortcut',
            'is_active'  => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if (!empty($data['shortcut']) && !str_starts_with($data['shortcut'], '/')) {
            $data['shortcut'] = '/' . $data['shortcut'];
        }

        CannedResponse::create($data);

        return response()->json(['message' => 'Template created successfully.']);
    }

    public function show(CannedResponse $cannedResponse)
    {
        return response()->json($cannedResponse);
    }

    public function update(Request $request, CannedResponse $cannedResponse)
    {

        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string|max:5000',
            'category'   => 'required|in:' . implode(',', CannedResponse::CATEGORIES),
            'shortcut'   => 'nullable|string|max:50|unique:canned_responses,shortcut,' . $cannedResponse->id,
            'is_active'  => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if (!empty($data['shortcut']) && !str_starts_with($data['shortcut'], '/')) {
            $data['shortcut'] = '/' . $data['shortcut'];
        }

        $cannedResponse->update($data);

        return response()->json(['message' => 'Template updated successfully.']);
    }

    public function destroy(CannedResponse $cannedResponse)
    {

        $cannedResponse->delete();

        return response()->json(['message' => 'Template deleted successfully.']);
    }
}
