<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Operating Systems',
            'Office & Productivity',
            'Antivirus & Security',
            'Games',
            'Gift Cards',
            'Software Development Tools',
            'Graphic Design',
        ];

        foreach ($categories as $category) {
            ProductCategory::firstOrCreate(
                ['slug' => Str::slug($category)],
                [
                    'name'   => $category,
                    'status' => 'active',
                ]
            );
        }
    }
}
