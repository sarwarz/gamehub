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
                'title' => 'Grand Theft Auto V (PC)',
                'sku'   => 'GTA5-PC',
                'image' => 'gta5.jpg',
            ],
            [
                'title' => 'Red Dead Redemption 2 (PC)',
                'sku'   => 'RDR2-PC',
                'image' => 'rdr2.jpg',
            ],
            [
                'title' => 'Cyberpunk 2077 (PC)',
                'sku'   => 'CYBERPUNK-2077-PC',
                'image' => 'cyberpunk2077.jpg',
            ],
            [
                'title' => 'Elden Ring (PC)',
                'sku'   => 'ELDEN-RING-PC',
                'image' => 'eldenring.jpg',
            ],
            [
                'title' => 'Call of Duty: Modern Warfare III (PC)',
                'sku'   => 'COD-MW3-PC',
                'image' => 'cod-mw3.jpg',
            ],
            [
                'title' => 'EA Sports FC 24 (PC)',
                'sku'   => 'EA-FC24-PC',
                'image' => 'fc24.jpg',
            ],
            [
                'title' => 'Minecraft Java Edition',
                'sku'   => 'MINECRAFT-JAVA-PC',
                'image' => 'minecraft.jpg',
            ],
            [
                'title' => 'Assassin’s Creed Valhalla (PC)',
                'sku'   => 'AC-VALHALLA-PC',
                'image' => 'ac-valhalla.jpg',
            ],
            [
                'title' => 'Forza Horizon 5 (PC)',
                'sku'   => 'FORZA-H5-PC',
                'image' => 'forza5.jpg',
            ],
            [
                'title' => 'The Witcher 3: Wild Hunt (PC)',
                'sku'   => 'WITCHER3-PC',
                'image' => 'witcher3.jpg',
            ],
        ];

        foreach ($products as $p) {

            $product = Product::firstOrCreate(
                ['slug' => Str::slug($p['title'])],
                [
                    'title'         => $p['title'],
                    'sku'           => $p['sku'],
                    'description'   => $p['title'].' description.',
                    'developer_id'  => 1,
                    'publisher_id'  => 1,
                    'label_id'      => rand(1, 3),
                    'delivery_type' => 'instant',
                    'status'        => 'active',
                    'is_featured'   => rand(0, 1),

                    'attributes' => [
                        ['key' => 'Edition', 'value' => 'Standard'],
                    ],

                    'system_requirements' => [
                        'minimum' => [
                            ['key' => 'Processor', 'value' => 'Intel i5'],
                            ['key' => 'RAM', 'value' => '8 GB'],
                        ],
                        'recommended' => [
                            ['key' => 'Processor', 'value' => 'Intel i7'],
                            ['key' => 'RAM', 'value' => '16 GB'],
                        ],
                    ],

                    'meta_title'       => $p['title'],
                    'meta_description' => $p['title'].' buy online.',
                    'meta_keywords'    => strtolower($p['title']),
                ]
            );

            /* ============================
             | Primary Image (Media)
             ============================ */
            if (!$product->primaryImage) {
                $product->media()->create([
                    'disk'       => 'public',
                    'directory'  => 'products/cover',
                    'filename'   => $p['image'],
                    'type'       => 'image',
                    'is_primary' => true,
                ]);
            }

            /* ============================
             | Relations
             ============================ */
            $product->categories()->sync([1]);
            $product->platforms()->sync([1]);
            $product->types()->sync([1]);
            $product->regions()->sync([1]);
            $product->languages()->sync([1]);
            $product->worksOn()->sync([1]);
        }
    }
}
