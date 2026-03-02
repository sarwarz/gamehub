<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Subscriber::query();

            if ($request->filled('filter_status')) {
                $query->where('status', $request->filter_status);
            }

            return DataTables::of($query)
                ->addColumn('checkbox', fn ($row) => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $row->id . '">')
                ->addColumn('subscriber_info', function ($row) {
                    $name = e($row->name ?: '—');
                    $email = e($row->email);
                    return '<div><span class="fw-semibold">' . $email . '</span>'
                         . ($row->name ? '<br><small class="text-muted">' . $name . '</small>' : '')
                         . '</div>';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->status === 'active'
                        ? '<span class="badge bg-label-success">Active</span>'
                        : '<span class="badge bg-label-warning">Unsubscribed</span>';
                })
                ->addColumn('date_col', function ($row) {
                    $sub = $row->subscribed_at ? $row->subscribed_at->format('M d, Y') : ($row->created_at ? $row->created_at->format('M d, Y') : '—');
                    $unsub = $row->unsubscribed_at ? '<br><small class="text-muted">Unsub: ' . $row->unsubscribed_at->format('M d, Y') . '</small>' : '';
                    return $sub . $unsub;
                })
                ->addColumn('actions', function ($row) {
                    $toggleClass = $row->status === 'active' ? 'btn-label-warning' : 'btn-label-success';
                    $toggleTitle = $row->status === 'active' ? 'Unsubscribe' : 'Resubscribe';
                    return '<div class="d-flex align-items-center justify-content-center gap-1">
                        <button type="button" class="btn btn-icon btn-sm ' . $toggleClass . ' btn-toggle" data-id="' . $row->id . '" data-status="' . $row->status . '" title="' . $toggleTitle . '">
                            <i class="ti tabler-switch-horizontal ti-xs"></i>
                        </button>
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger btn-delete" data-id="' . $row->id . '" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['checkbox', 'subscriber_info', 'status_badge', 'date_col', 'actions'])
                ->make(true);
        }

        $stats = [
            'total'        => Subscriber::count(),
            'active'       => Subscriber::where('status', 'active')->count(),
            'unsubscribed' => Subscriber::where('status', 'unsubscribed')->count(),
            'this_month'   => Subscriber::where('status', 'active')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        return view('content.subscribers.index', compact('stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:subscribers,email',
            'name'  => 'nullable|string|max:255',
        ]);

        $data['status']        = 'active';
        $data['subscribed_at'] = now();
        $data['ip_address']    = $request->ip();

        Subscriber::create($data);

        return response()->json(['message' => 'Subscriber added successfully.']);
    }

    public function update(Request $request, Subscriber $subscriber)
    {
        if ($request->has('_quick')) {
            if ($subscriber->status === 'active') {
                $subscriber->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
            } else {
                $subscriber->update(['status' => 'active', 'unsubscribed_at' => null]);
            }
            return response()->json(['message' => 'Status updated.']);
        }

        $data = $request->validate([
            'email' => 'required|email|unique:subscribers,email,' . $subscriber->id,
            'name'  => 'nullable|string|max:255',
        ]);

        $subscriber->update($data);

        return response()->json(['message' => 'Subscriber updated successfully.']);
    }

    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Subscriber deleted successfully.']);
        }
        return back()->with('success', 'Subscriber deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        Subscriber::whereIn('id', $request->ids)->delete();
        return response()->json(['message' => count($request->ids) . ' subscribers deleted.']);
    }

    public function export()
    {
        $subscribers = Subscriber::where('status', 'active')->get(['email', 'name', 'subscribed_at']);

        $csv = "Email,Name,Subscribed At\n";
        foreach ($subscribers as $sub) {
            $csv .= '"' . $sub->email . '","' . ($sub->name ?? '') . '","' . ($sub->subscribed_at ?? '') . "\"\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');
    }
}
