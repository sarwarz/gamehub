<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'title'       => 'Windows 11 Home OEM Key',
                'sku'         => 'WIN11-HOME-OEM',
                'description' => 'Genuine Microsoft Windows 11 Home OEM license key.',
                'developer_id'=> 1,
                'publisher_id'=> 1,
                'delivery_type' => 'instant',
                'status'        => 'active',
                'is_featured'   => true,
            ],
            [
                'title'       => 'Microsoft Office 2021 Professional Plus',
                'sku'         => 'OFFICE-2021-PRO-PLUS',
                'description' => 'Lifetime license for Microsoft Office 2021 Professional Plus.',
                'developer_id'=> 1,
                'publisher_id'=> 1,
                'delivery_type' => 'instant',
                'status'        => 'active',
                'is_featured'   => true,
            ],
            [
                'title'       => 'Grand Theft Auto V (GTA 5) PC - Rockstar Key',
                'sku'         => 'GTA5-PC-ROCKSTAR',
                'description' => 'Open-world action-adventure game from Rockstar Games.',
                'developer_id'=> 2,
                'publisher_id'=> 3,
                'delivery_type' => 'instant',
                'status'        => 'active',
                'is_featured'   => false,
            ],
            [
                'title'       => 'Minecraft Java Edition PC',
                'sku'         => 'MINECRAFT-PC-JAVA',
                'description' => 'Minecraft Java Edition for PC. Instant activation.',
                'developer_id'=> 3,
                'publisher_id'=> 3,
                'delivery_type' => 'instant',
                'status'        => 'active',
                'is_featured'   => false,
            ],
            [
                'title'       => 'FIFA 24 (EA Sports) - Origin Key',
                'sku'         => 'FIFA24-ORIGIN',
                'description' => 'EA Sports FIFA 24 football simulation game.',
                'developer_id'=> 4,
                'publisher_id'=> 2,
                'delivery_type' => 'instant',
                'status'        => 'active',
                'is_featured'   => true,
            ],
        ];

        foreach ($products as $p) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($p['title'])],
                array_merge($p, [
                    'attributes' => [
                        ['key' => 'Edition', 'value' => 'Standard'],
                    ],
                    'system_requirements' => [
                        'minimum' => [
                            ['key' => 'Processor', 'value' => 'Intel i3'],
                            ['key' => 'RAM', 'value' => '4 GB'],
                        ],
                        'recommended' => [
                            ['key' => 'Processor', 'value' => 'Intel i5'],
                            ['key' => 'RAM', 'value' => '8 GB'],
                        ],
                    ],
                    'meta_title'       => $p['title'],
                    'meta_description' => $p['description'],
                    'meta_keywords'    => strtolower($p['title']),
                ])
            );

            // Attach relations (IDs must exist from your seeders)
            $product->categories()->sync([1]);   // e.g. Software / Games
            $product->platforms()->sync([1]);    // e.g. Windows
            $product->types()->sync([1]);        // e.g. Game/Software
            $product->regions()->sync([1]);      // e.g. Global
            $product->languages()->sync([1]);    // e.g. English
            $product->worksOn()->sync([1]);      // e.g. Windows
        }
    }
}
