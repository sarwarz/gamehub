<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seller;
use App\Models\Product;
use App\Models\SellerOffer;
use App\Models\SellerOfferKey;

class SellerOfferSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch existing sellers & products
        $sellers  = Seller::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();

        if ($sellers->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No sellers or products found. Skipping SellerOfferSeeder.');
            return;
        }

        foreach ($sellers as $seller) {

            // Each seller sells up to 3 random products
            foreach ($products->random(min(3, $products->count())) as $product) {

                // Prevent duplicate offers
                $offer = SellerOffer::firstOrCreate(
                    [
                        'seller_id'  => $seller->id,
                        'product_id' => $product->id,
                    ],
                    [
                        // Pricing
                        'retail_price'                    => fake()->randomFloat(2, 30, 60),
                        'retail_acquisition_cost'         => fake()->randomFloat(2, 20, 35),

                        'wholesale_10_99_price'           => fake()->randomFloat(2, 25, 50),
                        'wholesale_10_99_acquisition_cost'=> fake()->randomFloat(2, 18, 30),

                        'wholesale_100_plus_price'        => fake()->randomFloat(2, 20, 45),
                        'wholesale_100_acquisition_cost'  => fake()->randomFloat(2, 15, 28),

                        'sale_mode'   => 'both',
                        'status'      => 'active',
                        'is_verified' => true,
                        'is_promoted' => fake()->boolean(30),
                    ]
                );

                // Attach license keys if none exist
                if ($offer->keys()->count() === 0) {
                    for ($i = 0; $i < 10; $i++) {
                        SellerOfferKey::create([
                            'seller_offer_id' => $offer->id,
                            'type'   => 'text',
                            'value'  => strtoupper(fake()->bothify('GAME-####-????-####')),
                            'status' => 'available',
                        ]);
                    }
                }
            }
        }
    }
}
