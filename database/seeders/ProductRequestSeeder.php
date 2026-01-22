<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ProductRequest;
use App\Models\ProductCategory;
use App\Models\ProductPlatform;
use App\Models\ProductType;
use App\Models\ProductRegion;
use App\Models\ProductLanguage;
use App\Models\ProductWorksOn;

class ProductRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure required data exists
        $user      = User::first();
        $category  = ProductCategory::first();
        $platform  = ProductPlatform::first();
        $type      = ProductType::first();
        $region    = ProductRegion::first();
        $language  = ProductLanguage::first();
        $worksOn   = ProductWorksOn::first();

        if (!$user || !$category || !$platform || !$type || !$region || !$language || !$worksOn) {
            $this->command->warn('⚠️ ProductRequestSeeder skipped (missing required master data)');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected', 'completed'];

        foreach (range(1, 25) as $i) {
            ProductRequest::create([
                'user_id'      => $user->id,
                'category_id'  => $category->id,
                'platform_id'  => $platform->id,
                'type_id'      => $type->id,
                'region_id'    => $region->id,
                'language_id'  => $language->id,
                'works_on_id'  => $worksOn->id,

                'title'        => "Sample Product Request {$i}",
                'description'  => "This is a sample product request description for item {$i}.",
                'source_url'   => $i % 2 === 0 ? 'https://example.com/product-'.$i : null,
                'status'       => $statuses[array_rand($statuses)],

                'created_at'   => now()->subDays(rand(0, 30)),
                'updated_at'   => now(),
            ]);
        }

        $this->command->info('✅ ProductRequestSeeder completed');
    }
}
