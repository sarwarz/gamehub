<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\SellerSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            CurrencySeeder::class,
            SellerSeeder::class,
            ProductCategorySeeder::class,
            ProductPlatformSeeder::class,
            ProductTypeSeeder::class,
            ProductRegionSeeder::class,
            ProductLanguageSeeder::class,
            ProductWorksOnSeeder::class,
            ProductDeveloperSeeder::class,
            ProductPublisherSeeder::class,
            ProductSeeder::class,
            ProductReviewSeeder::class,
        ]);

    }
}
