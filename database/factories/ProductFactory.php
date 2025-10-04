<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'title'       => ucfirst($title),
            'slug'        => Str::slug($title) . '-' . Str::random(5),
            'sku'         => strtoupper(Str::random(8)),
            'description' => $this->faker->paragraph,
            'developer_id'=> null,
            'publisher_id'=> null,
            'cover_image' => 'images/sample.png',
            'gallery'     => [],
            'attributes'  => [],
            'system_requirements' => [],
            'delivery_type' => 'instant',
            'status'        => $this->faker->randomElement(['draft', 'active', 'inactive']),
            'is_featured'   => $this->faker->boolean(20),
            'sort_order'    => 0,
            'meta_title'    => ucfirst($title),
            'meta_description' => $this->faker->sentence,
            'meta_keywords'    => implode(',', $this->faker->words(5)),
        ];
    }
}
