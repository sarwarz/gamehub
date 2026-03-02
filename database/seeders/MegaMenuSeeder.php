<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MegaMenuSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================================
        //  1. SEED PAGES
        // ==========================================================
        $pages = [
            [
                'title'            => 'About Us',
                'slug'             => 'about-us',
                'content'          => '<h2>About GameHub</h2><p>GameHub is your one-stop destination for digital games, software, gift cards, and subscriptions at the best prices. We partner with authorized sellers worldwide to bring you legitimate product keys delivered instantly.</p><h3>Our Mission</h3><p>To make digital entertainment accessible and affordable for everyone, everywhere.</p><h3>Why Choose Us?</h3><ul><li>Instant digital delivery</li><li>Best price guarantee</li><li>24/7 customer support</li><li>Verified sellers only</li><li>Secure payment methods</li></ul>',
                'meta_title'       => 'About Us - GameHub',
                'meta_description' => 'Learn about GameHub, your trusted marketplace for digital games, software, and gift cards.',
                'meta_keywords'    => 'about gamehub, digital games store, game keys marketplace',
                'is_active'        => true,
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'position'         => 1,
            ],
            [
                'title'            => 'Contact Us',
                'slug'             => 'contact-us',
                'content'          => '<h2>Get in Touch</h2><p>Have a question, suggestion, or need help with an order? Our support team is here for you.</p><h3>Contact Information</h3><ul><li><strong>Email:</strong> support@gamehub.com</li><li><strong>Live Chat:</strong> Available 24/7 on our website</li><li><strong>Response Time:</strong> Within 24 hours</li></ul><h3>Business Inquiries</h3><p>For partnerships, wholesale, or press inquiries, please email <strong>business@gamehub.com</strong></p>',
                'meta_title'       => 'Contact Us - GameHub',
                'meta_description' => 'Contact GameHub support team for help with orders, accounts, or general inquiries.',
                'meta_keywords'    => 'contact gamehub, support, help, customer service',
                'is_active'        => true,
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'position'         => 2,
            ],
            [
                'title'            => 'Privacy Policy',
                'slug'             => 'privacy-policy',
                'content'          => '<h2>Privacy Policy</h2><p>Last updated: February 2026</p><p>GameHub respects your privacy and is committed to protecting your personal data. This policy explains how we collect, use, and safeguard your information.</p><h3>Information We Collect</h3><ul><li>Account information (name, email, password)</li><li>Payment information (processed securely via third-party providers)</li><li>Usage data (browsing history, preferences)</li><li>Device information (IP address, browser type)</li></ul><h3>How We Use Your Data</h3><ul><li>Process orders and deliver products</li><li>Provide customer support</li><li>Improve our services</li><li>Send promotional communications (with your consent)</li></ul><h3>Your Rights</h3><p>You can request access, correction, or deletion of your personal data at any time by contacting our support team.</p>',
                'meta_title'       => 'Privacy Policy - GameHub',
                'meta_description' => 'Read GameHub privacy policy to understand how we collect, use, and protect your personal data.',
                'meta_keywords'    => 'privacy policy, data protection, gamehub privacy',
                'is_active'        => true,
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'position'         => 3,
            ],
            [
                'title'            => 'Terms & Conditions',
                'slug'             => 'terms-and-conditions',
                'content'          => '<h2>Terms & Conditions</h2><p>Last updated: February 2026</p><p>By using GameHub, you agree to these terms. Please read them carefully.</p><h3>Account Terms</h3><ul><li>You must be at least 18 years old to create an account</li><li>You are responsible for maintaining account security</li><li>One account per person</li></ul><h3>Purchases & Refunds</h3><ul><li>All sales are for digital products delivered electronically</li><li>Product keys are non-transferable once revealed</li><li>Refunds are available for undelivered or invalid keys</li><li>Refund requests must be made within 14 days of purchase</li></ul><h3>Seller Terms</h3><p>Sellers must provide legitimate, authorized product keys. Violation results in immediate account suspension.</p>',
                'meta_title'       => 'Terms & Conditions - GameHub',
                'meta_description' => 'Read the terms and conditions for using GameHub marketplace.',
                'meta_keywords'    => 'terms, conditions, user agreement, gamehub terms',
                'is_active'        => true,
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'position'         => 4,
            ],
            [
                'title'            => 'Refund Policy',
                'slug'             => 'refund-policy',
                'content'          => '<h2>Refund Policy</h2><p>Last updated: February 2026</p><p>At GameHub, customer satisfaction is our priority. If you experience any issues with your purchase, we are here to help.</p><h3>Eligible for Refund</h3><ul><li>Product key not delivered within 24 hours</li><li>Invalid or already-used product key</li><li>Product significantly different from description</li></ul><h3>Not Eligible for Refund</h3><ul><li>Key has been revealed/redeemed by the buyer</li><li>Buyer changed their mind after key reveal</li><li>Technical issues on buyer\'s device</li></ul><h3>How to Request</h3><p>Open a support ticket within 14 days of purchase with your order details. Our team will investigate and respond within 48 hours.</p>',
                'meta_title'       => 'Refund Policy - GameHub',
                'meta_description' => 'Understand GameHub refund policy for digital product purchases.',
                'meta_keywords'    => 'refund policy, returns, money back, gamehub refund',
                'is_active'        => true,
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'position'         => 5,
            ],
            [
                'title'            => 'Cookie Policy',
                'slug'             => 'cookie-policy',
                'content'          => '<h2>Cookie Policy</h2><p>Last updated: February 2026</p><p>GameHub uses cookies to enhance your browsing experience and analyze site traffic.</p><h3>Types of Cookies</h3><ul><li><strong>Essential:</strong> Required for site functionality (login, cart)</li><li><strong>Analytics:</strong> Help us understand how visitors use our site</li><li><strong>Marketing:</strong> Used for targeted advertising (with consent)</li></ul><h3>Managing Cookies</h3><p>You can manage cookie preferences in your browser settings. Disabling essential cookies may affect site functionality.</p>',
                'meta_title'       => 'Cookie Policy - GameHub',
                'meta_description' => 'Learn about how GameHub uses cookies on our website.',
                'meta_keywords'    => 'cookie policy, cookies, tracking, gamehub',
                'is_active'        => true,
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'position'         => 6,
            ],
            [
                'title'            => 'FAQ',
                'slug'             => 'faq',
                'content'          => '<h2>Frequently Asked Questions</h2><h3>How does GameHub work?</h3><p>GameHub is a marketplace where verified sellers list digital product keys. After purchase, you receive your key instantly via email and in your account.</p><h3>Are the keys legitimate?</h3><p>Yes. All sellers are verified and must provide authorized product keys. We have a strict anti-fraud system in place.</p><h3>How fast is delivery?</h3><p>Most keys are delivered instantly after payment confirmation. In rare cases, it may take up to 24 hours.</p><h3>What payment methods do you accept?</h3><p>We accept credit/debit cards, PayPal, cryptocurrency, and wallet balance.</p><h3>Can I sell on GameHub?</h3><p>Yes! Apply to become a seller through our seller application page. Once approved, you can start listing products.</p>',
                'meta_title'       => 'FAQ - GameHub',
                'meta_description' => 'Find answers to frequently asked questions about GameHub marketplace.',
                'meta_keywords'    => 'faq, help, questions, gamehub support',
                'is_active'        => true,
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'position'         => 7,
            ],
            [
                'title'            => 'How It Works',
                'slug'             => 'how-it-works',
                'content'          => '<h2>How GameHub Works</h2><h3>1. Browse & Search</h3><p>Explore thousands of digital games, software, and gift cards. Use filters to find exactly what you need.</p><h3>2. Compare Prices</h3><p>Multiple sellers offer the same product — compare prices and choose the best deal.</p><h3>3. Purchase Securely</h3><p>Pay with your preferred method. All transactions are protected by our buyer guarantee.</p><h3>4. Get Your Key</h3><p>Receive your product key instantly. Redeem it on the appropriate platform and enjoy!</p>',
                'meta_title'       => 'How It Works - GameHub',
                'meta_description' => 'Learn how to buy digital games and software on GameHub in 4 simple steps.',
                'meta_keywords'    => 'how it works, buy games, digital delivery, gamehub guide',
                'is_active'        => true,
                'show_in_header'   => true,
                'show_in_footer'   => true,
                'position'         => 8,
            ],
            [
                'title'            => 'Seller Program',
                'slug'             => 'seller-program',
                'content'          => '<h2>Become a GameHub Seller</h2><p>Join thousands of sellers on GameHub and reach millions of buyers worldwide.</p><h3>Benefits</h3><ul><li>Access to a global customer base</li><li>Low commission rates</li><li>Seller dashboard with analytics</li><li>Fast payouts</li><li>Dedicated seller support</li></ul><h3>Requirements</h3><ul><li>Valid business registration</li><li>Authorized product supply chain</li><li>Minimum 100 keys to start</li></ul><h3>How to Apply</h3><p>Visit our seller application page, fill in your details, and our team will review your application within 3 business days.</p>',
                'meta_title'       => 'Seller Program - GameHub',
                'meta_description' => 'Join GameHub as a seller and start selling digital products to millions of buyers.',
                'meta_keywords'    => 'sell on gamehub, seller program, become a seller, marketplace',
                'is_active'        => true,
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'position'         => 9,
            ],
            [
                'title'            => 'Order Status',
                'slug'             => 'order-status',
                'content'          => '<h2>Check Your Order Status</h2><p>Track the status of your order by logging into your account and visiting the Orders section.</p><h3>Order Statuses</h3><ul><li><strong>Pending:</strong> Payment is being processed</li><li><strong>Processing:</strong> Seller is preparing your key</li><li><strong>Completed:</strong> Key delivered — check your email or account</li><li><strong>Cancelled:</strong> Order was cancelled — refund initiated</li><li><strong>Refunded:</strong> Refund has been processed</li></ul><h3>Need Help?</h3><p>If your order is stuck or you have questions, open a support ticket and our team will assist you promptly.</p>',
                'meta_title'       => 'Order Status - GameHub',
                'meta_description' => 'Check and track your order status on GameHub.',
                'meta_keywords'    => 'order status, track order, order tracking, gamehub orders',
                'is_active'        => true,
                'show_in_header'   => false,
                'show_in_footer'   => false,
                'position'         => 10,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }

        // ==========================================================
        //  2. CLEAR EXISTING MENU DATA & REBUILD
        // ==========================================================
        MenuItem::query()->delete();
        Menu::query()->delete();

        // ==========================================================
        //  3. HEADER MEGA MENU
        // ==========================================================
        $header = Menu::create([
            'name'      => 'Main Navigation',
            'slug'      => 'main-navigation',
            'location'  => 'header',
            'is_active' => true,
        ]);

        $p = 0;

        $shop = $this->add($header, null, $p++, 'Shop', 'megamenu', '/shop', 'ti tabler-shopping-cart', 4);

            $games = $this->add($header, $shop->id, $p++, 'Games', 'link', '/shop?category=games', 'ti tabler-device-gamepad-2');
                $this->add($header, $games->id, $p++, 'PC Games', 'heading');
                $this->add($header, $games->id, $p++, 'Steam',           'link', '/shop?category=games&platform=steam');
                $this->add($header, $games->id, $p++, 'Epic Games',      'link', '/shop?category=games&platform=epic-games');
                $this->add($header, $games->id, $p++, 'GOG',             'link', '/shop?category=games&platform=gog');
                $this->add($header, $games->id, $p++, 'Origin',          'link', '/shop?category=games&platform=origin');
                $this->add($header, $games->id, $p++, 'Uplay',           'link', '/shop?category=games&platform=uplay');
                $this->add($header, $games->id, $p++, 'Console Games',   'heading');
                $this->add($header, $games->id, $p++, 'Xbox',            'link', '/shop?category=games&platform=xbox');
                $this->add($header, $games->id, $p++, 'PlayStation',     'link', '/shop?category=games&platform=playstation');
                $this->add($header, $games->id, $p++, 'Nintendo Switch', 'link', '/shop?category=games&platform=nintendo-switch');

            $software = $this->add($header, $shop->id, $p++, 'Software', 'link', '/shop?type=software', 'ti tabler-app-window');
                $this->add($header, $software->id, $p++, 'Office & Productivity', 'heading');
                $this->add($header, $software->id, $p++, 'Office Suites',         'link', '/shop?category=office-productivity');
                $this->add($header, $software->id, $p++, 'Project Management',    'link', '/shop?category=office-productivity&type=software');
                $this->add($header, $software->id, $p++, 'Antivirus & Security',  'heading');
                $this->add($header, $software->id, $p++, 'Antivirus',             'link', '/shop?category=antivirus-security');
                $this->add($header, $software->id, $p++, 'VPN Services',          'link', '/shop?category=antivirus-security&type=subscription');
                $this->add($header, $software->id, $p++, 'Design & Creative',     'heading');
                $this->add($header, $software->id, $p++, 'Graphic Design',        'link', '/shop?category=graphic-design');
                $this->add($header, $software->id, $p++, 'Video Editing',         'link', '/shop?category=graphic-design&type=software');
                $this->add($header, $software->id, $p++, 'Development Tools',     'heading');
                $this->add($header, $software->id, $p++, 'IDEs & Code Editors',   'link', '/shop?category=software-development-tools');
                $this->add($header, $software->id, $p++, 'API Tools',             'link', '/shop?category=software-development-tools&type=software');

            $gift = $this->add($header, $shop->id, $p++, 'Gift Cards', 'link', '/shop?category=gift-cards', 'ti tabler-gift');
                $this->add($header, $gift->id, $p++, 'Gaming',            'heading');
                $this->add($header, $gift->id, $p++, 'Steam Wallet',      'link', '/shop?category=gift-cards&platform=steam');
                $this->add($header, $gift->id, $p++, 'Xbox Gift Card',    'link', '/shop?category=gift-cards&platform=xbox');
                $this->add($header, $gift->id, $p++, 'PlayStation Store',  'link', '/shop?category=gift-cards&platform=playstation');
                $this->add($header, $gift->id, $p++, 'Nintendo eShop',    'link', '/shop?category=gift-cards&platform=nintendo-switch');
                $this->add($header, $gift->id, $p++, 'Entertainment',     'heading');
                $this->add($header, $gift->id, $p++, 'Netflix',           'link', '/shop?category=gift-cards&type=gift-card');
                $this->add($header, $gift->id, $p++, 'Spotify',           'link', '/shop?category=gift-cards&type=gift-card');
                $this->add($header, $gift->id, $p++, 'Apple iTunes',      'link', '/shop?category=gift-cards&type=gift-card');

            $this->add($header, $shop->id, $p++, 'DLCs & Add-Ons',    'link', '/shop?type=dlc',          'ti tabler-puzzle');
            $this->add($header, $shop->id, $p++, 'Subscriptions',     'link', '/shop?type=subscription', 'ti tabler-calendar-repeat');
            $this->add($header, $shop->id, $p++, 'Operating Systems', 'link', '/shop?category=operating-systems', 'ti tabler-device-desktop');

        $this->add($header, null, $p++, 'All Products', 'link', '/shop', 'ti tabler-packages');
        $this->add($header, null, $p++, 'Blog', 'link', '/blog', 'ti tabler-news');
        $this->add($header, null, $p++, 'How It Works', 'link', '/page/how-it-works', 'ti tabler-help-circle');
        $this->add($header, null, $p++, 'Order Status', 'link', '/page/order-status', 'ti tabler-truck-delivery');

        // ==========================================================
        //  4. FOOTER MENU
        // ==========================================================
        $footer = Menu::create([
            'name'      => 'Footer Navigation',
            'slug'      => 'footer-navigation',
            'location'  => 'footer',
            'is_active' => true,
        ]);

        $f = 0;

        $company = $this->add($footer, null, $f++, 'Company', 'heading');
        $this->add($footer, $company->id, $f++, 'About Us',      'link', '/page/about-us');
        $this->add($footer, $company->id, $f++, 'Contact Us',    'link', '/page/contact-us');
        $this->add($footer, $company->id, $f++, 'How It Works',  'link', '/page/how-it-works');
        $this->add($footer, $company->id, $f++, 'Seller Program','link', '/page/seller-program');
        $this->add($footer, $company->id, $f++, 'Blog',          'link', '/blog');

        $help = $this->add($footer, null, $f++, 'Help & Support', 'heading');
        $this->add($footer, $help->id, $f++, 'FAQ',             'link', '/page/faq');
        $this->add($footer, $help->id, $f++, 'Order Status',    'link', '/page/order-status');
        $this->add($footer, $help->id, $f++, 'Support Tickets', 'link', '/tickets');
        $this->add($footer, $help->id, $f++, 'Live Chat',       'link', '/contact');

        $legal = $this->add($footer, null, $f++, 'Legal', 'heading');
        $this->add($footer, $legal->id, $f++, 'Privacy Policy',       'link', '/page/privacy-policy');
        $this->add($footer, $legal->id, $f++, 'Terms & Conditions',   'link', '/page/terms-and-conditions');
        $this->add($footer, $legal->id, $f++, 'Refund Policy',        'link', '/page/refund-policy');
        $this->add($footer, $legal->id, $f++, 'Cookie Policy',        'link', '/page/cookie-policy');

        $shopCol = $this->add($footer, null, $f++, 'Shop', 'heading');
        $this->add($footer, $shopCol->id, $f++, 'Games',            'link', '/shop?category=games');
        $this->add($footer, $shopCol->id, $f++, 'Software',         'link', '/shop?type=software');
        $this->add($footer, $shopCol->id, $f++, 'Gift Cards',       'link', '/shop?category=gift-cards');
        $this->add($footer, $shopCol->id, $f++, 'DLCs & Add-Ons',   'link', '/shop?type=dlc');
        $this->add($footer, $shopCol->id, $f++, 'Subscriptions',    'link', '/shop?type=subscription');

        $platCol = $this->add($footer, null, $f++, 'Platforms', 'heading');
        $this->add($footer, $platCol->id, $f++, 'Steam',        'link', '/shop?platform=steam');
        $this->add($footer, $platCol->id, $f++, 'Epic Games',   'link', '/shop?platform=epic-games');
        $this->add($footer, $platCol->id, $f++, 'Xbox',         'link', '/shop?platform=xbox');
        $this->add($footer, $platCol->id, $f++, 'PlayStation',  'link', '/shop?platform=playstation');
        $this->add($footer, $platCol->id, $f++, 'GOG',          'link', '/shop?platform=gog');
        $this->add($footer, $platCol->id, $f++, 'Nintendo',     'link', '/shop?platform=nintendo-switch');

        // ==========================================================
        //  5. SIDEBAR MENU (Browse filters)
        // ==========================================================
        $sidebar = Menu::create([
            'name'      => 'Shop Sidebar',
            'slug'      => 'shop-sidebar',
            'location'  => 'sidebar',
            'is_active' => true,
        ]);

        $s = 0;

        $catGroup = $this->add($sidebar, null, $s++, 'Categories', 'heading');
        $this->add($sidebar, $catGroup->id, $s++, 'Games',                'link', '/shop?category=games');
        $this->add($sidebar, $catGroup->id, $s++, 'Operating Systems',    'link', '/shop?category=operating-systems');
        $this->add($sidebar, $catGroup->id, $s++, 'Office & Productivity','link', '/shop?category=office-productivity');
        $this->add($sidebar, $catGroup->id, $s++, 'Antivirus & Security', 'link', '/shop?category=antivirus-security');
        $this->add($sidebar, $catGroup->id, $s++, 'Gift Cards',           'link', '/shop?category=gift-cards');
        $this->add($sidebar, $catGroup->id, $s++, 'Dev Tools',            'link', '/shop?category=software-development-tools');
        $this->add($sidebar, $catGroup->id, $s++, 'Graphic Design',       'link', '/shop?category=graphic-design');

        $platGroup = $this->add($sidebar, null, $s++, 'Platforms', 'heading');
        $this->add($sidebar, $platGroup->id, $s++, 'Steam',          'link', '/shop?platform=steam');
        $this->add($sidebar, $platGroup->id, $s++, 'Epic Games',     'link', '/shop?platform=epic-games');
        $this->add($sidebar, $platGroup->id, $s++, 'Origin',         'link', '/shop?platform=origin');
        $this->add($sidebar, $platGroup->id, $s++, 'Uplay',          'link', '/shop?platform=uplay');
        $this->add($sidebar, $platGroup->id, $s++, 'GOG',            'link', '/shop?platform=gog');
        $this->add($sidebar, $platGroup->id, $s++, 'Xbox',           'link', '/shop?platform=xbox');
        $this->add($sidebar, $platGroup->id, $s++, 'PlayStation',    'link', '/shop?platform=playstation');
        $this->add($sidebar, $platGroup->id, $s++, 'Nintendo Switch','link', '/shop?platform=nintendo-switch');
        $this->add($sidebar, $platGroup->id, $s++, 'Windows',        'link', '/shop?platform=windows');
        $this->add($sidebar, $platGroup->id, $s++, 'Mac OS',         'link', '/shop?platform=mac-os');

        $typeGroup = $this->add($sidebar, null, $s++, 'Product Types', 'heading');
        $this->add($sidebar, $typeGroup->id, $s++, 'Games',         'link', '/shop?type=game');
        $this->add($sidebar, $typeGroup->id, $s++, 'Software',      'link', '/shop?type=software');
        $this->add($sidebar, $typeGroup->id, $s++, 'Gift Cards',    'link', '/shop?type=gift-card');
        $this->add($sidebar, $typeGroup->id, $s++, 'DLCs',          'link', '/shop?type=dlc');
        $this->add($sidebar, $typeGroup->id, $s++, 'Subscriptions', 'link', '/shop?type=subscription');

        $regionGroup = $this->add($sidebar, null, $s++, 'Regions', 'heading');
        $this->add($sidebar, $regionGroup->id, $s++, 'Global',           'link', '/shop?region=global');
        $this->add($sidebar, $regionGroup->id, $s++, 'Europe (EU)',      'link', '/shop?region=europe-eu');
        $this->add($sidebar, $regionGroup->id, $s++, 'North America',    'link', '/shop?region=north-america-na');
        $this->add($sidebar, $regionGroup->id, $s++, 'Asia',             'link', '/shop?region=asia');
        $this->add($sidebar, $regionGroup->id, $s++, 'Middle East',      'link', '/shop?region=middle-east');

        $osGroup = $this->add($sidebar, null, $s++, 'Works On', 'heading');
        $this->add($sidebar, $osGroup->id, $s++, 'Windows', 'link', '/shop?works-on=windows');
        $this->add($sidebar, $osGroup->id, $s++, 'Mac OS',  'link', '/shop?works-on=mac-os');
        $this->add($sidebar, $osGroup->id, $s++, 'Linux',   'link', '/shop?works-on=linux');
        $this->add($sidebar, $osGroup->id, $s++, 'Android', 'link', '/shop?works-on=android');
        $this->add($sidebar, $osGroup->id, $s++, 'iOS',     'link', '/shop?works-on=ios');
    }

    private function add(
        Menu    $menu,
        ?int    $parentId,
        int     $position,
        string  $title,
        string  $type = 'link',
        string  $url = '#',
        ?string $icon = null,
        int     $columns = 4,
    ): MenuItem {
        return MenuItem::create([
            'menu_id'   => $menu->id,
            'parent_id' => $parentId,
            'title'     => $title,
            'type'      => $type,
            'columns'   => $columns,
            'url'       => $url,
            'icon'      => $icon,
            'target'    => '_self',
            'position'  => $position,
            'is_active' => true,
        ]);
    }
}
