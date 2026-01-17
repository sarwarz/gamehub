<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            /* Dashboard */
            'dashboard',

            /* Product Attributes */
            'categories',
            'platforms',
            'types',
            'regions',
            'languages',
            'workson',
            'developers',
            'publishers',

            /* Products */
            'products',
            'product-requests',
            'product-reviews',

            /* Seller Offers */
            'seller-offers',

            /* Orders */
            'orders',

            /* Transactions */
            'transactions',

            /* Sellers */
            'sellers',
            'seller-withdraws',

            /* Ecommerce */
            'coupons',
            'taxes',
            'payment-methods',

            /* Website / CMS */
            'currencies',
            'sliders',
            'pages',

            /* Blogs */
            'blogs',
            'blog-categories',
            'blog-comments',

            /* Wallet */
            'wallets',

            /* Support */
            'support-tickets',

            /* Communications */
            'subscribers',
            'contact-messages',

            /* Users & Access Control */
            'users',
            'roles',
            'permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                [
                    'label' => ucwords(str_replace('-', ' ', $permission)),
                ]
            );
        }
    }
}
