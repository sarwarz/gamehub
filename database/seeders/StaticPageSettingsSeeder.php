<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class StaticPageSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'about_page' => [
                'title'            => 'About Us',
                'meta_title'       => 'About Us - GameHub',
                'meta_description' => 'Learn about GameHub, your trusted digital marketplace.',
                'content'          => '<h2>Who We Are</h2><p>GameHub is your trusted marketplace for digital products and gaming keys.</p><h2>Our Mission</h2><p>To provide the best digital products at competitive prices with exceptional customer service.</p>',
                'hero_title'       => 'About GameHub',
                'hero_subtitle'    => 'Your Trusted Digital Marketplace',
                'hero_image'       => '',
                'team_enabled'     => false,
                'team_members'     => [],
                'stats' => [
                    ['value' => '10K+', 'label' => 'Happy Customers'],
                    ['value' => '500+', 'label' => 'Digital Products'],
                    ['value' => '50+', 'label' => 'Sellers'],
                    ['value' => '24/7', 'label' => 'Support'],
                ],
            ],
            'contact_page' => [
                'title'            => 'Contact Us',
                'meta_title'       => 'Contact Us - GameHub',
                'meta_description' => 'Get in touch with the GameHub team.',
                'hero_title'       => 'Get In Touch',
                'hero_subtitle'    => 'We\'d love to hear from you',
                'address'          => '123 Commerce Street, Digital City, DC 12345',
                'phone'            => '+1 (555) 123-4567',
                'email'            => 'support@gamehub.com',
                'working_hours'    => 'Mon - Fri: 9:00 AM - 6:00 PM',
                'map_embed'        => '',
                'form_enabled'     => true,
            ],
            'privacy_page' => [
                'title'            => 'Privacy Policy',
                'meta_title'       => 'Privacy Policy - GameHub',
                'meta_description' => 'Read our privacy policy to understand how we protect your data.',
                'content'          => '<h2>Privacy Policy</h2><p>Last updated: ' . date('F d, Y') . '</p><p>This Privacy Policy describes how GameHub collects, uses, and shares your personal information.</p><h3>1. Information We Collect</h3><p>We collect information you provide directly, such as your name, email address, and payment information when you create an account or make a purchase.</p><h3>2. How We Use Your Information</h3><p>We use the information we collect to provide, maintain, and improve our services.</p><h3>3. Information Sharing</h3><p>We do not sell your personal information. We may share information with service providers who help us operate our platform.</p>',
                'last_updated'     => date('Y-m-d'),
            ],
            'terms_page' => [
                'title'            => 'Terms & Conditions',
                'meta_title'       => 'Terms & Conditions - GameHub',
                'meta_description' => 'Read our terms and conditions for using GameHub.',
                'content'          => '<h2>Terms & Conditions</h2><p>Last updated: ' . date('F d, Y') . '</p><p>Welcome to GameHub. By using our services, you agree to these terms.</p><h3>1. Acceptance of Terms</h3><p>By accessing or using GameHub, you agree to be bound by these Terms & Conditions.</p><h3>2. User Accounts</h3><p>You are responsible for maintaining the confidentiality of your account credentials.</p><h3>3. Purchases & Refunds</h3><p>All purchases are subject to our refund policy. Digital products may have different refund terms.</p>',
                'last_updated'     => date('Y-m-d'),
            ],
            'faq_page' => [
                'title'            => 'Frequently Asked Questions',
                'meta_title'       => 'FAQ - GameHub',
                'meta_description' => 'Find answers to common questions about GameHub.',
                'hero_title'       => 'How can we help?',
                'hero_subtitle'    => 'Search our FAQ or browse categories below',
            ],
        ];

        foreach ($pages as $group => $data) {
            foreach ($data as $key => $value) {
                Setting::set($group, $key, $value);
            }
        }
    }
}
