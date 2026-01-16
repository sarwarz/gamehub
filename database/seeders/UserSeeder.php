<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //  Super Admin user
        User::create([
            'name'          => 'Super Admin',
            'username'      => 'superadmin',
            'email'         => 'superadmin@gmail.com',
            'password'      => Hash::make('Freky@9622'),
            'is_active'     => true,
            'is_seller'     => false,
            'is_verified'   => true,
            'is_super_admin'=> true, 
        ]);

        // Admin user
        User::create([
            'name'       => 'MD Sarwar Zahan',
            'username'   => 'admin',
            'email'      => 'sarwarzahan16@gmail.com',
            'password'   => Hash::make('Freky@9622'),
            'is_active'  => true,
            'is_seller'  => false,
            'is_verified'=> true,
            'is_super_admin'=> true,
        ]);

        // Seller user
        User::create([
            'name'       => 'Test Seller',
            'username'   => 'seller01',
            'email'      => 'seller@gmail.com',
            'password'   => Hash::make('password'),
            'is_active'  => true,
            'is_seller'  => true,
            'is_verified'=> true,
            'is_super_admin'=> false,
        ]);

        // Buyer user
        User::create([
            'name'       => 'John Buyer',
            'username'   => 'buyer01',
            'email'      => 'buyer@gmail.com',
            'password'   => Hash::make('password'),
            'is_active'  => true,
            'is_seller'  => false,
            'is_verified'=> false,
            'is_super_admin'=> false,
        ]);
    }
}
