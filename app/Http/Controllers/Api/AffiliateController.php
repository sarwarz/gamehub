<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateTier;
use App\Models\Setting;
use App\Services\AffiliateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Affiliate Program
 *
 * Manage the authenticated user's affiliate account including
 * applying, viewing dashboard, commissions, referrals, and withdrawals.
 *
 * @authenticated
 */
class AffiliateController extends Controller
{
    /**
     * Apply to become an affiliate
     *
     * @bodyParam bio string Brief description about yourself. Example: I run a gaming blog with 50k visitors/month.
     * @bodyParam website string Your website URL. Example: https://mygamingblog.com
     * @bodyParam social_media string Social media profile. Example: https://twitter.com/gamer
     * @bodyParam payment_method string Preferred payout method. Example: wallet
     * @bodyParam payment_details object Payment details (bank info, paypal email, etc.).
     *
     * @response 201 { "status": true, "message": "Application submitted successfully.", "data": { "id": 1, "referral_code": "AB12CD34", "status": "pending" } }
     */
    public function apply(Request $request): JsonResponse
    {
        if (!AffiliateService::isEnabled()) {
            return $this->error('Affiliate program is currently disabled.', 422);
        }

        $user = $request->user();

        if ($user->affiliate) {
            return $this->error('You already have an affiliate account.', 422);
        }

        $request->validate([
            'bio'             => 'nullable|string|max:2000',
            'website'         => 'nullable|url|max:255',
            'social_media'    => 'nullable|string|max:255',
            'payment_method'  => 'nullable|string|max:50',
            'payment_details' => 'nullable|array',
        ]);

        $autoApprove = (bool) Setting::get('affiliate', 'auto_approve', false);
        $defaultTier = AffiliateTier::where('is_default', true)->first();

        $affiliate = Affiliate::create([
            'user_id'         => $user->id,
            'status'          => $autoApprove ? 'active' : 'pending',
            'tier'            => $defaultTier->slug ?? 'bronze',
            'bio'             => $request->bio,
            'website'         => $request->website,
            'social_media'    => $request->social_media,
            'payment_method'  => $request->payment_method,
            'payment_details' => $request->payment_details,
            'approved_at'     => $autoApprove ? now() : null,
        ]);

        return $this->success($affiliate, 'Application submitted successfully.', 201);
    }

    /**
     * Check application status
     *
     * @response 200 { "status": true, "message": "Affiliate status", "data": { "id": 1, "status": "active", "referral_code": "AB12CD34", "tier": "silver" } }
     * @response 404 { "status": false, "message": "No affiliate account found." }
     */
    public function status(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;

        if (!$affiliate) {
            return $this->error('No affiliate account found.', 404);
        }

        return $this->success($affiliate->only(['id', 'referral_code', 'status', 'tier', 'approved_at', 'rejection_reason']), 'Affiliate status');
    }

    /**
     * Affiliate dashboard
     *
     * Returns affiliate stats overview with balance, click/conversion metrics, and recent activity.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate || !$affiliate->isActive()) {
            return $this->error('Affiliate account not active.', 403);
        }

        $affiliate->load('balance');

        $totalClicks   = $affiliate->referrals()->count();
        $registrations = $affiliate->referrals()->whereIn('status', ['registered', 'converted'])->count();
        $conversions   = $affiliate->referrals()->where('status', 'converted')->count();

        $thisMonth = $affiliate->commissions()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('commission_amount');

        $lastMonth = $affiliate->commissions()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('commission_amount');

        $recentCommissions = $affiliate->commissions()
            ->with('order:id,order_number,total_amount')
            ->latest()
            ->limit(5)
            ->get(['id', 'order_id', 'commission_amount', 'commission_rate', 'level', 'status', 'created_at']);

        $monthlyData = $affiliate->commissions()
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(commission_amount) as earnings'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $this->success([
            'affiliate' => [
                'referral_code' => $affiliate->referral_code,
                'status'        => $affiliate->status,
                'tier'          => $affiliate->tier,
                'referral_url'  => $affiliate->referralUrl(),
                'approved_at'   => $affiliate->approved_at,
            ],
            'balance' => [
                'available' => $affiliate->balance->available_balance ?? 0,
                'pending'   => $affiliate->balance->pending_balance ?? 0,
                'total_earned' => $affiliate->balance->total_earned ?? 0,
                'total_paid'   => $affiliate->balance->total_paid ?? 0,
            ],
            'stats' => [
                'total_clicks'       => $totalClicks,
                'total_registrations' => $registrations,
                'total_conversions'  => $conversions,
                'conversion_rate'    => $totalClicks > 0 ? round(($conversions / $totalClicks) * 100, 2) : 0,
                'this_month_earnings' => $thisMonth,
                'last_month_earnings' => $lastMonth,
            ],
            'recent_commissions' => $recentCommissions,
            'chart_data'         => $monthlyData,
        ], 'Dashboard loaded');
    }

    /**
     * Get referral link
     */
    public function referralLink(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate || !$affiliate->isActive()) {
            return $this->error('Affiliate account not active.', 403);
        }

