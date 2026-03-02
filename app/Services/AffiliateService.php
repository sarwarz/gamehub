<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateBalance;
use App\Models\AffiliateCommission;
use App\Models\AffiliateReferral;
use App\Models\AffiliateTier;
use App\Models\AffiliateWithdrawal;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AffiliateService
{
    public static function isEnabled(): bool
    {
        return (bool) Setting::get('affiliate', 'is_enabled', false);
    }

    /**
     * Record a click from a referral link.
     */
    public static function trackClick(string $referralCode, string $ip, ?string $userAgent = null, ?string $landingPage = null): ?AffiliateReferral
    {
        if (!self::isEnabled()) return null;

        $affiliate = Affiliate::where('referral_code', $referralCode)->active()->first();
        if (!$affiliate) return null;

        $recentClick = AffiliateReferral::where('affiliate_id', $affiliate->id)
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($recentClick) return null;

        return AffiliateReferral::create([
            'affiliate_id'    => $affiliate->id,
            'ip_address'      => $ip,
            'user_agent'      => $userAgent ? substr($userAgent, 0, 500) : null,
            'landing_page'    => $landingPage ? substr($landingPage, 0, 500) : null,
            'referral_source' => 'link',
            'status'          => 'clicked',
        ]);
    }

    /**
     * Called during user registration when a referral code/cookie is present.
     */
    public static function trackRegistration(User $user, string $referralCode): ?AffiliateReferral
    {
        if (!self::isEnabled()) return null;

        $affiliate = Affiliate::where('referral_code', $referralCode)->active()->first();
        if (!$affiliate) return null;

        if (!self::isValidReferral($referralCode, null, $user->id)) return null;

        $referral = AffiliateReferral::where('affiliate_id', $affiliate->id)
            ->where('status', 'clicked')
            ->whereNull('referred_user_id')
            ->latest()
            ->first();

        if ($referral) {
            $referral->update([
                'referred_user_id' => $user->id,
                'status'           => 'registered',
                'registered_at'    => now(),
            ]);
        } else {
            $referral = AffiliateReferral::create([
                'affiliate_id'    => $affiliate->id,
                'referred_user_id' => $user->id,
                'referral_source' => 'link',
                'status'          => 'registered',
                'registered_at'   => now(),
            ]);
        }

        return $referral;
    }

    /**
     * Process affiliate commissions when an order is paid.
     */
    public static function processOrderCommission(Order $order): void
    {
        if (!self::isEnabled()) return;

        $order->loadMissing('user');
        $user = $order->user;
        if (!$user) return;

        $referral = AffiliateReferral::where('referred_user_id', $user->id)
            ->whereIn('status', ['registered', 'converted'])
            ->first();

        if (!$referral) return;

        $affiliate = $referral->affiliate;
        if (!$affiliate || !$affiliate->isActive()) return;

        if (AffiliateCommission::where('order_id', $order->id)->where('affiliate_id', $affiliate->id)->exists()) {
            return;
        }

        $basis = Setting::get('affiliate', 'commission_basis', 'net');
        $orderAmount = $basis === 'gross' ? (float) $order->total_amount : (float) ($order->total_amount - ($order->tax_amount ?? 0));

        $rate = $affiliate->getCommissionRate();
        $commissionAmount = round($orderAmount * ($rate / 100), 2);

        if ($commissionAmount <= 0) return;

        DB::transaction(function () use ($affiliate, $order, $referral, $orderAmount, $rate, $commissionAmount) {
            AffiliateCommission::create([
                'affiliate_id'      => $affiliate->id,
                'order_id'          => $order->id,
                'referral_id'       => $referral->id,
                'order_amount'      => $orderAmount,
                'commission_rate'   => $rate,
                'commission_amount' => $commissionAmount,
                'level'             => 'l1',
                'status'            => 'pending',
            ]);

            AffiliateBalanceService::addPending($affiliate->id, $commissionAmount);

            if ($referral->status === 'registered') {
                $referral->update(['status' => 'converted', 'converted_at' => now()]);
            }
        });

        if ((bool) Setting::get('affiliate', 'allow_l2_commissions', false)) {
            self::processL2Commission($affiliate, $order, $orderAmount);
        }

        try {
            $affiliate->user->notify(new \App\Notifications\AffiliateCommissionEarnedNotification($affiliate, $order, $commissionAmount));
        } catch (\Throwable $e) {
            Log::warning('Affiliate commission notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Process level-2 commission (affiliate who referred the affiliate).
     */
    protected static function processL2Commission(Affiliate $l1Affiliate, Order $order, float $orderAmount): void
    {
        $l1User = $l1Affiliate->user;
        $l1Referral = AffiliateReferral::where('referred_user_id', $l1User->id)
            ->whereIn('status', ['registered', 'converted'])
            ->first();

        if (!$l1Referral) return;

        $l2Affiliate = $l1Referral->affiliate;
        if (!$l2Affiliate || !$l2Affiliate->isActive()) return;

        if (AffiliateCommission::where('order_id', $order->id)->where('affiliate_id', $l2Affiliate->id)->where('level', 'l2')->exists()) {
            return;
        }

        $rate = $l2Affiliate->getL2CommissionRate();
        $commissionAmount = round($orderAmount * ($rate / 100), 2);

        if ($commissionAmount <= 0) return;

        DB::transaction(function () use ($l2Affiliate, $order, $l1Referral, $orderAmount, $rate, $commissionAmount) {
            AffiliateCommission::create([
                'affiliate_id'      => $l2Affiliate->id,
                'order_id'          => $order->id,
                'referral_id'       => $l1Referral->id,
                'order_amount'      => $orderAmount,
                'commission_rate'   => $rate,
                'commission_amount' => $commissionAmount,
                'level'             => 'l2',
                'status'            => 'pending',
            ]);

            AffiliateBalanceService::addPending($l2Affiliate->id, $commissionAmount);
        });
    }

    /**
     * Move pending commissions to held after order is completed.
     */
    public static function holdCommissions(int $orderId): void
    {
        $holdDays = (int) Setting::get('affiliate', 'hold_period_days', 14);

        $commissions = AffiliateCommission::where('order_id', $orderId)
            ->where('status', 'pending')
            ->get();

        if ($holdDays === 0) {
            foreach ($commissions->groupBy('affiliate_id') as $affiliateId => $items) {
                $total = $items->sum('commission_amount');
                DB::transaction(function () use ($items, $affiliateId, $total) {
                    AffiliateCommission::whereIn('id', $items->pluck('id'))
                        ->update(['status' => 'available', 'available_at' => now()]);
                    AffiliateBalanceService::moveHeldToAvailable($affiliateId, $total);
                });
            }
            return;
        }

        AffiliateCommission::whereIn('id', $commissions->pluck('id'))
            ->update(['status' => 'held', 'held_at' => now()]);
    }

    /**
     * Release held commissions whose hold period has expired. Called daily.
     */
    public static function releaseHeldCommissions(): int
    {
        $holdDays = (int) Setting::get('affiliate', 'hold_period_days', 14);
        $cutoff = now()->subDays($holdDays);

        $commissions = AffiliateCommission::where('status', 'held')
            ->where('held_at', '<=', $cutoff)
            ->get();

        $count = 0;
        foreach ($commissions->groupBy('affiliate_id') as $affiliateId => $items) {
            $total = $items->sum('commission_amount');
            DB::transaction(function () use ($items, $affiliateId, $total) {
                AffiliateCommission::whereIn('id', $items->pluck('id'))
                    ->update(['status' => 'available', 'available_at' => now()]);
                AffiliateBalanceService::moveHeldToAvailable($affiliateId, $total);
            });
            $count += $items->count();
        }

        return $count;
    }

    /**
     * Reverse all commissions for a cancelled/refunded order.
     */
    public static function reverseCommissions(int $orderId, string $reason = 'Order cancelled/refunded'): void
    {
        $commissions = AffiliateCommission::where('order_id', $orderId)
            ->whereIn('status', ['pending', 'held', 'available'])
            ->get();

        foreach ($commissions as $commission) {
            DB::transaction(function () use ($commission, $reason) {
                $oldStatus = $commission->status;
                $commission->update([
                    'status'           => 'reversed',
                    'reversed_at'      => now(),
                    'reversal_reason'  => $reason,
                ]);

                if ($oldStatus === 'available') {
                    AffiliateBalanceService::deductAvailable($commission->affiliate_id, (float) $commission->commission_amount);
                    $balance = AffiliateBalance::where('affiliate_id', $commission->affiliate_id)->first();
                    if ($balance) {
                        $balance->increment('total_reversed', $commission->commission_amount);
                    }
                } else {
                    AffiliateBalanceService::reverse($commission->affiliate_id, (float) $commission->commission_amount);
                }
            });
        }
    }

    /**
     * Process a withdrawal request to wallet.
     */
    public static function processWithdrawalToWallet(Affiliate $affiliate, float $amount): WalletTransaction
    {
        $minWithdrawal = (float) Setting::get('affiliate', 'min_withdrawal', 50.00);
        $fee = (float) Setting::get('affiliate', 'withdrawal_fee', 0);

        if ($amount < $minWithdrawal) {
            throw new \RuntimeException("Minimum withdrawal amount is " . number_format($minWithdrawal, 2) . ".");
        }

        return DB::transaction(function () use ($affiliate, $amount, $fee) {
            $balance = AffiliateBalance::where('affiliate_id', $affiliate->id)
                ->lockForUpdate()->first();

            if (!$balance || $balance->available_balance < $amount) {
                throw new \RuntimeException(
                    'Insufficient affiliate balance. Available: ' . number_format($balance->available_balance ?? 0, 2)
                );
            }

            $netAmount = bcsub($amount, $fee, 2);

            $balance->available_balance = bcsub($balance->available_balance, $amount, 2);
            $balance->total_paid = bcadd($balance->total_paid, $amount, 2);
            $balance->save();

            AffiliateWithdrawal::create([
                'affiliate_id'   => $affiliate->id,
                'amount'         => $amount,
                'fee'            => $fee,
                'net_amount'     => $netAmount,
                'payment_method' => 'wallet',
                'status'         => 'completed',
                'completed_at'   => now(),
            ]);

            $walletService = app(WalletService::class);
            $wallet = $walletService->getOrCreateWallet($affiliate->user);
            $walletService->ensureWalletUsable($wallet);

            return $walletService->credit(
                wallet: $wallet,
                amount: (float) $netAmount,
                source: 'affiliate_transfer',
                description: "Affiliate commission transfer (#{$affiliate->referral_code})",
                referenceId: $affiliate->id,
                referenceType: Affiliate::class,
            );
        });
    }

    /**
     * Anti-fraud: validate a referral is legitimate.
     */
    public static function isValidReferral(string $referralCode, ?string $ip, ?int $userId): bool
    {
        $affiliate = Affiliate::where('referral_code', $referralCode)->first();
        if (!$affiliate) return false;

        if ($userId && $affiliate->user_id === $userId) return false;

        if ($userId && AffiliateReferral::where('referred_user_id', $userId)->exists()) return false;

        return true;
    }

    /**
     * Recalculate an affiliate's tier based on performance.
     */
    public static function recalculateTier(Affiliate $affiliate): void
    {
        $balance = $affiliate->balance;
        $totalEarned = $balance ? (float) $balance->total_earned : 0;
        $totalReferrals = $affiliate->referrals()->whereIn('status', ['registered', 'converted'])->count();
        $totalConversions = $affiliate->referrals()->where('status', 'converted')->count();

        $bestTier = AffiliateTier::where('is_default', true)->first();

        $tiers = AffiliateTier::orderBy('sort_order', 'desc')->get();
        foreach ($tiers as $tier) {
            if (
                $totalEarned >= $tier->min_earnings_threshold &&
                $totalReferrals >= $tier->min_referrals &&
                $totalConversions >= $tier->min_conversions
            ) {
                $bestTier = $tier;
                break;
            }
        }

        if ($bestTier && $affiliate->tier !== $bestTier->slug) {
            $affiliate->update(['tier' => $bestTier->slug]);
        }
    }
}
