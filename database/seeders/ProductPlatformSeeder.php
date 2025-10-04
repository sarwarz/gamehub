<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductPlatform;
use Illuminate\Support\Str;

class ProductPlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            'Steam',
            'Epic Games',
            'Origin',
            'Uplay',
            'GOG',
            'Xbox',
            'PlayStation',
            'Nintendo Switch',
            'Windows',
            'Mac OS',
        ];

        foreach ($platforms as $platform) {
            ProductPlatform::firstOrCreate(
                ['slug' => Str::slug($platform)],
                [
                    'name'   => $platform,
                    'status' => 'active',
                ]
            );
        }
    }
}
