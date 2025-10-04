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
        // create 3 sellers with 1 user each
        $sellers = Seller::factory(3)->create();

        // create 5 products
        $products = Product::factory(5)->create();

        foreach ($sellers as $seller) {
            foreach ($products->random(3) as $product) {
                $offer = SellerOffer::create([
                    'seller_id'   => $seller->id,
                    'product_id'  => $product->id,

                    // Pricing
                    'retail_price'              => 49.99,
                    'retail_acquisition_cost'   => 30.00,

                    'wholesale_10_99_price'     => 45.00,
                    'wholesale_10_99_acquisition_cost' => 28.00,

                    'wholesale_100_plus_price'  => 40.00,
                    'wholesale_100_acquisition_cost'   => 25.00,

                    'sale_mode'   => 'both',
                    'status'      => 'active',
                    'is_verified' => true,
                    'is_promoted' => fake()->boolean(30),
                ]);

                // attach sample keys
                for ($i = 0; $i < 5; $i++) {
                    SellerOfferKey::create([
                        'seller_offer_id' => $offer->id,
                        'type'   => 'text',
                        'value'  => strtoupper(fake()->bothify('KEY-####-????')),
                        'status' => 'available',
                    ]);
                }
            }
        }
    }
}
