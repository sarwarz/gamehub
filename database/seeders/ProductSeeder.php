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
                'title'        => 'Grand Theft Auto V (PC)',
                'sku'          => 'GTA5-PC',
                'description'  => 'Open-world action-adventure game by Rockstar Games.',
                'developer_id' => 1, // Rockstar Games
                'publisher_id' => 1,
                'cover_image'  => 'uploads/products/cover/gta5.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => true,
            ],
            [
                'title'        => 'Red Dead Redemption 2 (PC)',
                'sku'          => 'RDR2-PC',
                'description'  => 'Epic western open-world adventure.',
                'developer_id' => 1,
                'publisher_id' => 1,
                'cover_image'  => 'uploads/products/cover/rdr2.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => true,
            ],
            [
                'title'        => 'Cyberpunk 2077 (PC)',
                'sku'          => 'CYBERPUNK-2077-PC',
                'description'  => 'Futuristic open-world RPG from CD Projekt Red.',
                'developer_id' => 2,
                'publisher_id' => 2,
                'cover_image'  => 'uploads/products/cover/cyberpunk2077.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => true,
            ],
            [
                'title'        => 'Elden Ring (PC)',
                'sku'          => 'ELDEN-RING-PC',
                'description'  => 'Action RPG created by FromSoftware.',
                'developer_id' => 3,
                'publisher_id' => 3,
                'cover_image'  => 'uploads/products/cover/eldenring.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => true,
            ],
            [
                'title'        => 'Call of Duty: Modern Warfare III (PC)',
                'sku'          => 'COD-MW3-PC',
                'description'  => 'Fast-paced FPS action from Activision.',
                'developer_id' => 4,
                'publisher_id' => 4,
                'cover_image'  => 'uploads/products/cover/cod-mw3.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => false,
            ],
            [
                'title'        => 'FIFA 24 (EA Sports FC 24)',
                'sku'          => 'EA-FC24-PC',
                'description'  => 'Realistic football simulation game.',
                'developer_id' => 1,
                'publisher_id' => 2,
                'cover_image'  => 'uploads/products/cover/fc24.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => true,
            ],
            [
                'title'        => 'Minecraft Java Edition',
                'sku'          => 'MINECRAFT-JAVA-PC',
                'description'  => 'Sandbox survival and creativity game.',
                'developer_id' => 2,
                'publisher_id' => 5,
                'cover_image'  => 'uploads/products/cover/minecraft.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => false,
            ],
            [
                'title'        => 'Assassin’s Creed Valhalla (PC)',
                'sku'          => 'AC-VALHALLA-PC',
                'description'  => 'Viking-era action RPG.',
                'developer_id' => 3,
                'publisher_id' => 1,
                'cover_image'  => 'uploads/products/cover/ac-valhalla.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => false,
            ],
            [
                'title'        => 'Forza Horizon 5 (PC)',
                'sku'          => 'FORZA-H5-PC',
                'description'  => 'Open-world racing game set in Mexico.',
                'developer_id' => 1,
                'publisher_id' => 2,
                'cover_image'  => 'uploads/products/cover/forza5.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => true,
            ],
            [
                'title'        => 'The Witcher 3: Wild Hunt (PC)',
                'sku'          => 'WITCHER3-PC',
                'description'  => 'Story-rich open-world RPG.',
                'developer_id' => 1,
                'publisher_id' => 2,
                'cover_image'  => 'uploads/products/cover/witcher3.jpg',
                'delivery_type'=> 'instant',
                'status'       => 'active',
                'is_featured'  => true,
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
                            ['key' => 'Processor', 'value' => 'Intel i5'],
                            ['key' => 'RAM', 'value' => '8 GB'],
                            ['key' => 'Storage', 'value' => '60 GB'],
                        ],
                        'recommended' => [
                            ['key' => 'Processor', 'value' => 'Intel i7'],
                            ['key' => 'RAM', 'value' => '16 GB'],
                            ['key' => 'Storage', 'value' => '60 GB SSD'],
                        ],
                    ],
                    'meta_title'       => $p['title'],
                    'meta_description' => $p['description'],
                    'meta_keywords'    => strtolower($p['title']),
                ])
            );

            // Attach relations (IDs must exist)
            $product->categories()->sync([1]);   // Games
            $product->platforms()->sync([1]);    // PC
            $product->types()->sync([1]);        // Digital
            $product->regions()->sync([1]);      // Global
            $product->languages()->sync([1]);    // English
            $product->worksOn()->sync([1]);      // Windows
        }
    }
}
