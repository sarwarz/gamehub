<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductWorksOn;
use Illuminate\Support\Str;

class ProductWorksOnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            ['name' => 'Windows', 'icon' => 'fa-brands fa-windows'],
            ['name' => 'Mac OS', 'icon' => 'fa-brands fa-apple'],
            ['name' => 'Linux', 'icon' => 'fa-brands fa-linux'],
            ['name' => 'Android', 'icon' => 'fa-brands fa-android'],
            ['name' => 'iOS', 'icon' => 'fa-brands fa-apple'],
        ];

        foreach ($platforms as $p) {
            ProductWorksOn::firstOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'name'   => $p['name'],
                    'icon'   => $p['icon'],
                    'status' => 'active',
                ]
            );
        }
    }
}
