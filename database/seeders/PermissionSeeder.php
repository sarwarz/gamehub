<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'categories',
            'platforms',
            'types',
            'regions',
            'languages',
            'workson',
            'developers',
            'publishers',
            'products',
            'sellers',
            'seller-offers',
            'currencies',
            'orders',
            'roles',
            'permissions',
            'users',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm],
                ['label' => ucwords(str_replace('-', ' ', $perm))]
            );
        }
    }
}
