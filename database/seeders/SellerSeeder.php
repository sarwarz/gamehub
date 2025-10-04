<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Str;

class SellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure at least one seller user exists
        $sellerUser = User::firstOrCreate(
            ['email' => 'seller@gmail.com'],
            [
                'name'        => 'Demo Seller',
                'username'    => 'seller01',
                'password'    => bcrypt('password'),
                'is_seller'   => true,
                'is_verified' => true,
                'is_active'   => true,
            ]
        );

        // Create seller profile for this user
        Seller::firstOrCreate(
            ['user_id' => $sellerUser->id],
            [
                'store_name'  => 'Demo Seller Store',
                'slug'        => 'demo-seller-store',
                'description' => 'This is a demo seller profile for testing.',
                'email'       => $sellerUser->email,
                'phone'       => '+880123456789',
                'country'     => 'Bangladesh',
                'city'        => 'Dhaka',
                'address'     => 'House 10, Road 5, Dhanmondi',
                'rating'      => 4.5,
                'total_sales' => 100,
                'status'      => 'active',
                'is_verified' => true,
            ]
        );

        // Create multiple fake sellers (optional)
        for ($i = 1; $i <= 5; $i++) {
            $user = User::factory()->create([
                'is_seller'   => true,
                'is_verified' => fake()->boolean(),
                'is_active'   => true,
            ]);

            Seller::create([
                'user_id'     => $user->id,
                'store_name'  => "Store $i",
                'slug'        => "store-" . Str::slug($user->username ?? $user->id),
                'description' => "This is the description for Store $i",
                'email'       => $user->email,
                'phone'       => fake()->phoneNumber(),
                'country'     => fake()->country(),
                'city'        => fake()->city(),
                'address'     => fake()->address(),
                'rating'      => fake()->randomFloat(2, 3, 5),
                'total_sales' => fake()->numberBetween(10, 500),
                'status'      => 'active',
                'is_verified' => fake()->boolean(),
            ]);
        }
    }
}
