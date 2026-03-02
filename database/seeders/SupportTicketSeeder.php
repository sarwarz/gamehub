<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))->pluck('id')->toArray();
        $admins    = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'superadmin']))->pluck('id')->toArray();
        $sellerIds = Seller::pluck('id')->toArray();
        $orders    = Order::pluck('id')->toArray();

        if (empty($customers)) {
            $customers = User::where('id', '>', 2)->pluck('id')->toArray();
        }
        if (empty($admins)) {
            $admins = [1];
        }

        $now = Carbon::now();

        $tickets = [
            // ── OPEN tickets ──────────────────────────────────
            [
                'department' => 'order',
                'subject'    => 'I did not receive my game key after payment',
                'priority'   => 'high',
                'status'     => 'open',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "Hi, I placed an order about 30 minutes ago and the payment was confirmed, but I still haven't received the game key. My order status shows 'completed' but I can't find the key anywhere in my account. Can you help?"],
                ],
                'created_ago' => 30,
            ],
            [
                'department' => 'payment',
                'subject'    => 'Double charged for my order',
                'priority'   => 'urgent',
                'status'     => 'open',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "I just noticed I was charged twice for order. My bank shows two transactions of the same amount. I only placed one order. Please investigate and refund the duplicate charge immediately."],
                ],
                'created_ago' => 15,
            ],
            [
                'department' => 'product',
                'subject'    => 'Game key is showing as already redeemed',
                'priority'   => 'high',
                'status'     => 'open',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "I tried to activate the key I received for my purchase but Steam says the key has already been redeemed by another account. This is a brand new purchase and I've never shared the key with anyone. Please provide a working replacement key."],
                ],
                'created_ago' => 45,
            ],

            // ── AWAITING CUSTOMER ─────────────────────────────
            [
                'department' => 'order',
                'subject'    => 'Wrong product delivered',
                'priority'   => 'medium',
                'status'     => 'awaiting_customer',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "I ordered a Windows 11 Pro key but received a Windows 11 Home key instead. This is not what I paid for. Please send me the correct product."],
                    ['role' => 'admin', 'msg' => "I'm sorry to hear about this mix-up. I've checked your order and can confirm the issue. Could you please provide a screenshot of the key you received so I can verify it with the seller and arrange a replacement?", 'delay' => 120],
                ],
                'created_ago' => 180,
            ],
            [
                'department' => 'account',
                'subject'    => 'Cannot change my email address',
                'priority'   => 'low',
                'status'     => 'awaiting_customer',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "I'm trying to update my email address in my account settings but I keep getting an error message saying 'email already in use'. I don't have another account with this email."],
                    ['role' => 'admin', 'msg' => "Thank you for reaching out. I've looked into this and it appears there may be an older deactivated account using that email address. Could you provide the new email address you'd like to use? I can check and resolve this for you.", 'delay' => 60],
                ],
                'created_ago' => 300,
            ],

            // ── AWAITING SELLER ───────────────────────────────
            [
                'department' => 'product',
                'subject'    => 'Key not working on the specified platform',
                'priority'   => 'medium',
                'status'     => 'awaiting_seller',
                'has_seller' => true,
                'messages'   => [
                    ['role' => 'customer', 'msg' => "The product listing said this key works on Epic Games Store, but when I try to activate it there, it says the key format is not recognized. Is this actually a Steam key instead?"],
                    ['role' => 'admin', 'msg' => "I've forwarded your concern to the seller for clarification. They should respond within 24 hours. We'll make sure you get the correct key for the platform advertised.", 'delay' => 90],
                ],
                'created_ago' => 240,
            ],

            // ── AWAITING ADMIN ────────────────────────────────
            [
                'department' => 'payment',
                'subject'    => 'Refund not received after 10 days',
                'priority'   => 'high',
                'status'     => 'awaiting_admin',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "I was told my refund would be processed within 5-7 business days, but it's been 10 days and I still haven't received anything. My refund request was approved on the 15th."],
                    ['role' => 'admin', 'msg' => "I apologize for the delay. Let me escalate this to our finance team to check the refund status. I'll get back to you shortly.", 'delay' => 60],
                    ['role' => 'customer', 'msg' => "It's been another 2 days since your last message. Can you please provide an update? This is getting frustrating.", 'delay' => 2880],
                ],
                'created_ago' => 4500,
            ],

            // ── ON HOLD ───────────────────────────────────────
            [
                'department' => 'general',
                'subject'    => 'Request for bulk purchase discount',
                'priority'   => 'low',
                'status'     => 'on_hold',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "Hello, I'm an IT manager at a mid-sized company. We need to purchase about 50 Windows licenses and 30 Office licenses. Do you offer any bulk pricing or corporate discounts?"],
                    ['role' => 'admin', 'msg' => "Thank you for your interest in bulk purchasing! I've forwarded your request to our sales team. They'll prepare a custom quote for you. This may take 1-2 business days. I'll keep this ticket on hold until we hear back.", 'delay' => 120],
                ],
                'created_ago' => 1440,
            ],

            // ── ESCALATED ─────────────────────────────────────
            [
                'department' => 'order',
                'subject'    => 'Order stuck in processing for 3 days',
                'priority'   => 'urgent',
                'status'     => 'escalated',
                'is_escalated' => true,
                'messages'   => [
                    ['role' => 'customer', 'msg' => "My order has been stuck in 'processing' status for 3 days now. I've already contacted you twice about this. The money has been deducted from my account but I haven't received anything."],
                    ['role' => 'admin', 'msg' => "I sincerely apologize for this experience. I can see the order is indeed stuck. Let me investigate what's causing the delay.", 'delay' => 30],
                    ['role' => 'admin', 'msg' => "I've identified the issue — the seller's inventory system had a sync error. I'm escalating this to our senior team to manually process your order and ensure delivery within the next few hours.", 'delay' => 120, 'internal' => false],
                    ['role' => 'customer', 'msg' => "Thank you for looking into it. Please let me know as soon as it's resolved.", 'delay' => 60],
                ],
                'created_ago' => 4320,
            ],

            // ── RESOLVED ──────────────────────────────────────
            [
                'department' => 'product',
                'subject'    => 'How to activate Office 365 key',
                'priority'   => 'low',
                'status'     => 'resolved',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "Hi, I just bought an Office 365 key but I'm not sure how to activate it. Can you provide instructions?"],
                    ['role' => 'admin', 'msg' => "Of course! Here's how to activate your Office 365 key:\n\n1. Go to office.com/setup\n2. Sign in with your Microsoft account (or create one)\n3. Enter the product key from your order\n4. Follow the on-screen instructions to complete activation\n5. Download and install Office from your account page\n\nLet me know if you run into any issues!", 'delay' => 45],
                    ['role' => 'customer', 'msg' => "That worked perfectly! Thank you for the quick help.", 'delay' => 60],
                    ['role' => 'admin', 'msg' => "Glad it worked! If you need anything else in the future, don't hesitate to reach out. Enjoy Office 365!", 'delay' => 30],
                ],
                'created_ago' => 2880,
            ],
            [
                'department' => 'payment',
                'subject'    => 'Wallet balance not updating after deposit',
                'priority'   => 'medium',
                'status'     => 'resolved',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "I deposited $50 into my wallet about an hour ago. The payment went through on PayPal but my wallet balance still shows $0."],
                    ['role' => 'admin', 'msg' => "I can see the PayPal transaction was received but there was a slight delay in the webhook processing. I've manually credited the $50.00 to your wallet. Your current balance should now show $50.00. Please refresh the page and check.", 'delay' => 90],
                    ['role' => 'customer', 'msg' => "Yes, I can see it now. Thank you for the quick resolution!", 'delay' => 20],
                ],
                'created_ago' => 1440,
            ],

            // ── CLOSED ────────────────────────────────────────
            [
                'department' => 'general',
                'subject'    => 'How do I become a seller on the platform?',
                'priority'   => 'low',
                'status'     => 'closed',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "I'm interested in selling game keys on your platform. What's the process to become a verified seller?"],
                    ['role' => 'admin', 'msg' => "Great to hear your interest! To become a seller:\n\n1. Go to your account dashboard\n2. Click 'Apply as Seller' in the menu\n3. Fill out the seller application form with your business details\n4. Our team will review your application within 2-3 business days\n5. Once approved, you can start listing products\n\nSeller commission rates and policies are available on our Seller FAQ page. Let me know if you have any questions!", 'delay' => 60],
                    ['role' => 'customer', 'msg' => "Perfect, I've submitted my application. Thanks!", 'delay' => 120],
                ],
                'created_ago' => 10080,
            ],
            [
                'department' => 'order',
                'subject'    => 'Can I get an invoice for my purchase?',
                'priority'   => 'low',
                'status'     => 'closed',
                'messages'   => [
                    ['role' => 'customer', 'msg' => "I need an invoice for my recent purchase for tax purposes. Is there a way to download it from my account?"],
                    ['role' => 'admin', 'msg' => "Yes! You can download invoices directly from your order history. Go to 'My Orders', click on the specific order, and you'll find a 'Download Invoice' button. The invoice includes all the details needed for tax records. Let me know if you need anything else!", 'delay' => 30],
                    ['role' => 'customer', 'msg' => "Found it, thanks!", 'delay' => 15],
                ],
                'created_ago' => 7200,
            ],
        ];

        foreach ($tickets as $idx => $data) {
            $customerId = $customers[array_rand($customers)];
            $adminId    = $admins[array_rand($admins)];
            $createdAt  = $now->copy()->subMinutes($data['created_ago']);

            $ticketData = [
                'user_id'           => $customerId,
                'department'        => $data['department'],
                'subject'           => $data['subject'],
                'priority'          => $data['priority'],
                'status'            => $data['status'],
                'assigned_admin_id' => $adminId,
                'is_escalated'      => $data['is_escalated'] ?? false,
                'ip_address'        => '127.0.0.' . ($idx + 1),
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ];

            if (!empty($orders) && $data['department'] === 'order') {
                $ticketData['order_id'] = $orders[array_rand($orders)];
            }

            if (!empty($data['has_seller']) && !empty($sellerIds)) {
                $ticketData['seller_id'] = $sellerIds[array_rand($sellerIds)];
            }

            if (in_array($data['status'], ['resolved', 'closed'])) {
                $ticketData['resolved_at'] = $createdAt->copy()->addHours(rand(1, 24));
            }
            if ($data['status'] === 'closed') {
                $ticketData['closed_at'] = ($ticketData['resolved_at'] ?? $createdAt)->copy()->addHours(rand(24, 72));
            }
            if (!empty($data['is_escalated'])) {
                $ticketData['escalated_at'] = $createdAt->copy()->addHours(rand(2, 12));
            }

            $ticket = SupportTicket::create($ticketData);

            $msgTime = $createdAt->copy();
            foreach ($data['messages'] as $m) {
                if (!empty($m['delay'])) {
                    $msgTime = $msgTime->copy()->addMinutes($m['delay']);
                }

                $senderId = $m['role'] === 'customer'
                    ? $customerId
                    : $adminId;

                SupportTicketMessage::create([
                    'ticket_id'      => $ticket->id,
                    'user_id'        => $senderId,
                    'sender_role'    => $m['role'] === 'customer' ? 'customer' : 'admin',
                    'message'        => $m['msg'],
                    'is_internal_note' => $m['internal'] ?? false,
                    'created_at'     => $msgTime,
                    'updated_at'     => $msgTime,
                ]);
            }

            $ticket->update(['last_reply_at' => $msgTime]);
        }
    }
}
