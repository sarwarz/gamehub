<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Str;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        $realSellers = [
            [
                'name'        => 'GameHub Official',
                'username'    => 'gamehub_official',
                'email'       => 'support@gamehub.com',
                'store_name'  => 'GameHub Store',
                'description' => 'Official digital game and software marketplace.',
                'country'     => 'United States',
                'city'        => 'Los Angeles',
                'rating'      => 4.9,
                'total_sales' => 2500,
                'logo'        => 'uploads/sellers/logos/GameHubOfficial.jpg',
            ],
            [
                'name'        => 'Digital Keys BD',
                'username'    => 'digitalkeysbd',
                'email'       => 'sales@digitalkeysbd.com',
                'store_name'  => 'Digital Keys BD',
                'description' => 'Trusted Bangladeshi seller of genuine game & software keys.',
                'country'     => 'Bangladesh',
                'city'        => 'Dhaka',
                'rating'      => 4.7,
                'total_sales' => 1800,
                'logo'        => 'uploads/sellers/logos/digitalkeysbd.png',
            ],
            [
                'name'        => 'PlayZone Market',
                'username'    => 'playzone_market',
                'email'       => 'support@playzone.com',
                'store_name'  => 'PlayZone Market',
                'description' => 'Affordable PC games, DLCs, and digital items.',
                'country'     => 'United Kingdom',
                'city'        => 'London',
                'rating'      => 4.6,
                'total_sales' => 1320,
                'logo'        => 'uploads/sellers/logos/PlayZoneMarket.png',
            ],
            [
                'name'        => 'KeyWorld',
                'username'    => 'keyworld',
                'email'       => 'contact@keyworld.io',
                'store_name'  => 'KeyWorld',
                'description' => 'Global distributor of genuine activation keys.',
                'country'     => 'Germany',
                'city'        => 'Berlin',
                'rating'      => 4.8,
                'total_sales' => 2100,
                'logo'        => 'uploads/sellers/logos/KeyWorld.jpg',
            ],
            [
                'name'        => 'NextGen Games',
                'username'    => 'nextgen_games',
                'email'       => 'hello@nextgengames.gg',
                'store_name'  => 'NextGen Games',
                'description' => 'Next-generation games at competitive prices.',
                'country'     => 'Canada',
                'city'        => 'Toronto',
                'rating'      => 4.5,
                'total_sales' => 980,
                'logo'        => 'uploads/sellers/logos/nextgen_games.png',
            ],
        ];

        foreach ($realSellers as $sellerData) {

            // Create or get seller user
            $user = User::firstOrCreate(
                ['email' => $sellerData['email']],
                [
                    'name'        => $sellerData['name'],
                    'username'    => $sellerData['username'],
                    'password'    => bcrypt('password'),
                    'is_seller'   => true,
                    'is_verified' => true,
                    'is_active'   => true,
                ]
            );

            // Create seller profile
            Seller::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'store_name'  => $sellerData['store_name'],
                    'slug'        => Str::slug($sellerData['store_name']),
                    'description' => $sellerData['description'],
                    'logo'        => $sellerData['logo'],
                    'email'       => $sellerData['email'],
                    'phone'       => fake()->phoneNumber(),
                    'country'     => $sellerData['country'],
                    'city'        => $sellerData['city'],
                    'address'     => fake()->address(),
                    'rating'      => $sellerData['rating'],
                    'total_sales' => $sellerData['total_sales'],
                    'status'      => 'active',
                    'is_verified' => true,
                ]
            );
        }
    }
}
