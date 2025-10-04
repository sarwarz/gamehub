<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductRegion;
use Illuminate\Support\Str;

class ProductRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            'Global',
            'Europe (EU)',
            'North America (NA)',
            'South America (SA)',
            'Asia',
            'Middle East',
            'Africa',
            'Oceania',
        ];

        foreach ($regions as $region) {
            ProductRegion::firstOrCreate(
                ['slug' => Str::slug($region)],
                [
                    'name'   => $region,
                    'status' => 'active',
                ]
            );
        }
    }
}
