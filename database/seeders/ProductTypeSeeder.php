<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductType;
use Illuminate\Support\Str;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Game',        'commission' => 10.00],
            ['name' => 'Software',    'commission' => 12.50],
            ['name' => 'Gift Card',   'commission' => 5.00],
            ['name' => 'DLC',         'commission' => 8.00],
            ['name' => 'Subscription','commission' => 15.00],
        ];

        foreach ($types as $type) {
            ProductType::firstOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name'       => $type['name'],
                    'commission' => $type['commission'],
                    'status'     => 'active',
                ]
            );
        }
    }
}
