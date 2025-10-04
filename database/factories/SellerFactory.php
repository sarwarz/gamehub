<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        $storeName = $this->faker->unique()->company;

        return [
            'user_id'      => User::factory(), // creates user if not provided
            'store_name'   => $storeName,
            'slug'         => Str::slug($storeName) . '-' . Str::random(5),
            'logo'         => null,
            'banner'       => null,
            'description'  => $this->faker->sentence,
            'email'        => $this->faker->unique()->safeEmail,
            'phone'        => $this->faker->phoneNumber,
            'website'      => $this->faker->url,
            'company_name' => $this->faker->company,
            'vat_number'   => strtoupper(Str::random(10)),
            'country'      => $this->faker->country,
            'city'         => $this->faker->city,
            'address'      => $this->faker->address,
            'postal_code'  => $this->faker->postcode,
            'rating'       => $this->faker->randomFloat(2, 0, 5),
            'total_sales'  => $this->faker->numberBetween(0, 1000),
            'status'       => $this->faker->randomElement(['pending', 'active', 'suspended']),
            'is_verified'  => $this->faker->boolean(70),
        ];
    }
}
