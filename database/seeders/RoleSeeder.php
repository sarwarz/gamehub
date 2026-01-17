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
        DB::table('roles')->insert([
            [
                'name'       => 'superadmin',
                'label'      => 'Super Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'admin',
                'label'      => 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'seller',
                'label'      => 'Seller',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'customer',
                'label'      => 'Customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
