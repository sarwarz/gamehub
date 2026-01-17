<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch roles
        $superAdminRole = Role::where('name', 'superadmin')->first();
        $adminRole      = Role::where('name', 'admin')->first();
        $sellerRole     = Role::where('name', 'seller')->first();
        $customerRole   = Role::where('name', 'customer')->first();

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name'           => 'Super Admin',
                'username'       => 'superadmin',
                'password'       => Hash::make('Freky@9622'),
                'is_active'      => true,
                'is_seller'      => false,
                'is_verified'    => true,
                'is_super_admin' => true,
            ]
        );
        $superAdmin->roles()->sync([$superAdminRole->id]);

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'sarwarzahan16@gmail.com'],
            [
                'name'           => 'MD Sarwar Zahan',
                'username'       => 'admin',
                'password'       => Hash::make('Freky@9622'),
                'is_active'      => true,
                'is_seller'      => false,
                'is_verified'    => true,
                'is_super_admin' => false,
            ]
        );
        $admin->roles()->sync([$adminRole->id]);

        // Seller
        $seller = User::firstOrCreate(
            ['email' => 'seller@gmail.com'],
            [
                'name'           => 'Test Seller',
                'username'       => 'seller01',
                'password'       => Hash::make('password'),
                'is_active'      => true,
                'is_seller'      => true,
                'is_verified'    => true,
                'is_super_admin' => false,
            ]
        );
        $seller->roles()->sync([$sellerRole->id]);

        // Customer
        $customer = User::firstOrCreate(
            ['email' => 'buyer@gmail.com'],
            [
                'name'           => 'John Buyer',
                'username'       => 'buyer01',
                'password'       => Hash::make('password'),
                'is_active'      => true,
                'is_seller'      => false,
                'is_verified'    => false,
                'is_super_admin' => false,
            ]
        );
        $customer->roles()->sync([$customerRole->id]);
    }
}
