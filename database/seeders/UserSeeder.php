<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole  = Role::where('name', 'superadmin')->first();
        $adminRole       = Role::where('name', 'admin')->first();
        $supportRole     = Role::where('name', 'support_agent')->first();
        $sellerRole      = Role::where('name', 'seller')->first();
        $customerRole    = Role::where('name', 'customer')->first();

        // ── Assign all permissions to admin role ─────────────────
        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::pluck('id'));
        }

        // ── Assign limited permissions to support_agent role ─────
        if ($supportRole) {
            $supportPermissions = Permission::whereIn('name', [
                'dashboard',
                'orders',
                'support-tickets',
                'contact-messages',
                'users',
            ])->pluck('id');

            $supportRole->permissions()->sync($supportPermissions);
        }

        // ── Super Admin (bypasses all permission checks) ─────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name'        => 'Super Admin',
                'username'    => 'superadmin',
                'password'    => Hash::make('Freky@9622'),
                'is_active'   => true,
                'is_verified' => true,
            ]
        );
        $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);

        // ── Admin (has all permissions via role) ─────────────────
        $admin = User::firstOrCreate(
            ['email' => 'sarwarzahan16@gmail.com'],
            [
                'name'        => 'MD Sarwar Zahan',
                'username'    => 'admin',
                'password'    => Hash::make('Freky@9622'),
                'is_active'   => true,
                'is_verified' => true,
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        // ── Support Agent (limited admin panel access) ───────────
        if ($supportRole) {
            $support = User::firstOrCreate(
                ['email' => 'support@gmail.com'],
                [
                    'name'        => 'Support Agent',
                    'username'    => 'support01',
                    'password'    => Hash::make('password'),
                    'is_active'   => true,
                    'is_verified' => true,
                ]
            );
            $support->roles()->syncWithoutDetaching([$supportRole->id]);
        }

        // ── Seller (also gets customer role for buying) ──────────
        $seller = User::firstOrCreate(
            ['email' => 'seller@gmail.com'],
            [
                'name'        => 'Test Seller',
                'username'    => 'seller01',
                'password'    => Hash::make('password'),
                'is_active'   => true,
                'is_verified' => true,
            ]
        );
        $seller->roles()->syncWithoutDetaching([$sellerRole->id, $customerRole->id]);

        // ── Customer (external user, no admin access) ────────────
        $customer = User::firstOrCreate(
            ['email' => 'buyer@gmail.com'],
            [
                'name'        => 'John Buyer',
                'username'    => 'buyer01',
                'password'    => Hash::make('password'),
                'is_active'   => true,
                'is_verified' => false,
            ]
        );
        $customer->roles()->syncWithoutDetaching([$customerRole->id]);
    }
}
