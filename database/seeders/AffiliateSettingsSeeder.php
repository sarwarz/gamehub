<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AffiliateSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'is_enabled'              => false,
            'auto_approve'            => false,
            'cookie_duration_days'    => 30,
            'hold_period_days'        => 14,
            'min_withdrawal'          => 50.00,
            'withdrawal_fee'          => 0,
            'allow_self_referral'     => false,
            'allow_l2_commissions'    => false,
            'default_commission_rate' => 5.00,
            'default_l2_rate'         => 2.00,
            'commission_basis'        => 'net',
            'payout_methods'          => ['wallet'],
            'terms_content'           => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::set('affiliate', $key, $value);
        }
    }
}
