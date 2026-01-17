<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    /**
     * All transactions
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

    /**
     * Pending transactions
     */
    public function pending(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionDataTable(
                Transaction::where('status', 'pending')->with(['user', 'seller'])
            );
        }

        return view('content.transactions.pending');
    }

    /**
     * Failed transactions
     */
    public function failed(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionDataTable(
                Transaction::where('status', 'failed')->with(['user', 'seller'])
            );
        }

        return view('content.transactions.failed');
    }

    /**
     * Completed transactions
     */
    public function completed(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionDataTable(
                Transaction::where('status', 'completed')->with(['user', 'seller'])
            );
        }

        return view('content.transactions.completed');
    }

    /**
     * Reusable DataTable builder
     */
    private function transactionDataTable($query)
    {
        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('trx', fn($row) => e($row->trx))

            ->addColumn('owner', function ($row) {
                if ($row->seller) {
                    return 'Seller: <strong>'.e($row->seller->store_name).'</strong>';
                }

                return 'User: <strong>'.e($row->user->name ?? 'N/A').'</strong>';
            })

            ->addColumn('type_badge', function ($row) {
                return $row->type === 'credit'
                    ? '<span class="badge bg-success">Credit</span>'
                    : '<span class="badge bg-danger">Debit</span>';
            })

            ->addColumn('amount', function ($row) {
                return '<strong>'.$row->currency.' '.number_format($row->amount, 2).'</strong>';
            })

            ->addColumn('category', fn($row) => ucfirst($row->category))

            ->addColumn('status_badge', function ($row) {
                $map = [
                    'pending'   => 'warning',
                    'completed' => 'success',
                    'failed'    => 'danger',
                    'reversed'  => 'secondary',
                ];

                $class = $map[$row->status] ?? 'secondary';

                return '<span class="badge bg-'.$class.'">'
                    .ucfirst($row->status).
                '</span>';
            })

            ->addColumn('created_at', fn($row) =>
                $row->created_at->format('d M Y H:i')
            )

            ->rawColumns([
                'owner',
                'type_badge',
                'amount',
                'status_badge',
            ])
            ->make(true);
    }
}
