<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'superadmin',    'label' => 'Super Administrator', 'type' => 'internal', 'is_system' => true],
            ['name' => 'admin',         'label' => 'Administrator',       'type' => 'internal', 'is_system' => true],
            ['name' => 'support_agent', 'label' => 'Support Agent',       'type' => 'internal', 'is_system' => false],
            ['name' => 'seller',        'label' => 'Seller',              'type' => 'external', 'is_system' => true],
            ['name' => 'customer',      'label' => 'Customer',            'type' => 'external', 'is_system' => true],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
