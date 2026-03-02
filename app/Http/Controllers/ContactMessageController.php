<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ContactMessage::query();

            if ($request->filled('filter_status')) {
                $query->where('status', $request->filter_status);
            }

            return DataTables::of($query)
                ->addColumn('checkbox', fn ($row) => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $row->id . '">')
                ->addColumn('sender_info', function ($row) {
                    $name  = e($row->name);
                    $email = e($row->email);
                    $phone = $row->phone ? '<br><small class="text-muted"><i class="ti tabler-phone"></i> ' . e($row->phone) . '</small>' : '';
                    return '<div><span class="fw-semibold">' . $name . '</span><br><small class="text-muted">' . $email . '</small>' . $phone . '</div>';
                })
                ->addColumn('subject_col', function ($row) {
                    $icon = $row->status === 'new' ? '<span class="badge badge-dot bg-primary me-1"></span>' : '';
                    $preview = e(\Illuminate\Support\Str::limit($row->message, 60));
                    return $icon . '<span class="fw-semibold">' . e($row->subject) . '</span><br><small class="text-muted">' . $preview . '</small>';
                })
                ->addColumn('status_badge', function ($row) {
                    return match ($row->status) {
                        'new'      => '<span class="badge bg-label-primary">New</span>',
                        'read'     => '<span class="badge bg-label-info">Read</span>',
                        'replied'  => '<span class="badge bg-label-success">Replied</span>',
                        'archived' => '<span class="badge bg-label-secondary">Archived</span>',
                    };
                })
                ->addColumn('date_col', function ($row) {
                    return $row->created_at ? $row->created_at->format('M d, Y H:i') : '—';
                })
                ->addColumn('actions', function ($row) {
                    $archive = $row->status !== 'archived'
                        ? '<button type="button" class="btn btn-icon btn-sm btn-label-warning btn-status" data-id="' . $row->id . '" data-status="archived" title="Archive">
                            <i class="ti tabler-archive ti-xs"></i>
                        </button>'
                        : '';
                    return '<div class="d-flex align-items-center justify-content-center gap-1">
                        <button type="button" class="btn btn-icon btn-sm btn-label-info btn-view" data-id="' . $row->id . '" title="View">
                            <i class="ti tabler-eye ti-xs"></i>
                        </button>
                        ' . $archive . '
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger btn-delete" data-id="' . $row->id . '" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['checkbox', 'sender_info', 'subject_col', 'status_badge', 'actions'])
                ->make(true);
        }

        $stats = [
            'total'    => ContactMessage::count(),
            'new'      => ContactMessage::where('status', 'new')->count(),
            'read'     => ContactMessage::where('status', 'read')->count(),
            'replied'  => ContactMessage::where('status', 'replied')->count(),
            'archived' => ContactMessage::where('status', 'archived')->count(),
        ];

        return view('content.contact-messages.index', compact('stats'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return response()->json(['data' => $contactMessage]);
    }

    public function update(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate([
            'status'      => 'sometimes|in:new,read,replied,archived',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        if (isset($data['status']) && $data['status'] === 'replied' && $contactMessage->status !== 'replied') {
            $data['replied_at'] = now();
        }

        $contactMessage->update($data);

        return response()->json(['message' => 'Message updated successfully.']);
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Message deleted successfully.']);
        }
        return back()->with('success', 'Message deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        ContactMessage::whereIn('id', $request->ids)->delete();
        return response()->json(['message' => count($request->ids) . ' messages deleted.']);
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'status' => 'required|in:read,archived',
        ]);
        ContactMessage::whereIn('id', $request->ids)->update(['status' => $request->status]);
        return response()->json(['message' => count($request->ids) . ' messages updated.']);
    }
}
