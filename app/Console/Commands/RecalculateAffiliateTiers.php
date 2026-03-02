<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Services\AffiliateService;
use Illuminate\Console\Command;

class RecalculateAffiliateTiers extends Command
{
    protected $signature = 'affiliate:recalculate-tiers';
    protected $description = 'Recalculate tier assignments for all active affiliates based on performance';

    public function handle(): int
    {
        $affiliates = Affiliate::active()->with('balance')->get();
        $updated = 0;

        foreach ($affiliates as $affiliate) {
            $oldTier = $affiliate->tier;
            AffiliateService::recalculateTier($affiliate);
            if ($affiliate->fresh()->tier !== $oldTier) {
                $updated++;
            }
        }

        $this->info("Recalculated tiers for {$affiliates->count()} affiliates. {$updated} upgraded/downgraded.");
        return self::SUCCESS;
    }
}
