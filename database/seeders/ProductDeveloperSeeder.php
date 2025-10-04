<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductDeveloper;
use Illuminate\Support\Str;

class ProductDeveloperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $developers = [
            [
                'name'        => 'Ubisoft',
                'website'     => 'https://www.ubisoft.com',
                'description' => 'Ubisoft is a leading publisher of popular franchises like Assassin’s Creed, Far Cry, and Watch Dogs.',
            ],
            [
                'name'        => 'Rockstar Games',
                'website'     => 'https://www.rockstargames.com',
                'description' => 'Rockstar Games is best known for Grand Theft Auto, Red Dead Redemption, and other blockbuster titles.',
            ],
            [
                'name'        => 'Mojang Studios',
                'website'     => 'https://www.minecraft.net',
                'description' => 'Mojang is the creator of Minecraft, one of the best-selling games of all time.',
            ],
            [
                'name'        => 'CD Projekt Red',
                'website'     => 'https://en.cdprojektred.com',
                'description' => 'CD Projekt Red is known for The Witcher series and Cyberpunk 2077.',
            ],
            [
                'name'        => 'Valve Corporation',
                'website'     => 'https://www.valvesoftware.com',
                'description' => 'Valve is behind Half-Life, Counter-Strike, and the Steam platform.',
            ],
        ];

        foreach ($developers as $dev) {
            ProductDeveloper::firstOrCreate(
                ['slug' => Str::slug($dev['name'])],
                [
                    'name'        => $dev['name'],
                    'website'     => $dev['website'],
                    'description' => $dev['description'],
                    'status'      => 'active',
                ]
            );
        }
    }
}
