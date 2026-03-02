<?php

namespace App\Http\Controllers;

use App\Models\AffiliateWithdrawal;
use App\Models\AffiliateBalance;
use App\Notifications\AffiliateWithdrawalProcessedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AffiliateWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = AffiliateWithdrawal::with(['affiliate.user']);

            if ($request->filled('status')) $query->where('status', $request->status);

            return DataTables::of($query)
                ->addColumn('affiliate_col', function ($r) {
                    return e($r->affiliate?->user?->name ?? '—');
                })
                ->addColumn('trx_col', fn ($r) => '<code>' . e($r->trx) . '</code>')
                ->addColumn('amount_col', fn ($r) => '$' . number_format($r->amount, 2))
                ->addColumn('net_col', fn ($r) => '$' . number_format($r->net_amount, 2))
                ->addColumn('method_col', fn ($r) => $r->method_label)
                ->addColumn('status_badge', function ($r) {
                    $map = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'completed' => 'primary'];
                    $color = $map[$r->status] ?? 'secondary';
                    return '<span class="badge bg-label-' . $color . '">' . ucfirst($r->status) . '</span>';
                })
                ->addColumn('date_col', fn ($r) => $r->created_at?->format('M d, Y') ?? '—')
                ->addColumn('actions', function ($r) {
                    if ($r->status !== 'pending') return '—';
                    return '<div class="dropdown">
                        <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti tabler-dots-vertical"></i></button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:void(0);" class="dropdown-item btn-approve" data-id="' . $r->id . '"><i class="ti tabler-check me-1"></i> Approve</a>
                            <a href="javascript:void(0);" class="dropdown-item text-danger btn-reject" data-id="' . $r->id . '"><i class="ti tabler-x me-1"></i> Reject</a>
                        </div>
                    </div>';
                })
                ->rawColumns(['trx_col', 'status_badge', 'actions'])
                ->make(true);
        }

        $stats = [
            'pending'   => AffiliateWithdrawal::where('status', 'pending')->sum('amount'),
            'approved'  => AffiliateWithdrawal::where('status', 'approved')->sum('amount'),
            'completed' => AffiliateWithdrawal::where('status', 'completed')->sum('amount'),
            'rejected'  => AffiliateWithdrawal::where('status', 'rejected')->sum('amount'),
        ];

        return view('content.affiliates.withdrawals', compact('stats'));
    }

    public function pending(Request $request)
    {
        return $this->index($request->merge(['status' => 'pending']));
    }

    public function approve(AffiliateWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json(['message' => 'Only pending withdrawals can be approved.'], 422);
        }

        DB::transaction(function () use ($withdrawal) {
            $balance = AffiliateBalance::where('affiliate_id', $withdrawal->affiliate_id)
                ->lockForUpdate()->first();

            if ($balance) {
                $balance->total_paid = bcadd($balance->total_paid, $withdrawal->amount, 2);
                $balance->save();
            }

            $withdrawal->update([
                'status'      => 'approved',
                'approved_at' => now(),
            ]);
        });

        try {
            $withdrawal->affiliate->user->notify(new AffiliateWithdrawalProcessedNotification($withdrawal, 'approved'));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['message' => 'Withdrawal approved.']);
    }

    public function reject(Request $request, AffiliateWithdrawal $withdrawal)
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);

        if ($withdrawal->status !== 'pending') {
            return response()->json(['message' => 'Only pending withdrawals can be rejected.'], 422);
        }

        DB::transaction(function () use ($withdrawal, $request) {
            $balance = AffiliateBalance::where('affiliate_id', $withdrawal->affiliate_id)
                ->lockForUpdate()->first();

            if ($balance) {
                $balance->available_balance = bcadd($balance->available_balance, $withdrawal->amount, 2);
                $balance->save();
            }

            $withdrawal->update([
                'status'           => 'rejected',
                'rejection_reason' => $request->reason,
            ]);
        });

        try {
            $withdrawal->affiliate->user->notify(new AffiliateWithdrawalProcessedNotification($withdrawal, 'rejected'));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['message' => 'Withdrawal rejected. Balance refunded.']);
    }
}
