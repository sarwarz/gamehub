<?php

namespace App\Http\Controllers;

use App\Models\RefundRequest;
use App\Models\Order;
use App\Events\OrderRefunded;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = RefundRequest::with(['user:id,name,email', 'order:id,order_number,total_amount', 'seller:id,store_name'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
                ->latest();

            return DataTables::eloquent($query)
                ->addColumn('customer', fn ($r) => $r->user?->name ?? 'N/A')
                ->addColumn('order_number', fn ($r) => $r->order?->order_number ?? 'N/A')
                ->addColumn('seller_name', fn ($r) => $r->seller?->store_name ?? 'N/A')
                ->addColumn('formatted_amount', fn ($r) => format_currency($r->amount))
                ->addColumn('status_badge', function ($r) {
                    $colors = [
                        'pending'    => 'warning',
                        'approved'   => 'info',
                        'rejected'   => 'danger',
                        'processing' => 'primary',
                        'completed'  => 'success',
                    ];
                    $color = $colors[$r->status] ?? 'secondary';
                    return '<span class="badge bg-label-' . $color . '">' . ucfirst($r->status) . '</span>';
                })
                ->addColumn('actions', function ($r) {
                    $showUrl = route('refunds.show', $r->id);
                    return '<div class="d-flex gap-1 justify-content-center">
                        <a href="' . $showUrl . '" class="btn btn-icon btn-sm btn-label-primary" title="View">
                            <i class="ti tabler-eye ti-xs"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        $stats = [
            'total'     => RefundRequest::count(),
            'pending'   => RefundRequest::where('status', 'pending')->count(),
            'approved'  => RefundRequest::where('status', 'approved')->count(),
            'rejected'  => RefundRequest::where('status', 'rejected')->count(),
            'completed' => RefundRequest::where('status', 'completed')->count(),
            'amount'    => RefundRequest::whereIn('status', ['approved', 'completed'])->sum('amount'),
        ];

        return view('content.refunds.index', compact('stats'));
    }

    public function show($id)
    {
        $refund = RefundRequest::with([
            'user:id,name,email',
            'order' => fn ($q) => $q->with(['items.product:id,title,slug,image', 'billingAddress']),
            'orderItem.product:id,title,slug,image',
            'seller:id,store_name,slug',
            'processor:id,name',
        ])->findOrFail($id);

        return view('content.refunds.show', compact('refund'));
    }

    public function approve(Request $request, $id)
    {
        $refund = RefundRequest::findOrFail($id);

        if ($refund->status !== 'pending') {
            return response()->json(['message' => 'Only pending refunds can be approved.'], 422);
        }

        $refund->update([
            'status'       => 'approved',
            'admin_note'   => $request->admin_note,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        try {
            if (\App\Models\Setting::get('refund_notifications', 'customer_on_approved', true)) {
                $refund->load('order', 'user');
                if ($refund->user) {
                    $refund->user->notify(new \App\Notifications\RefundStatusNotification($refund, 'approved'));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Refund approval notification failed: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Refund request approved.']);
    }

    public function reject(Request $request, $id)
    {
        $refund = RefundRequest::findOrFail($id);

        if ($refund->status !== 'pending') {
            return response()->json(['message' => 'Only pending refunds can be rejected.'], 422);
        }

        $request->validate(['admin_note' => 'required|string|max:1000']);

        $refund->update([
            'status'       => 'rejected',
            'admin_note'   => $request->admin_note,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        try {
            if (\App\Models\Setting::get('refund_notifications', 'customer_on_rejected', true)) {
                $refund->load('order', 'user');
                if ($refund->user) {
                    $refund->user->notify(new \App\Notifications\RefundStatusNotification($refund, 'rejected'));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Refund rejection notification failed: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Refund request rejected.']);
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|in:wallet,original',
        ]);

        $refundSettings = \App\Models\Setting::group('refund_escrow');
        if ($request->method === 'wallet' && isset($refundSettings['refund_to_wallet_enabled']) && !$refundSettings['refund_to_wallet_enabled']) {
            return response()->json(['message' => 'Refund to wallet is currently disabled.'], 422);
        }
        if ($request->method === 'original' && isset($refundSettings['refund_to_original_enabled']) && !$refundSettings['refund_to_original_enabled']) {
            return response()->json(['message' => 'Refund to original payment method is currently disabled.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($request, $id) {
                $refund = RefundRequest::where('id', $id)->lockForUpdate()->first();

                if (!$refund) {
                    throw new \RuntimeException('Refund request not found.', 404);
                }

                if ($refund->status !== 'approved') {
                    throw new \RuntimeException('Only approved refunds can be processed.', 422);
                }

                $refund->load('order', 'user');

                if ($refund->order && in_array($refund->order->status, ['cancelled'])) {
                    throw new \RuntimeException('Cannot process refund for a cancelled order.', 422);
                }

                $refund->update(['status' => 'processing']);

                if ($request->method === 'wallet') {
                    $walletService = app(WalletService::class);
                    $wallet = $refund->user->wallet;

                    if ($wallet) {
                        $walletService->credit(
                            $wallet,
                            $refund->amount,
                            "Refund for Order #{$refund->order->order_number}",
                            'refund',
                            $refund->order
                        );
                    }
                }

                $refund->update([
                    'status'       => 'completed',
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);

                $order = $refund->order;
                if ($refund->type === 'full') {
                    $order->update([
                        'status'         => 'refunded',
                        'payment_status' => 'refunded',
                        'refunded_at'    => now(),
                    ]);
                }

                return ['refund' => $refund, 'order' => $order];
            });

            event(new OrderRefunded($result['order'], (float) $result['refund']->amount));

            try {
                if (\App\Models\Setting::get('refund_notifications', 'customer_on_completed', true) && $result['refund']->user) {
                    $result['refund']->user->notify(new \App\Notifications\RefundStatusNotification($result['refund'], 'completed'));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Refund completed notification failed: ' . $e->getMessage());
            }

            return response()->json(['message' => 'Refund processed successfully.']);
        } catch (\RuntimeException $e) {
            $code = $e->getCode() ?: 422;
            return response()->json(['message' => $e->getMessage()], $code);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to process refund: ' . $e->getMessage()], 500);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'action' => 'required|in:approve,reject',
        ]);

        $refunds = RefundRequest::whereIn('id', $request->ids)->where('status', 'pending')->get();
        $count = 0;

        foreach ($refunds as $refund) {
            $newStatus = $request->action === 'approve' ? 'approved' : 'rejected';
            $refund->update([
                'status'       => $newStatus,
                'admin_note'   => $request->admin_note ?? ($request->action === 'approve' ? 'Bulk approved' : 'Bulk rejected'),
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
            $count++;

            try {
                $settingKey = $newStatus === 'approved' ? 'customer_on_approved' : 'customer_on_rejected';
                if (\App\Models\Setting::get('refund_notifications', $settingKey, true)) {
                    $refund->load('order', 'user');
                    if ($refund->user) {
                        $refund->user->notify(new \App\Notifications\RefundStatusNotification($refund, $newStatus));
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Bulk refund {$newStatus} notification failed: " . $e->getMessage());
            }
        }

        return response()->json(['message' => "{$count} refund(s) {$request->action}d successfully."]);
    }
}
