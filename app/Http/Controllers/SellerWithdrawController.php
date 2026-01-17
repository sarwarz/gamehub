<?php

namespace App\Http\Controllers;

use App\Models\SellerWithdraw;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SellerWithdrawController extends Controller
{
    /**
     * All withdraw requests
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->withdrawDataTable(
                SellerWithdraw::with(['seller.user'])
            );
        }

        return view('content.seller_withdraws.index');
    }

    /**
     * Pending withdraw requests
     */
    public function pending(Request $request)
    {
        if ($request->ajax()) {
            return $this->withdrawDataTable(
                SellerWithdraw::where('status', 'pending')
                    ->with(['seller.user'])
            );
        }

        return view('content.seller_withdraws.pending');
    }

    /**
     * Approve withdraw
     */
    public function approve($id)
    {
        $withdraw = SellerWithdraw::findOrFail($id);
        $withdraw->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Withdraw approved successfully'
        ]);
    }

    /**
     * Reject withdraw
     */
    public function reject(Request $request, $id)
    {
        $withdraw = SellerWithdraw::findOrFail($id);
        $withdraw->update([
            'status' => 'rejected',
            'note'   => $request->note
        ]);

        return response()->json([
            'message' => 'Withdraw rejected'
        ]);
    }

    /**
     * Reusable DataTable builder
     */
    private function withdrawDataTable($query)
    {
        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('seller', function ($row) {
                return e($row->seller->store_name ?? 'N/A');
            })

            ->addColumn('email', function ($row) {
                return e($row->seller->user->email ?? 'N/A');
            })

            ->addColumn('amount', function ($row) {
                return '<strong>$'.number_format($row->amount, 2).'</strong>';
            })

            ->addColumn('method', fn($row) => ucfirst($row->method))

            ->addColumn('status_badge', function ($row) {
                $map = [
                    'pending'  => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                ];

                $class = $map[$row->status] ?? 'secondary';

                return '<span class="badge bg-'.$class.'">'
                    .ucfirst($row->status).
                '</span>';
            })

            ->addColumn('created_at', fn($row) =>
                $row->created_at->format('d M Y')
            )

            ->addColumn('actions', function ($row) {

                if ($row->status !== 'pending') {
                    return '-';
                }

                return '
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success btn-approve"
                            data-url="'.route('seller-withdraws.approve', $row->id).'">
                            Approve
                        </button>

                        <button class="btn btn-sm btn-danger btn-reject"
                            data-url="'.route('seller-withdraws.reject', $row->id).'">
                            Reject
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['amount', 'status_badge', 'actions'])
            ->make(true);
    }
}
