<?php

namespace Database\Seeders;

use App\Models\TicketDepartment;
use Illuminate\Database\Seeder;

class TicketDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Order',   'slug' => 'order',   'color' => 'primary',   'icon' => 'ti tabler-shopping-cart',   'sort_order' => 1, 'description' => 'Order related issues — tracking, missing items, cancellations'],
            ['name' => 'Payment', 'slug' => 'payment', 'color' => 'warning',   'icon' => 'ti tabler-credit-card',     'sort_order' => 2, 'description' => 'Payment, billing, refunds and charges'],
            ['name' => 'Account', 'slug' => 'account', 'color' => 'info',      'icon' => 'ti tabler-user-circle',     'sort_order' => 3, 'description' => 'Account settings, login issues, profile changes'],
            ['name' => 'Product', 'slug' => 'product', 'color' => 'success',   'icon' => 'ti tabler-package',         'sort_order' => 4, 'description' => 'Product questions, availability, specifications'],
            ['name' => 'General', 'slug' => 'general', 'color' => 'secondary', 'icon' => 'ti tabler-help-circle',     'sort_order' => 5, 'description' => 'General inquiries and feedback'],
        ];

        foreach ($departments as $dept) {
            TicketDepartment::firstOrCreate(
                ['slug' => $dept['slug']],
                $dept
            );
        }
    }
}
