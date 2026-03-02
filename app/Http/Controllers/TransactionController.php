<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionDataTable(
                Transaction::with(['user', 'seller'])
            );
        }

        $stats = [
            'total'     => Transaction::count(),
            'completed' => Transaction::where('status', 'completed')->count(),
            'pending'   => Transaction::where('status', 'pending')->count(),
            'failed'    => Transaction::where('status', 'failed')->count(),
            'volume'    => Transaction::where('status', 'completed')->sum('amount'),
        ];

        return view('content.transactions.index', compact('stats'));
    }

    public function pending(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionDataTable(
                Transaction::where('status', 'pending')->with(['user', 'seller'])
            );
        }

        return view('content.transactions.pending');
    }

    public function failed(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionDataTable(
                Transaction::where('status', 'failed')->with(['user', 'seller'])
            );
        }

        $stats = [
            'failed_count' => Transaction::where('status', 'failed')->count(),
            'failed_amount' => Transaction::where('status', 'failed')->sum('amount'),
        ];

        return view('content.transactions.failed', compact('stats'));
    }

    public function completed(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionDataTable(
                Transaction::where('status', 'completed')->with(['user', 'seller'])
            );
        }

        $stats = [
            'completed_count' => Transaction::where('status', 'completed')->count(),
            'completed_volume' => Transaction::where('status', 'completed')->sum('amount'),
        ];

        return view('content.transactions.completed', compact('stats'));
    }

    /*
    |--------------------------------------------------------------------------
    | DataTable Builder
    |--------------------------------------------------------------------------
    */

    private function transactionDataTable($query)
    {
        $query = $this->applyFilters($query);

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('checkbox', fn ($row) =>
                '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$row->id.'">'
            )

            ->addColumn('trx', fn ($row) =>
                '<code class="small">'.e($row->trx).'</code>'
            )

            ->addColumn('owner', function ($row) {
                if ($row->seller) {
                    return '<div class="d-flex align-items-center"><div class="avatar avatar-sm me-2 bg-label-info rounded-circle d-flex align-items-center justify-content-center"><i class="ti tabler-building-store ti-xs"></i></div><div class="lh-sm"><span class="fw-semibold d-block">'.e($row->seller->store_name).'</span><small class="text-muted">Seller #'.$row->seller->id.'</small></div></div>';
                }
                if ($row->user) {
                    $avatar = $row->user->avatar_url ?? asset('assets/img/avatars/1.png');
                    return '<div class="d-flex align-items-center"><img src="'.$avatar.'" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover"><div class="lh-sm"><span class="fw-semibold d-block">'.e($row->user->name).'</span><small class="text-muted">'.e($row->user->email).'</small></div></div>';
                }
                return '<div class="d-flex align-items-center"><div class="avatar avatar-sm me-2 bg-label-secondary rounded-circle d-flex align-items-center justify-content-center"><i class="ti tabler-robot ti-xs"></i></div><div class="lh-sm"><span class="fw-semibold d-block">System</span><small class="text-muted">Auto generated</small></div></div>';
            })

            ->addColumn('type', fn ($row) =>
                '<span class="badge bg-label-'.($row->type === 'credit' ? 'success' : 'danger').'"><i class="ti tabler-arrow-'.($row->type === 'credit' ? 'down-left' : 'up-right').' ti-xs me-1"></i>'.ucfirst($row->type).'</span>'
            )

            ->addColumn('amount', fn ($row) =>
                '<span class="fw-semibold">'.format_currency($row->amount).'</span>'
            )

            ->addColumn('fee', fn ($row) =>
                $row->fee > 0 ? '<span class="text-danger">'.format_currency($row->fee).'</span>' : '<span class="text-muted">—</span>'
            )

            ->addColumn('net_amount', fn ($row) =>
                '<span class="fw-bold text-primary">'.format_currency($row->net_amount).'</span>'
            )

            ->addColumn('category', fn ($row) =>
                '<span class="badge bg-label-primary" style="font-size:.7rem">'.ucfirst($row->category).'</span>'
            )

            ->addColumn('payment', function ($row) {
                if (!$row->payment_method) return '<span class="text-muted small">Wallet</span>';
                $icons = ['stripe' => 'tabler-brand-stripe', 'paypal' => 'tabler-brand-paypal', 'wallet' => 'tabler-wallet'];
                $icon = $icons[strtolower($row->payment_method)] ?? 'tabler-credit-card';
                return '<div class="d-flex align-items-center"><i class="ti '.$icon.' ti-xs me-1 text-muted"></i><span class="small">'.ucfirst($row->payment_method).'</span></div>';
            })

            ->addColumn('status', function ($row) {
                $map = ['pending' => 'warning', 'completed' => 'success', 'failed' => 'danger', 'reversed' => 'secondary'];
                return '<span class="badge bg-label-'.($map[$row->status] ?? 'secondary').'">'.ucfirst($row->status).'</span>';
            })

            ->addColumn('date', fn ($row) =>
                '<span class="small text-muted">'.$row->created_at->format('M d, Y').'</span><div class="text-muted small">'.$row->created_at->format('h:i A').'</div>'
            )

            ->rawColumns([
                'checkbox', 'trx', 'owner', 'type', 'amount',
                'fee', 'net_amount', 'category', 'payment', 'status', 'date',
            ])
            ->make(true);
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    private function applyFilters($query)
    {
        $filters = request()->input('filters', []);

        if (!is_array($filters)) {
            return $query;
        }

        if (!is_array($filters)) {
            return $query;
        }

        foreach ($filters as $filter) {

            $field    = $filter['field']    ?? null;
            $operator = $filter['operator'] ?? '=';
            $value    = $filter['value']    ?? null;

            if (!$field || $value === null || $value === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Owner Filters (User + Seller)
            |--------------------------------------------------------------------------
            */
            if ($field === 'owner_name') {
                $query->where(function ($q) use ($value) {
                    $q->whereHas('user', fn ($u) =>
                        $u->where('name', 'like', "%{$value}%")
                    )->orWhereHas('seller', fn ($s) =>
                        $s->where('store_name', 'like', "%{$value}%")
                    );
                });
                continue;
            }

            if ($field === 'owner_email') {
                $query->whereHas('user', fn ($q) =>
                    $q->where('email', 'like', "%{$value}%")
                );
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Date Filter
            |--------------------------------------------------------------------------
            */
            if ($field === 'created_at') {
                $query->whereDate('created_at', $value);
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Amount / Numeric Filters
            |--------------------------------------------------------------------------
            */
            if (in_array($field, ['amount', 'fee', 'net_amount'])) {
                $query->where($field, $operator, (float) $value);
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Enum / Exact Match Filters
            |--------------------------------------------------------------------------
            */
            if (in_array($field, [
                'status',
                'type',
                'category',
                'currency',
                'payment_method'
            ])) {

                if ($operator === 'like') {
                    $query->where($field, 'like', "%{$value}%");
                } else {
                    $query->where($field, $operator, $value);
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Fallback (Text fields)
            |--------------------------------------------------------------------------
            */
            if ($operator === 'like') {
                $query->where($field, 'like', "%{$value}%");
            } else {
                $query->where($field, $operator, $value);
            }
        }

        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | Bulk Actions
    |--------------------------------------------------------------------------
    */

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'status' => 'required|in:pending,completed,failed,reversed',
        ]);

        Transaction::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction status updated successfully',
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            Transaction::whereIn('id', $request->ids)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Transactions deleted successfully',
        ]);
    }
}
