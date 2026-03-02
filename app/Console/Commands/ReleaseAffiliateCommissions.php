<?php

namespace App\Console\Commands;

use App\Services\AffiliateService;
use Illuminate\Console\Command;

class ReleaseAffiliateCommissions extends Command
{
    protected $signature = 'affiliate:release-commissions';
    protected $description = 'Release held affiliate commissions that have passed the hold period';

    public function handle(): int
    {
        $count = AffiliateService::releaseHeldCommissions();
        $this->info("Released {$count} affiliate commissions.");
        return self::SUCCESS;
    }
}
