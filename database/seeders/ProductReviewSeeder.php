<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductReview;
use Illuminate\Support\Arr;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users    = User::pluck('id')->toArray();
        $products = Product::pluck('id')->toArray();

        if (empty($users) || empty($products)) {
            $this->command->warn('No users or products found. Skipping ProductReviewSeeder.');
            return;
        }

        foreach ($products as $productId) {

            // Random 3–8 reviews per product
            $reviewCount = rand(3, 4);

            $reviewUsers = Arr::random($users, min($reviewCount, count($users)));

            foreach ((array) $reviewUsers as $userId) {

                ProductReview::create([
                    'product_id' => $productId,
                    'user_id'    => $userId,
                    'rating'     => rand(3, 5),
                    'title'      => fake()->sentence(4),
                    'review'     => fake()->paragraph(),
                    'status'     => Arr::random(['approved', 'pending']),
                    'is_verified_purchase' => fake()->boolean(70),
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                ]);
            }
        }

        $this->command->info('Product reviews seeded successfully.');
    }
}
