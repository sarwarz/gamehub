<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateReferral;
use App\Models\AffiliateTier;
use App\Notifications\AffiliateApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Affiliate::with(['user', 'balance']);

            if ($request->filled('status'))  $query->where('status', $request->status);
            if ($request->filled('tier'))    $query->where('tier', $request->tier);

            return DataTables::of($query)
                ->addColumn('checkbox', fn ($r) =>
                    '<input type="checkbox" class="form-check-input row-checkbox" value="' . $r->id . '">')
                ->addColumn('user_col', function ($r) {
                    $name  = e($r->user->name ?? '—');
                    $email = e($r->user->email ?? '');
                    return '<span class="fw-semibold">' . $name . '</span><br><small class="text-muted">' . $email . '</small>';
                })
                ->addColumn('code_col', function ($r) {
                    return '<code>' . e($r->referral_code) . '</code>';
                })
                ->addColumn('tier_badge', function ($r) {
                    $tier = AffiliateTier::where('slug', $r->tier)->first();
                    $color = $tier->color ?? 'secondary';
                    return '<span class="badge bg-label-' . $color . '">' . ucfirst($r->tier) . '</span>';
                })
                ->addColumn('status_badge', function ($r) {
                    $map = ['pending' => 'warning', 'active' => 'success', 'suspended' => 'danger', 'rejected' => 'secondary'];
                    $color = $map[$r->status] ?? 'secondary';
                    return '<span class="badge bg-label-' . $color . '">' . ucfirst($r->status) . '</span>';
                })
                ->addColumn('balance_col', function ($r) {
                    $avail   = number_format($r->balance->available_balance ?? 0, 2);
                    $pending = number_format($r->balance->pending_balance ?? 0, 2);
                    return '<span class="fw-semibold text-success">$' . $avail . '</span><br><small class="text-muted">Pending: $' . $pending . '</small>';
                })
                ->addColumn('earned_col', fn ($r) => '$' . number_format($r->balance->total_earned ?? 0, 2))
                ->addColumn('date_col', fn ($r) => $r->created_at?->format('M d, Y') ?? '—')
                ->addColumn('actions', function ($r) {
                    $url = route('affiliates.show', $r->id);
                    return '<div class="dropdown">
                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="ti tabler-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="' . $url . '" class="dropdown-item"><i class="ti tabler-eye me-1"></i> View</a>
                            ' . ($r->status === 'pending' ? '<a href="javascript:void(0);" class="dropdown-item btn-approve" data-id="' . $r->id . '"><i class="ti tabler-check me-1"></i> Approve</a>' : '') . '
                            ' . ($r->status === 'active' ? '<a href="javascript:void(0);" class="dropdown-item text-danger btn-suspend" data-id="' . $r->id . '"><i class="ti tabler-ban me-1"></i> Suspend</a>' : '') . '
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0);" class="dropdown-item text-danger btn-delete" data-id="' . $r->id . '"><i class="ti tabler-trash me-1"></i> Delete</a>
                        </div>
                    </div>';
                })
                ->rawColumns(['checkbox', 'user_col', 'code_col', 'tier_badge', 'status_badge', 'balance_col', 'actions'])
                ->make(true);
        }

        $stats = [
            'total'     => Affiliate::count(),
            'active'    => Affiliate::where('status', 'active')->count(),
            'pending'   => Affiliate::where('status', 'pending')->count(),
            'suspended' => Affiliate::where('status', 'suspended')->count(),
        ];

        $tiers = AffiliateTier::ordered()->get();

        return view('content.affiliates.index', compact('stats', 'tiers'));
    }

    public function pending(Request $request)
    {
        return $this->index($request->merge(['status' => 'pending']));
    }

    public function show(Affiliate $affiliate)
    {
        $affiliate->load(['user', 'balance', 'tierModel']);

        $commissionStats = [
            'total'     => $affiliate->commissions()->sum('commission_amount'),
            'pending'   => $affiliate->commissions()->whereIn('status', ['pending', 'held'])->sum('commission_amount'),
            'available' => $affiliate->commissions()->where('status', 'available')->sum('commission_amount'),
            'reversed'  => $affiliate->commissions()->where('status', 'reversed')->sum('commission_amount'),
        ];

        $referralStats = [
            'total_clicks'      => $affiliate->referrals()->count(),
            'registrations'     => $affiliate->referrals()->whereIn('status', ['registered', 'converted'])->count(),
            'conversions'       => $affiliate->referrals()->where('status', 'converted')->count(),
            'conversion_rate'   => 0,
        ];

        if ($referralStats['total_clicks'] > 0) {
            $referralStats['conversion_rate'] = round(($referralStats['conversions'] / $referralStats['total_clicks']) * 100, 2);
        }

        $recentCommissions = $affiliate->commissions()
            ->with('order')
            ->latest()
            ->limit(10)
            ->get();

        $recentReferrals = $affiliate->referrals()
            ->with('referredUser')
            ->latest()
            ->limit(10)
            ->get();

        $tiers = AffiliateTier::ordered()->get();

        return view('content.affiliates.show', compact(
            'affiliate', 'commissionStats', 'referralStats',
            'recentCommissions', 'recentReferrals', 'tiers'
        ));
    }

    public function approve(Affiliate $affiliate)
    {
        if ($affiliate->status !== 'pending') {
            return response()->json(['message' => 'Only pending affiliates can be approved.'], 422);
        }

        $affiliate->update([
            'status'      => 'active',
            'approved_at' => now(),
        ]);

        try {
            $affiliate->user->notify(new AffiliateApprovedNotification($affiliate));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['message' => 'Affiliate approved successfully.']);
    }

    public function reject(Request $request, Affiliate $affiliate)
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);

        if ($affiliate->status !== 'pending') {
            return response()->json(['message' => 'Only pending affiliates can be rejected.'], 422);
        }

        $affiliate->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Affiliate rejected.']);
    }

    public function suspend(Affiliate $affiliate)
    {
        if ($affiliate->status !== 'active') {
            return response()->json(['message' => 'Only active affiliates can be suspended.'], 422);
        }

        $affiliate->update([
            'status'       => 'suspended',
            'suspended_at' => now(),
        ]);

        return response()->json(['message' => 'Affiliate suspended.']);
    }

    public function reactivate(Affiliate $affiliate)
    {
        if (!in_array($affiliate->status, ['suspended', 'rejected'])) {
            return response()->json(['message' => 'Cannot reactivate this affiliate.'], 422);
        }

        $affiliate->update([
            'status'           => 'active',
            'approved_at'      => $affiliate->approved_at ?? now(),
            'suspended_at'     => null,
            'rejection_reason' => null,
        ]);

        return response()->json(['message' => 'Affiliate reactivated.']);
    }

    public function updateTier(Request $request, Affiliate $affiliate)
    {
        $request->validate(['tier' => 'required|exists:affiliate_tiers,slug']);
        $affiliate->update(['tier' => $request->tier]);
        return response()->json(['message' => 'Tier updated to ' . ucfirst($request->tier) . '.']);
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:affiliates,id',
            'status' => 'required|in:' . implode(',', Affiliate::STATUSES),
        ]);

        $data = ['status' => $request->status];
        if ($request->status === 'active')    $data['approved_at'] = now();
        if ($request->status === 'suspended') $data['suspended_at'] = now();

        Affiliate::whereIn('id', $request->ids)->update($data);

        return response()->json(['message' => count($request->ids) . ' affiliates updated.']);
    }

    public function destroy(Affiliate $affiliate)
    {
        $affiliate->delete();
        return response()->json(['message' => 'Affiliate deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:affiliates,id',
        ]);

        Affiliate::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => count($request->ids) . ' affiliates deleted.']);
    }
}
