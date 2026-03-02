<?php

namespace Database\Seeders;

use App\Models\AffiliateTier;
use Illuminate\Database\Seeder;

class AffiliateTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name'                    => 'Bronze',
                'slug'                    => 'bronze',
                'commission_rate'         => 5.00,
                'l2_commission_rate'      => 1.00,
                'min_earnings_threshold'  => 0,
                'min_referrals'           => 0,
                'min_conversions'         => 0,
                'color'                   => 'warning',
                'sort_order'              => 1,
                'is_default'              => true,
            ],
            [
                'name'                    => 'Silver',
                'slug'                    => 'silver',
                'commission_rate'         => 8.00,
                'l2_commission_rate'      => 2.00,
                'min_earnings_threshold'  => 500,
                'min_referrals'           => 10,
                'min_conversions'         => 5,
                'color'                   => 'secondary',
                'sort_order'              => 2,
                'is_default'              => false,
            ],
            [
                'name'                    => 'Gold',
                'slug'                    => 'gold',
                'commission_rate'         => 12.00,
                'l2_commission_rate'      => 3.00,
                'min_earnings_threshold'  => 2000,
                'min_referrals'           => 50,
                'min_conversions'         => 25,
                'color'                   => 'info',
                'sort_order'              => 3,
                'is_default'              => false,
            ],
            [
                'name'                    => 'Platinum',
                'slug'                    => 'platinum',
                'commission_rate'         => 15.00,
                'l2_commission_rate'      => 5.00,
                'min_earnings_threshold'  => 10000,
                'min_referrals'           => 200,
                'min_conversions'         => 100,
                'color'                   => 'primary',
                'sort_order'              => 4,
                'is_default'              => false,
            ],
        ];

        foreach ($tiers as $tier) {
            AffiliateTier::updateOrCreate(['slug' => $tier['slug']], $tier);
        }
    }
}
