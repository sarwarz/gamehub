<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SellerWithdraw;
use App\Services\SellerBalanceService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SellerWithdrawController extends Controller
{
    public function __construct(
        protected SellerBalanceService $balanceService
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SellerWithdraw::with(['seller.user']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return $this->withdrawDataTable($query);
        }

        $stats = [
            'total'    => SellerWithdraw::count(),
            'pending'  => SellerWithdraw::where('status', 'pending')->count(),
            'approved' => SellerWithdraw::where('status', 'approved')->count(),
            'rejected' => SellerWithdraw::where('status', 'rejected')->count(),
            'amount'   => SellerWithdraw::where('status', 'approved')->sum('amount'),
        ];

        return view('content.seller_withdraws.index', compact('stats'));
    }

    public function pending(Request $request)
    {
        if ($request->ajax()) {
            return $this->withdrawDataTable(
                SellerWithdraw::where('status', 'pending')
                    ->with(['seller.user'])
            );
        }

        $stats = [
            'pending_count'  => SellerWithdraw::where('status', 'pending')->count(),
            'pending_amount' => SellerWithdraw::where('status', 'pending')->sum('amount'),
        ];

        return view('content.seller_withdraws.pending', compact('stats'));
    }

    public function show($id)
    {
        $withdraw = SellerWithdraw::with(['seller.user', 'seller.balance'])->findOrFail($id);

        return view('content.seller_withdraws.show', compact('withdraw'));
    }

    public function approve(Request $request, $id)
    {
        $withdraw = SellerWithdraw::findOrFail($id);

        try {
            $this->balanceService->approveWithdrawal($withdraw);

            $withdraw->update([
                'admin_note'     => $request->admin_note,
                'transaction_id' => $request->transaction_id,
                'processed_at'   => now(),
            ]);

            try {
                if (Setting::get('notifications', 'withdrawal_approved', true)) {
                    $withdraw->load('seller.user');
                    $withdraw->seller->user->notify(new \App\Notifications\Seller\WithdrawalStatusNotification($withdraw, 'approved'));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Withdrawal approval notification failed: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Withdraw approved and balance deducted successfully.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reject(Request $request, $id)
    {
        $withdraw = SellerWithdraw::findOrFail($id);

        try {
            $this->balanceService->rejectWithdrawal($withdraw, $request->note);

            $withdraw->update([
                'admin_note'   => $request->admin_note ?? $request->note,
                'processed_at' => now(),
            ]);

            try {
                if (Setting::get('notifications', 'withdrawal_rejected', true)) {
                    $withdraw->load('seller.user');
                    $withdraw->seller->user->notify(new \App\Notifications\Seller\WithdrawalStatusNotification($withdraw, 'rejected'));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Withdrawal rejection notification failed: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Withdraw rejected.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function withdrawDataTable($query)
    {
        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">';
            })

            ->addColumn('seller', function ($row) {
                $seller = $row->seller;
                $avatar = $seller?->logo
                    ? asset($seller->logo)
                    : asset('assets/img/avatars/1.png');

                $name = e($seller->store_name ?? 'N/A');
                $email = e($seller->user->email ?? 'N/A');

                return '
                <div class="d-flex align-items-center">
                    <img src="'.$avatar.'" class="rounded-circle me-3" width="38" height="38" style="object-fit:cover">
                    <div class="lh-sm">
                        <span class="fw-semibold text-body">'.$name.'</span>
                        <div class="text-muted small">'.$email.'</div>
                    </div>
                </div>';
            })

            ->addColumn('amount', function ($row) {
                return '<span class="fw-semibold">'.format_currency($row->amount).'</span>';
            })

            ->addColumn('method', function ($row) {
                $icons = [
                    'paypal'   => 'brand-paypal',
                    'bank'     => 'building-bank',
                    'crypto'   => 'currency-bitcoin',
                    'bkash'    => 'device-mobile',
                    'nagad'    => 'device-mobile',
                    'wise'     => 'arrows-exchange',
                    'payoneer' => 'credit-card',
                    'skrill'   => 'wallet',
                ];
                $icon = $icons[$row->method] ?? 'cash';
                return '<span class="badge bg-label-info"><i class="ti tabler-'.$icon.' me-1"></i>'.ucfirst($row->method).'</span>';
            })

            ->addColumn('status_badge', function ($row) {
                $map = [
                    'pending'   => 'warning',
                    'approved'  => 'success',
                    'rejected'  => 'danger',
                    'cancelled' => 'secondary',
                ];
                $class = $map[$row->status] ?? 'secondary';
                return '<span class="badge bg-label-'.$class.'">'.ucfirst($row->status).'</span>';
            })

            ->addColumn('created_at', fn($row) =>
                $row->created_at->format('d M Y')
            )

            ->addColumn('actions', function ($row) {
                $viewBtn = '<a href="'.route('seller-withdraws.show', $row->id).'" class="btn btn-icon btn-sm btn-label-primary" title="View Details">
                    <i class="ti tabler-eye ti-xs"></i>
                </a>';

                if ($row->status !== 'pending') {
                    return '<div class="d-flex align-items-center justify-content-center gap-1">'.$viewBtn.'</div>';
                }

                return '<div class="d-flex align-items-center justify-content-center gap-1">
                    '.$viewBtn.'
                    <button type="button" class="btn btn-icon btn-sm btn-label-success approve-btn" data-url="'.route('seller-withdraws.approve', $row->id).'" title="Approve">
                        <i class="ti tabler-check ti-xs"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-label-danger reject-btn" data-url="'.route('seller-withdraws.reject', $row->id).'" title="Reject">
                        <i class="ti tabler-x ti-xs"></i>
                    </button>
                </div>';
            })

            ->rawColumns(['checkbox', 'seller', 'amount', 'method', 'status_badge', 'actions'])
            ->make(true);
    }
}
