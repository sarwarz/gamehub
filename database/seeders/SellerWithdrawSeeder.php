<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\SellerBalance;
use App\Models\SellerWithdraw;
use Illuminate\Database\Seeder;

class SellerWithdrawSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = Seller::all();
        if ($sellers->isEmpty()) return;

        foreach ($sellers as $seller) {
            SellerBalance::firstOrCreate(
                ['seller_id' => $seller->id],
                [
                    'available_balance' => rand(500, 5000) + rand(0, 99) / 100,
                    'pending_balance'   => rand(100, 1000) + rand(0, 99) / 100,
                    'total_earned'      => rand(5000, 25000) + rand(0, 99) / 100,
                    'total_paid'        => rand(2000, 10000) + rand(0, 99) / 100,
                ]
            );
        }

        $withdrawals = [
            // Pending - PayPal
            [
                'seller_index'    => 0,
                'amount'          => 350.00,
                'method'          => 'paypal',
                'payment_details' => [
                    'email' => 'payments@gamehub.com',
                ],
                'status'          => 'pending',
                'note'            => 'Monthly payout request',
                'created_at'      => now()->subDays(1),
            ],
            // Pending - Bank Transfer
            [
                'seller_index'    => 1,
                'amount'          => 1200.00,
                'method'          => 'bank',
                'payment_details' => [
                    'bank_name'      => 'Dutch-Bangla Bank Limited',
                    'account_name'   => 'Digital Keys BD Ltd',
                    'account_number' => '110.115.00012345',
                    'routing_number' => 'DBBLBDDH',
                    'branch_name'    => 'Motijheel Branch, Dhaka',
                ],
                'status'          => 'pending',
                'note'            => 'Please process before end of month',
                'created_at'      => now()->subDays(2),
            ],
            // Pending - Crypto
            [
                'seller_index'    => 2,
                'amount'          => 500.00,
                'method'          => 'crypto',
                'payment_details' => [
                    'network'        => 'USDT-TRC20',
                    'wallet_address' => 'TKdP6g3nVHMfKgMdFbRNMmw4wGTSUXA9Zc',
                ],
                'status'          => 'pending',
                'note'            => null,
                'created_at'      => now()->subHours(6),
            ],
            // Pending - bKash
            [
                'seller_index'    => 3,
                'amount'          => 250.00,
                'method'          => 'bkash',
                'payment_details' => [
                    'phone'        => '+8801712345678',
                    'account_type' => 'Personal',
                ],
                'status'          => 'pending',
                'note'            => 'Urgent withdrawal',
                'created_at'      => now()->subHours(3),
            ],
            // Pending - Wise
            [
                'seller_index'    => 4,
                'amount'          => 800.00,
                'method'          => 'wise',
                'payment_details' => [
                    'email'    => 'finance@nextgengames.gg',
                    'currency' => 'CAD',
                ],
                'status'          => 'pending',
                'note'            => 'Prefer CAD if possible',
                'created_at'      => now()->subDays(1)->subHours(5),
            ],

            // Approved - PayPal
            [
                'seller_index'    => 0,
                'amount'          => 1500.00,
                'method'          => 'paypal',
                'payment_details' => [
                    'email' => 'payments@gamehub.com',
                ],
                'status'          => 'approved',
                'note'            => 'Weekly payout',
                'admin_note'      => 'Sent via PayPal mass payment',
                'transaction_id'  => 'PP-8WN47293HS0294716',
                'processed_at'    => now()->subDays(5),
                'created_at'      => now()->subDays(7),
            ],
            // Approved - Bank
            [
                'seller_index'    => 1,
                'amount'          => 2000.00,
                'method'          => 'bank',
                'payment_details' => [
                    'bank_name'      => 'Dutch-Bangla Bank Limited',
                    'account_name'   => 'Digital Keys BD Ltd',
                    'account_number' => '110.115.00012345',
                    'routing_number' => 'DBBLBDDH',
                    'branch_name'    => 'Motijheel Branch, Dhaka',
                ],
                'status'          => 'approved',
                'note'            => null,
                'admin_note'      => 'Wire transfer sent - takes 2-3 business days',
                'transaction_id'  => 'SWIFT-2026022810045',
                'processed_at'    => now()->subDays(10),
                'created_at'      => now()->subDays(12),
            ],
            // Approved - Crypto
            [
                'seller_index'    => 3,
                'amount'          => 750.00,
                'method'          => 'crypto',
                'payment_details' => [
                    'network'        => 'BTC',
                    'wallet_address' => 'bc1qar0srrr7xfkvy5l643lydnw9re59gtzzwf5mdq',
                ],
                'status'          => 'approved',
                'note'            => null,
                'admin_note'      => 'BTC sent at market rate',
                'transaction_id'  => '3J98t1WpEZ73CNmQviecrnyiWrnqRhWNLy',
                'processed_at'    => now()->subDays(3),
                'created_at'      => now()->subDays(4),
            ],
            // Approved - Payoneer
            [
                'seller_index'    => 4,
                'amount'          => 620.00,
                'method'          => 'payoneer',
                'payment_details' => [
                    'email' => 'finance@nextgengames.gg',
                ],
                'status'          => 'approved',
                'note'            => null,
                'admin_note'      => 'Payoneer payment initiated',
                'transaction_id'  => 'PYR-9847201',
                'processed_at'    => now()->subDays(8),
                'created_at'      => now()->subDays(9),
            ],

            // Rejected - PayPal
            [
                'seller_index'    => 2,
                'amount'          => 5000.00,
                'method'          => 'paypal',
                'payment_details' => [
                    'email' => 'wrong-email@test.com',
                ],
                'status'          => 'rejected',
                'note'            => 'Large withdrawal',
                'admin_note'      => 'PayPal email does not match verified seller account. Please update your payment info and resubmit.',
                'processed_at'    => now()->subDays(6),
                'created_at'      => now()->subDays(7),
            ],
            // Rejected - Bank
            [
                'seller_index'    => 3,
                'amount'          => 300.00,
                'method'          => 'bank',
                'payment_details' => [
                    'bank_name'      => 'Unknown Bank',
                    'account_name'   => 'Test Account',
                    'account_number' => '000000',
                    'routing_number' => null,
                    'branch_name'    => null,
                ],
                'status'          => 'rejected',
                'note'            => null,
                'admin_note'      => 'Incomplete bank details. Please provide a valid account number and routing/SWIFT code.',
                'processed_at'    => now()->subDays(14),
                'created_at'      => now()->subDays(15),
            ],

            // Cancelled
            [
                'seller_index'    => 4,
                'amount'          => 100.00,
                'method'          => 'skrill',
                'payment_details' => [
                    'email' => 'old-email@nextgengames.gg',
                ],
                'status'          => 'cancelled',
                'note'            => 'Wrong email, will resubmit',
                'created_at'      => now()->subDays(3),
            ],
        ];

        foreach ($withdrawals as $data) {
            $seller = $sellers[$data['seller_index']] ?? $sellers->first();

            SellerWithdraw::create([
                'seller_id'       => $seller->id,
                'amount'          => $data['amount'],
                'method'          => $data['method'],
                'payment_details' => $data['payment_details'],
                'status'          => $data['status'],
                'note'            => $data['note'] ?? null,
                'admin_note'      => $data['admin_note'] ?? null,
                'transaction_id'  => $data['transaction_id'] ?? null,
                'processed_at'    => $data['processed_at'] ?? null,
                'created_at'      => $data['created_at'],
                'updated_at'      => $data['processed_at'] ?? $data['created_at'],
            ]);
        }
    }
}
