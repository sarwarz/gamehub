<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateBalance;
use Illuminate\Support\Facades\DB;

class AffiliateBalanceService
{
    public static function getOrCreate(int $affiliateId): AffiliateBalance
    {
        return AffiliateBalance::firstOrCreate(
            ['affiliate_id' => $affiliateId],
            ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_paid' => 0, 'total_reversed' => 0]
        );
    }

    public static function addPending(int $affiliateId, float $amount): void
    {
        DB::transaction(function () use ($affiliateId, $amount) {
            $balance = AffiliateBalance::lockForUpdate()->firstOrCreate(
                ['affiliate_id' => $affiliateId],
                ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_paid' => 0, 'total_reversed' => 0]
            );

            $balance->pending_balance = bcadd($balance->pending_balance, $amount, 2);
            $balance->total_earned    = bcadd($balance->total_earned, $amount, 2);
            $balance->save();
        });
    }

    public static function moveToHeld(int $affiliateId, float $amount): void
    {
        // Pending → held is a status change on commissions, balance stays in pending_balance
    }

    public static function moveHeldToAvailable(int $affiliateId, float $amount): void
    {
        DB::transaction(function () use ($affiliateId, $amount) {
            $balance = AffiliateBalance::where('affiliate_id', $affiliateId)
                ->lockForUpdate()->first();

            if (!$balance) return;

            $balance->pending_balance   = max(0, bcsub($balance->pending_balance, $amount, 2));
            $balance->available_balance = bcadd($balance->available_balance, $amount, 2);
            $balance->save();
        });
    }

    public static function deductAvailable(int $affiliateId, float $amount): void
    {
        DB::transaction(function () use ($affiliateId, $amount) {
            $balance = AffiliateBalance::where('affiliate_id', $affiliateId)
                ->lockForUpdate()->first();

            if (!$balance) return;

            $balance->available_balance = max(0, bcsub($balance->available_balance, $amount, 2));
            $balance->save();
        });
    }

    public static function addPaid(int $affiliateId, float $amount): void
    {
        DB::transaction(function () use ($affiliateId, $amount) {
            $balance = AffiliateBalance::where('affiliate_id', $affiliateId)
                ->lockForUpdate()->first();

            if (!$balance) return;

            $balance->total_paid = bcadd($balance->total_paid, $amount, 2);
            $balance->save();
        });
    }

    public static function reverse(int $affiliateId, float $amount): void
    {
        DB::transaction(function () use ($affiliateId, $amount) {
            $balance = AffiliateBalance::where('affiliate_id', $affiliateId)
                ->lockForUpdate()->first();

            if (!$balance) return;

            $balance->pending_balance = max(0, bcsub($balance->pending_balance, $amount, 2));
            $balance->total_reversed  = bcadd($balance->total_reversed, $amount, 2);
            $balance->save();
        });
    }

    public static function refundAvailable(int $affiliateId, float $amount): void
    {
        DB::transaction(function () use ($affiliateId, $amount) {
            $balance = AffiliateBalance::where('affiliate_id', $affiliateId)
                ->lockForUpdate()->first();

            if (!$balance) return;

            $balance->available_balance = bcadd($balance->available_balance, $amount, 2);
            $balance->save();
        });
    }
}
