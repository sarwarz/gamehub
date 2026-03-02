<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCommission;
use App\Services\AffiliateBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AffiliateCommissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = AffiliateCommission::with(['affiliate.user', 'order']);

            if ($request->filled('status'))       $query->where('status', $request->status);
            if ($request->filled('affiliate_id'))  $query->where('affiliate_id', $request->affiliate_id);
            if ($request->filled('level'))         $query->where('level', $request->level);

            return DataTables::of($query)
                ->addColumn('affiliate_col', function ($r) {
                    return e($r->affiliate?->user?->name ?? '—') . '<br><small class="text-muted"><code>' . e($r->affiliate?->referral_code ?? '') . '</code></small>';
                })
                ->addColumn('order_col', function ($r) {
                    if (!$r->order) return '—';
                    $url = route('orders.show', $r->order->id);
                    return '<a href="' . $url . '">#' . e($r->order->order_number) . '</a>';
                })
                ->addColumn('amount_col', fn ($r) => '$' . number_format($r->commission_amount, 2))
                ->addColumn('rate_col', fn ($r) => $r->commission_rate . '%')
                ->addColumn('level_badge', function ($r) {
                    $color = $r->level === 'l1' ? 'primary' : 'info';
                    return '<span class="badge bg-label-' . $color . '">' . strtoupper($r->level) . '</span>';
                })
                ->addColumn('status_badge', function ($r) {
                    $map = ['pending' => 'warning', 'held' => 'info', 'available' => 'success', 'paid' => 'primary', 'reversed' => 'danger'];
                    $color = $map[$r->status] ?? 'secondary';
                    return '<span class="badge bg-label-' . $color . '">' . ucfirst($r->status) . '</span>';
                })
                ->addColumn('date_col', fn ($r) => $r->created_at?->format('M d, Y') ?? '—')
                ->addColumn('actions', function ($r) {
                    $actions = '';
                    if ($r->status === 'held') {
                        $actions .= '<a href="javascript:void(0);" class="dropdown-item btn-release" data-id="' . $r->id . '"><i class="ti tabler-lock-open me-1"></i> Release</a>';
                    }
                    if (in_array($r->status, ['pending', 'held', 'available'])) {
                        $actions .= '<a href="javascript:void(0);" class="dropdown-item text-danger btn-reverse" data-id="' . $r->id . '"><i class="ti tabler-arrow-back me-1"></i> Reverse</a>';
                    }
                    if (!$actions) return '—';
                    return '<div class="dropdown">
                        <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti tabler-dots-vertical"></i></button>
                        <div class="dropdown-menu dropdown-menu-end">' . $actions . '</div>
                    </div>';
                })
                ->rawColumns(['affiliate_col', 'order_col', 'level_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        $stats = [
            'total_earned' => AffiliateCommission::whereIn('status', ['pending', 'held', 'available', 'paid'])->sum('commission_amount'),
            'pending'      => AffiliateCommission::whereIn('status', ['pending', 'held'])->sum('commission_amount'),
            'available'    => AffiliateCommission::where('status', 'available')->sum('commission_amount'),
            'reversed'     => AffiliateCommission::where('status', 'reversed')->sum('commission_amount'),
        ];

        return view('content.affiliates.commissions', compact('stats'));
    }

    public function release(AffiliateCommission $commission)
    {
        if ($commission->status !== 'held') {
            return response()->json(['message' => 'Only held commissions can be released.'], 422);
        }

        DB::transaction(function () use ($commission) {
            $commission->update(['status' => 'available', 'available_at' => now()]);
            AffiliateBalanceService::moveHeldToAvailable($commission->affiliate_id, (float) $commission->commission_amount);
        });

        return response()->json(['message' => 'Commission released.']);
    }

    public function reverse(Request $request, AffiliateCommission $commission)
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);

        if (!in_array($commission->status, ['pending', 'held', 'available'])) {
            return response()->json(['message' => 'This commission cannot be reversed.'], 422);
        }

        DB::transaction(function () use ($commission, $request) {
            $oldStatus = $commission->status;

            $commission->update([
                'status'          => 'reversed',
                'reversed_at'     => now(),
                'reversal_reason' => $request->reason ?? 'Admin reversal',
            ]);

            if ($oldStatus === 'available') {
                AffiliateBalanceService::deductAvailable($commission->affiliate_id, (float) $commission->commission_amount);
                $commission->affiliate->balance?->increment('total_reversed', $commission->commission_amount);
            } else {
                AffiliateBalanceService::reverse($commission->affiliate_id, (float) $commission->commission_amount);
            }
        });

        return response()->json(['message' => 'Commission reversed.']);
    }
}
