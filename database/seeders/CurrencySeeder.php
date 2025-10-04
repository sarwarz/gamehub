<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('currencies')->insert([
            [
                'code'       => 'USD',
                'name'       => 'US Dollar',
                'symbol'     => '$',
                'is_active'  => true,
                'is_default' => true, // Default currency
                'rate'       => 1.00000000,
                'fetched_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code'       => 'EUR',
                'name'       => 'Euro',
                'symbol'     => '€',
                'is_active'  => true,
                'is_default' => false,
                'rate'       => 0.92000000,
                'fetched_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code'       => 'GBP',
                'name'       => 'British Pound',
                'symbol'     => '£',
                'is_active'  => true,
                'is_default' => false,
                'rate'       => 0.79000000,
                'fetched_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code'       => 'BDT',
                'name'       => 'Bangladeshi Taka',
                'symbol'     => '৳',
                'is_active'  => true,
                'is_default' => false,
                'rate'       => 110.25000000,
                'fetched_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code'       => 'JPY',
                'name'       => 'Japanese Yen',
                'symbol'     => '¥',
                'is_active'  => true,
                'is_default' => false,
                'rate'       => 148.35000000,
                'fetched_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
