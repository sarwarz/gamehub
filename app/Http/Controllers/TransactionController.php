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

        return view('content.transactions.index');
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

        return view('content.transactions.failed');
    }

    public function completed(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionDataTable(
                Transaction::where('status', 'completed')->with(['user', 'seller'])
            );
        }

        return view('content.transactions.completed');
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
                '<code>'.e($row->trx).'</code>'
            )

            ->addColumn('owner', function ($row) {

                if ($row->seller) {
                    return '
                    <div class="d-flex flex-column">
                        <div class="fw-semibold">'.$row->seller->store_name.'
                        </div>
                        <small class="text-muted">ID: #'.$row->seller->id.'</small>
                    </div>';
                }

                if ($row->user) {
                    return '
                    <div class="d-flex flex-column">
                        <div class="fw-semibold">'.$row->user->name.'
                        </div>
                        <small class="text-muted">'.$row->user->email.'</small>
                    </div>';
                }

                return '
                <div class="d-flex flex-column">
                    <div class="fw-semibold">System</div>
                    <small class="text-muted">Auto generated</small>
                </div>';
            })

            ->addColumn('type', fn ($row) =>
                $row->type === 'credit'
                    ? '<span class="badge bg-success">Credit</span>'
                    : '<span class="badge bg-danger">Debit</span>'
            )

            ->addColumn('amount', fn ($row) =>
                $row->currency.' '.number_format($row->amount, 2)
            )

            ->addColumn('fee', fn ($row) =>
                $row->fee > 0
                    ? $row->currency.' '.number_format($row->fee, 2)
                    : '-'
            )

            ->addColumn('net_amount', fn ($row) =>
                '<strong>'.$row->currency.' '.number_format($row->net_amount, 2).'</strong>'
            )

            ->addColumn('category', fn ($row) => ucfirst($row->category))

            ->addColumn('payment', fn ($row) =>
                $row->payment_method ? ucfirst($row->payment_method) : 'Wallet'
            )

            ->addColumn('status', function ($row) {
                $map = [
                    'pending'   => 'warning',
                    'completed' => 'success',
                    'failed'    => 'danger',
                    'reversed'  => 'secondary',
                ];

                return '<span class="badge bg-'.($map[$row->status] ?? 'secondary').'">'
                    .ucfirst($row->status).
                '</span>';
            })

            ->addColumn('date', fn ($row) =>
                $row->created_at->format('d M Y, h:i A')
            )

            ->rawColumns([
                'checkbox',
                'trx',
                'owner',
                'type',
                'net_amount',
                'status',
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
