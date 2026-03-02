<?php

namespace Database\Seeders;

use App\Models\CannedResponse;
use Illuminate\Database\Seeder;

class CannedResponseSeeder extends Seeder
{
    public function run(): void
    {
        $responses = [
            // Greetings
            ['title' => 'Welcome Greeting', 'body' => "Hello {customer_name},\n\nThank you for reaching out to us. I'd be happy to help you with your inquiry. Let me look into this right away.", 'category' => 'greeting', 'shortcut' => '/hello', 'sort_order' => 1],
            ['title' => 'Follow-up Greeting', 'body' => "Hi {customer_name},\n\nThank you for your patience. I have an update regarding your inquiry.", 'category' => 'greeting', 'shortcut' => '/followup', 'sort_order' => 2],

            // Order
            ['title' => 'Order Being Processed', 'body' => "Your order #{order_number} is currently being processed. You'll receive a confirmation email with tracking details once it's shipped. Estimated delivery time is 3-5 business days.", 'category' => 'order', 'shortcut' => '/processing', 'sort_order' => 10],
            ['title' => 'Order Shipped', 'body' => "Great news! Your order #{order_number} has been shipped. You can track your package using the tracking number provided in your shipping confirmation email.", 'category' => 'order', 'shortcut' => '/shipped', 'sort_order' => 11],
            ['title' => 'Order Delayed', 'body' => "I understand your concern about the delay with order #{order_number}. I've checked with the seller and the item is expected to ship within the next 24-48 hours. I sincerely apologize for the inconvenience.", 'category' => 'order', 'shortcut' => '/delayed', 'sort_order' => 12],
            ['title' => 'Wrong Item Received', 'body' => "I'm sorry to hear you received the wrong item. We'll arrange for a replacement to be sent right away. Please keep the incorrect item — we'll provide you with a prepaid return label if needed.", 'category' => 'order', 'shortcut' => '/wrongitem', 'sort_order' => 13],
            ['title' => 'Order Cancellation', 'body' => "Your order #{order_number} has been cancelled as requested. If a payment was already made, the refund will be processed within 5-7 business days. Please let me know if you need anything else.", 'category' => 'order', 'shortcut' => '/cancelled', 'sort_order' => 14],

            // Payment
            ['title' => 'Payment Received', 'body' => "Your payment for order #{order_number} has been successfully received and confirmed. Your order is now being processed.", 'category' => 'payment', 'shortcut' => '/paidok', 'sort_order' => 20],
            ['title' => 'Payment Failed', 'body' => "It appears the payment for your order was not successful. This could be due to insufficient funds, an expired card, or a temporary bank issue. Please try again or use an alternative payment method.", 'category' => 'payment', 'shortcut' => '/paymentfail', 'sort_order' => 21],
            ['title' => 'Double Charge Explanation', 'body' => "I've reviewed your account and can see the duplicate charge. The first transaction was a pre-authorization hold that will automatically drop off within 3-5 business days. You will only be charged once. If it persists beyond that, please let us know.", 'category' => 'payment', 'shortcut' => '/doublecharge', 'sort_order' => 22],

            // Refund
            ['title' => 'Refund Initiated', 'body' => "A refund of {amount} has been initiated for your order #{order_number}. Please allow 5-10 business days for the amount to appear in your account. You'll receive a confirmation email shortly.", 'category' => 'refund', 'shortcut' => '/refunded', 'sort_order' => 30],
            ['title' => 'Refund Policy', 'body' => "Our refund policy allows returns within 30 days of delivery for most items in unused condition with original packaging. Digital products are eligible for refund within 14 days if not activated. Once approved, refunds are processed within 5-10 business days.", 'category' => 'refund', 'shortcut' => '/refundpolicy', 'sort_order' => 31],
            ['title' => 'Partial Refund', 'body' => "Based on our review, we're processing a partial refund of {amount} due to {reason}. The amount will be credited to your original payment method within 5-10 business days.", 'category' => 'refund', 'shortcut' => '/partrefund', 'sort_order' => 32],

            // Shipping
            ['title' => 'Tracking Information', 'body' => "Here are the tracking details for your order #{order_number}:\n\nCarrier: {carrier}\nTracking Number: {tracking}\n\nYou can track your package at the carrier's website.", 'category' => 'shipping', 'shortcut' => '/tracking', 'sort_order' => 40],
            ['title' => 'Delivery Address Change', 'body' => "I've updated the shipping address for your order #{order_number}. Please note that address changes are only possible before the item has been shipped. The updated delivery estimate will be sent to your email.", 'category' => 'shipping', 'shortcut' => '/addrchange', 'sort_order' => 41],

            // Product
            ['title' => 'Product Key / Digital Delivery', 'body' => "Your product key/digital content for order #{order_number} has been sent to your registered email address. Please check your inbox and spam folder. If you haven't received it, I can resend it for you.", 'category' => 'product', 'shortcut' => '/key', 'sort_order' => 50],
            ['title' => 'Product Out of Stock', 'body' => "I'm sorry, the product you're interested in is currently out of stock. We expect it to be back in stock within {timeframe}. I can set up a notification for you when it becomes available.", 'category' => 'product', 'shortcut' => '/outofstock', 'sort_order' => 51],
            ['title' => 'Product Activation Help', 'body' => "To activate your product, please follow these steps:\n\n1. Go to the product's official website\n2. Create or log in to your account\n3. Navigate to 'Redeem Code' or 'Activate'\n4. Enter the key provided in your order confirmation\n\nIf you encounter any issues, please share a screenshot and I'll assist you further.", 'category' => 'product', 'shortcut' => '/activate', 'sort_order' => 52],

            // Account
            ['title' => 'Password Reset', 'body' => "To reset your password, please click the 'Forgot Password' link on the login page and enter your registered email address. You'll receive a reset link within a few minutes. If you don't see the email, please check your spam folder.", 'category' => 'account', 'shortcut' => '/resetpw', 'sort_order' => 60],
            ['title' => 'Account Verification', 'body' => "Your account needs to be verified before you can proceed. A verification email has been sent to your registered email address. Please click the verification link to activate your account.", 'category' => 'account', 'shortcut' => '/verify', 'sort_order' => 61],

            // Closing
            ['title' => 'Resolution Closing', 'body' => "I'm glad we could resolve this for you! If you need any further assistance in the future, don't hesitate to reach out. We're always here to help.\n\nBest regards,\n{agent_name}", 'category' => 'closing', 'shortcut' => '/resolved', 'sort_order' => 90],
            ['title' => 'Awaiting Response', 'body' => "I'm following up on your ticket. If you need further assistance, please let us know. Otherwise, this ticket will be automatically closed after 72 hours of inactivity.", 'category' => 'closing', 'shortcut' => '/waiting', 'sort_order' => 91],
            ['title' => 'Escalation Notice', 'body' => "I've escalated your case to our senior support team for further review. They will reach out to you within 24 hours. Thank you for your patience.", 'category' => 'closing', 'shortcut' => '/escalated', 'sort_order' => 92],

            // General
            ['title' => 'Request More Info', 'body' => "To better assist you, could you please provide the following information:\n\n1. Your order number (if applicable)\n2. A screenshot of the issue\n3. The device/browser you're using\n\nThis will help us resolve your issue faster.", 'category' => 'general', 'shortcut' => '/moreinfo', 'sort_order' => 70],
            ['title' => 'Seller Contact', 'body' => "I've forwarded your inquiry to the seller for this product. They typically respond within 24 hours. I'll keep monitoring this ticket and step in if needed to ensure your issue is resolved.", 'category' => 'general', 'shortcut' => '/sellercontact', 'sort_order' => 71],
            ['title' => 'Apology for Inconvenience', 'body' => "I sincerely apologize for the inconvenience you've experienced. This is not the level of service we strive to provide. We're taking immediate steps to resolve your issue and prevent this from happening again.", 'category' => 'general', 'shortcut' => '/sorry', 'sort_order' => 72],
        ];

        foreach ($responses as $r) {
            CannedResponse::firstOrCreate(
                ['shortcut' => $r['shortcut']],
                $r
            );
        }
    }
}
