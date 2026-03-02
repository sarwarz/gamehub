<?php

namespace App\Http\Controllers;

use App\Models\TicketDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TicketDepartmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = TicketDepartment::withCount('tickets');

            return DataTables::of($query)
                ->addColumn('name_col', function ($d) {
                    $icon = '<i class="' . e($d->icon) . ' me-2" style="font-size:1.1rem"></i>';
                    return '<div class="d-flex align-items-center">' . $icon . '<div><span class="fw-semibold">' . e($d->name) . '</span><br><code class="text-muted" style="font-size:.72rem">' . e($d->slug) . '</code></div></div>';
                })
                ->addColumn('color_col', function ($d) {
                    return '<span class="badge bg-label-' . e($d->color) . '">' . ucfirst($d->color) . '</span>';
                })
                ->addColumn('tickets_count_col', function ($d) {
                    return '<span class="fw-semibold">' . $d->tickets_count . '</span>';
                })
                ->addColumn('status_col', fn ($d) => $d->is_active
                    ? '<span class="badge bg-label-success">Active</span>'
                    : '<span class="badge bg-label-secondary">Inactive</span>')
                ->addColumn('actions', function ($d) {
                    return '<div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-icon btn-text-primary rounded-pill btn-edit" data-id="' . $d->id . '"><i class="ti tabler-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill btn-delete" data-id="' . $d->id . '"><i class="ti tabler-trash"></i></button>
                    </div>';
                })
                ->rawColumns(['name_col', 'color_col', 'tickets_count_col', 'status_col', 'actions'])
                ->make(true);
        }

        return view('content.support-tickets.departments');
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'nullable|string|max:100|unique:ticket_departments,slug',
            'color'       => 'required|string|max:30',
            'icon'        => 'nullable|string|max:60',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'sometimes|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['icon']       = $data['icon'] ?: 'ti tabler-folder';

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        TicketDepartment::create($data);

        return response()->json(['message' => 'Department created successfully.']);
    }

    public function show(TicketDepartment $ticketDepartment)
    {
        return response()->json($ticketDepartment);
    }

    public function update(Request $request, TicketDepartment $ticketDepartment)
    {

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'nullable|string|max:100|unique:ticket_departments,slug,' . $ticketDepartment->id,
            'color'       => 'required|string|max:30',
            'icon'        => 'nullable|string|max:60',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'sometimes|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['icon']       = $data['icon'] ?: 'ti tabler-folder';

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $ticketDepartment->update($data);

        return response()->json(['message' => 'Department updated successfully.']);
    }

    public function destroy(TicketDepartment $ticketDepartment)
    {

        if ($ticketDepartment->tickets()->exists()) {
            return response()->json(['message' => 'Cannot delete — department has associated tickets.'], 422);
        }

        $ticketDepartment->delete();

        return response()->json(['message' => 'Department deleted successfully.']);
    }
}