        return $this->success([
            'referral_code' => $affiliate->referral_code,
            'referral_url'  => $affiliate->referralUrl(),
        ], 'Referral link');
    }

    /**
     * List commissions
     *
     * @queryParam status string Filter by status (pending,held,available,paid,reversed). Example: available
     * @queryParam per_page int Items per page. Example: 15
     */
    public function commissions(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate || !$affiliate->isActive()) {
            return $this->error('Affiliate account not active.', 403);
        }

        $query = $affiliate->commissions()->with('order:id,order_number,total_amount');

        if ($request->filled('status')) {
            $request->validate(['status' => 'in:' . implode(',', AffiliateCommission::STATUSES)]);
            $query->where('status', $request->status);
        }

        $commissions = $query->latest()->paginate(min($request->integer('per_page', 15), 100));

        return $this->success($commissions, 'Commissions fetched');
    }

    /**
     * List referrals
     *
     * @queryParam status string Filter by status (clicked,registered,converted). Example: converted
     * @queryParam per_page int Items per page. Example: 15
     */
    public function referrals(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate || !$affiliate->isActive()) {
            return $this->error('Affiliate account not active.', 403);
        }

        $query = $affiliate->referrals()
            ->select(['id', 'affiliate_id', 'status', 'referral_source', 'landing_page', 'registered_at', 'converted_at', 'created_at']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $referrals = $query->latest()->paginate(min($request->integer('per_page', 15), 100));

        return $this->success($referrals, 'Referrals fetched');
    }

    /**
     * Get balance
     */
    public function balance(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate) {
            return $this->error('No affiliate account found.', 404);
        }

        $affiliate->load('balance');

        return $this->success([
            'available'      => $affiliate->balance->available_balance ?? 0,
            'pending'        => $affiliate->balance->pending_balance ?? 0,
            'total_earned'   => $affiliate->balance->total_earned ?? 0,
            'total_paid'     => $affiliate->balance->total_paid ?? 0,
            'total_reversed' => $affiliate->balance->total_reversed ?? 0,
        ], 'Balance fetched');
    }

    /**
     * List withdrawals
     *
     * @queryParam per_page int Items per page. Example: 15
     */
    public function withdrawals(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate || !$affiliate->isActive()) {
            return $this->error('Affiliate account not active.', 403);
        }

        $withdrawals = $affiliate->withdrawals()
            ->latest()
            ->paginate(min($request->integer('per_page', 15), 100));

        return $this->success($withdrawals, 'Withdrawals fetched');
    }

    /**
     * Request a withdrawal
     *
     * @bodyParam amount number required Withdrawal amount. Example: 100.00
     * @bodyParam payment_method string required Payout method. Example: wallet
     * @bodyParam payment_details object Payment details for non-wallet methods.
     */
    public function withdraw(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate || !$affiliate->isActive()) {
            return $this->error('Affiliate account not active.', 403);
        }

        $request->validate([
            'amount'          => 'required|numeric|min:1',
            'payment_method'  => 'required|string|max:50',
            'payment_details' => 'nullable|array',
        ]);

        $amount = (float) $request->amount;
        $minWithdrawal = (float) Setting::get('affiliate', 'min_withdrawal', 50.00);

        if ($amount < $minWithdrawal) {
            return $this->error("Minimum withdrawal amount is \${$minWithdrawal}.", 422);
        }

        $balance = $affiliate->balance;
        if (!$balance || $balance->available_balance < $amount) {
            return $this->error('Insufficient balance.', 422);
        }

        if ($request->payment_method === 'wallet') {
            try {
                $transaction = AffiliateService::processWithdrawalToWallet($affiliate, $amount);
                return $this->success(['wallet_transaction_id' => $transaction->id], 'Transfer to wallet completed.');
            } catch (\RuntimeException $e) {
                return $this->error($e->getMessage(), 422);
            }
        }

        $fee = (float) Setting::get('affiliate', 'withdrawal_fee', 0);
        $netAmount = bcsub($amount, $fee, 2);

        try {
            DB::transaction(function () use ($affiliate, $amount, $fee, $netAmount, $request) {
                $balance = \App\Models\AffiliateBalance::where('affiliate_id', $affiliate->id)
                    ->lockForUpdate()->first();

                if (!$balance || bccomp($balance->available_balance, (string) $amount, 2) < 0) {
                    throw new \RuntimeException('Insufficient balance.');
                }

                $balance->available_balance = bcsub($balance->available_balance, $amount, 2);
                $balance->save();

                \App\Models\AffiliateWithdrawal::create([
                    'affiliate_id'   => $affiliate->id,
                    'amount'         => $amount,
                    'fee'            => $fee,
                    'net_amount'     => $netAmount,
                    'payment_method' => $request->payment_method,
                    'payment_details' => $request->payment_details,
                    'status'         => 'pending',
                ]);
            });

            return $this->success(null, 'Withdrawal request submitted. Pending admin approval.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to process withdrawal.', 500);
        }
    }

    /**
     * Transfer to wallet
     *
     * @bodyParam amount number required Amount to transfer. Example: 50.00
     */
    public function transferToWallet(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate || !$affiliate->isActive()) {
            return $this->error('Affiliate account not active.', 403);
        }

        $request->validate(['amount' => 'required|numeric|min:1']);

        try {
            $transaction = AffiliateService::processWithdrawalToWallet($affiliate, (float) $request->amount);
            return $this->success([
                'wallet_transaction_id' => $transaction->id,
                'balance_after'         => $transaction->balance_after,
            ], 'Transfer to wallet completed.');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Transfer failed.', 500);
        }
    }

    /**
     * Update affiliate profile
     *
     * @bodyParam bio string Bio text. Example: Gaming influencer
     * @bodyParam website string Website URL. Example: https://example.com
     * @bodyParam social_media string Social media link. Example: https://twitter.com/user
     * @bodyParam payment_method string Preferred payout method. Example: paypal
     * @bodyParam payment_details object Payment details.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate) {
            return $this->error('No affiliate account found.', 404);
        }

        $request->validate([
            'bio'             => 'nullable|string|max:2000',
            'website'         => 'nullable|url|max:255',
            'social_media'    => 'nullable|string|max:255',
            'payment_method'  => 'nullable|string|max:50',
            'payment_details' => 'nullable|array',
        ]);

        $affiliate->update($request->only(['bio', 'website', 'social_media', 'payment_method', 'payment_details']));

        return $this->success($affiliate->fresh(), 'Profile updated.');
    }

    /**
     * Analytics data (charts)
     */
    public function analytics(Request $request): JsonResponse
    {
        $affiliate = $request->user()->affiliate;
        if (!$affiliate || !$affiliate->isActive()) {
            return $this->error('Affiliate account not active.', 403);
        }

        $months = min((int) ($request->months ?? 6), 24);
        $since = now()->subMonths($months)->startOfMonth();

        $commissionsByMonth = $affiliate->commissions()
            ->where('created_at', '>=', $since)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(commission_amount) as earnings'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $clicksByMonth = $affiliate->referrals()
            ->where('created_at', '>=', $since)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as clicks'),
                DB::raw("SUM(CASE WHEN status IN ('registered','converted') THEN 1 ELSE 0 END) as registrations"),
                DB::raw("SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as conversions")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $topPages = $affiliate->referrals()
            ->whereNotNull('landing_page')
            ->where('created_at', '>=', $since)
            ->select('landing_page', DB::raw('COUNT(*) as clicks'))
            ->groupBy('landing_page')
            ->orderByDesc('clicks')
            ->limit(10)
            ->get();

        return $this->success([
            'commissions_by_month' => $commissionsByMonth,
            'clicks_by_month'     => $clicksByMonth,
            'top_landing_pages'   => $topPages,
        ], 'Analytics data');
    }

    /**
     * Get program info & tiers
     */
    public function programInfo(): JsonResponse
    {
        $tiers = AffiliateTier::ordered()->get(['name', 'slug', 'commission_rate', 'l2_commission_rate', 'min_earnings_threshold', 'min_referrals', 'min_conversions', 'color']);

        return $this->success([
            'is_enabled'      => AffiliateService::isEnabled(),
            'cookie_days'     => (int) Setting::get('affiliate', 'cookie_duration_days', 30),
            'hold_days'       => (int) Setting::get('affiliate', 'hold_period_days', 14),
            'min_withdrawal'  => (float) Setting::get('affiliate', 'min_withdrawal', 50),
            'payout_methods'  => Setting::get('affiliate', 'payout_methods', ['wallet']),
            'tiers'           => $tiers,
        ], 'Program info');
    }
}
