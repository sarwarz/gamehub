<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductPublisher;
use Illuminate\Support\Str;

class ProductPublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishers = [
            [
                'name'        => 'Ubisoft',
                'website'     => 'https://www.ubisoft.com',
                'description' => 'Ubisoft is one of the largest video game publishers, known for Assassin’s Creed, Far Cry, and more.',
            ],
            [
                'name'        => 'Electronic Arts (EA)',
                'website'     => 'https://www.ea.com',
                'description' => 'Electronic Arts is a leading publisher known for FIFA, Battlefield, and The Sims.',
            ],
            [
                'name'        => 'Activision',
                'website'     => 'https://www.activision.com',
                'description' => 'Activision is famous for Call of Duty and other blockbuster franchises.',
            ],
            [
                'name'        => 'Square Enix',
                'website'     => 'https://square-enix-games.com',
                'description' => 'Square Enix is the publisher behind Final Fantasy, Kingdom Hearts, and Dragon Quest.',
            ],
            [
                'name'        => 'Bethesda Softworks',
                'website'     => 'https://bethesda.net',
                'description' => 'Bethesda is the publisher of The Elder Scrolls, Fallout, and Doom franchises.',
            ],
        ];

        foreach ($publishers as $pub) {
            ProductPublisher::firstOrCreate(
                ['slug' => Str::slug($pub['name'])],
                [
                    'name'        => $pub['name'],
                    'website'     => $pub['website'],
                    'description' => $pub['description'],
                    'status'      => 'active',
                ]
            );
        }
    }
}
