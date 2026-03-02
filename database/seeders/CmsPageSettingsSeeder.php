<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CmsPageSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $homepage = [
            'hero_slider' => [
                'enabled' => true, 'autoplay' => true, 'speed' => 5000,
                'type' => 'hero',
            ],
            'category_bar' => [
                'enabled' => true, 'limit' => 6,
            ],
            'featured_products' => [
                'enabled' => true, 'title' => 'Featured Products',
                'subtitle' => 'Discover our hand-picked selection',
                'limit' => 8, 'sort' => 'featured',
            ],
            'promotional_banner' => [
                'enabled' => true,
                'title' => 'Digital Marketplace Mega Sale',
                'subtitle' => 'Up to 60% Off',
                'description' => 'Get the best deals on games, software, and digital content.',
                'image' => '',
                'video_url' => '',
                'button_text' => 'Shop Now', 'button_url' => '/shop',
                'button2_text' => 'Learn More', 'button2_url' => '',
                'badge_text' => 'LIMITED',
                'bg_color' => '#1a1a2e',
            ],
            'new_arrivals' => [
                'enabled' => true, 'title' => 'New Arrivals',
                'subtitle' => 'Latest additions to our catalog',
                'limit' => 8,
            ],
            'categories_grid' => [
                'enabled' => true,
                'title' => 'Explore Our Vast Collection',
                'subtitle' => 'Browse by category to find exactly what you need',
                'limit' => 6,
            ],
            'stats_counter' => [
                'enabled' => true,
                'items' => [
                    ['value' => '500+', 'label' => 'Digital Products', 'icon' => 'tabler-package'],
                    ['value' => '10K+', 'label' => 'Happy Customers', 'icon' => 'tabler-users'],
                    ['value' => '97%', 'label' => 'Satisfaction Rate', 'icon' => 'tabler-thumb-up'],
                    ['value' => '24/7', 'label' => 'Support Available', 'icon' => 'tabler-headset'],
                ],
            ],
            'hot_deals' => [
                'enabled' => true,
                'title' => 'HOT DEALS! HURRY UP',
                'subtitle' => 'Limited time offers',
                'limit' => 5, 'show_timer' => true,
                'banner_image' => '', 'banner_discount' => '80%',
                'banner_event' => '12.12',
            ],
            'blog_section' => [
                'enabled' => true,
                'title' => 'Blog GAMEHUB',
                'subtitle' => 'Latest news and articles',
                'limit' => 4,
            ],
            'newsletter' => [
                'enabled' => true,
                'title' => 'Stay Updated with the Latest',
                'subtitle' => 'Get Our Community',
                'description' => 'Subscribe to our newsletter for exclusive deals and updates.',
                'button_text' => 'Subscribe',
            ],
            'sections_order' => [
                'hero_slider', 'category_bar', 'featured_products',
                'promotional_banner', 'new_arrivals', 'categories_grid',
                'stats_counter', 'hot_deals', 'blog_section', 'newsletter',
            ],
        ];

        foreach ($homepage as $key => $value) {
            Setting::set('homepage', $key, $value);
        }

        $shoppage = [
            'layout' => [
                'default_view' => 'grid',
                'products_per_page' => 12,
                'default_sort' => 'featured',
                'sidebar_position' => 'left',
                'columns' => 4,
            ],
            'filters' => [
                'price_range' => true,
                'categories' => true,
                'platforms' => true,
                'types' => true,
                'regions' => true,
                'languages' => true,
                'works_on' => true,
                'developers' => true,
                'publishers' => false,
            ],
            'banner' => [
                'enabled' => false,
                'title' => '', 'image' => '', 'url' => '',
            ],
            'seo' => [
                'title' => 'All Products',
                'description' => 'Discover thousands of games, software, and digital content',
                'keywords' => 'games, software, digital products',
            ],
        ];

        foreach ($shoppage as $key => $value) {
            Setting::set('shoppage', $key, $value);
        }

        $footer = [
            'about' => [
                'show_logo' => true,
                'description' => 'Your trusted marketplace for digital products and gaming keys.',
                'show_social' => true,
            ],
            'columns' => [
                [
                    'title' => 'Quick Links',
                    'links' => [
                        ['label' => 'About Us', 'url' => '/about'],
                        ['label' => 'Products', 'url' => '/shop'],
                        ['label' => 'Blog', 'url' => '/blog'],
                        ['label' => 'Contact', 'url' => '/contact'],
                    ],
                ],
                [
                    'title' => 'Customer Service',
                    'links' => [
                        ['label' => 'Help Center', 'url' => '/help'],
                        ['label' => 'Returns', 'url' => '/returns'],
                        ['label' => 'Shipping Info', 'url' => '/shipping'],
                        ['label' => 'FAQ', 'url' => '/faq'],
                    ],
                ],
                [
                    'title' => 'Contact Us',
                    'items' => [
                        ['icon' => 'tabler-map-pin', 'text' => '123 Commerce Street, Digital City, DC 12345'],
                        ['icon' => 'tabler-phone', 'text' => '+1 (555) 123-4567'],
                        ['icon' => 'tabler-mail', 'text' => 'support@gamehub.com'],
                    ],
                ],
            ],
            'bottom_bar' => [
                'copyright' => '© {year} GameHub. All rights reserved.',
                'links' => [
                    ['label' => 'Privacy Policy', 'url' => '/privacy'],
                    ['label' => 'Terms of Service', 'url' => '/terms'],
                    ['label' => 'Cookie Policy', 'url' => '/cookies'],
                ],
            ],
            'payment_icons' => [
                'enabled' => true,
                'methods' => ['visa', 'mastercard', 'paypal', 'stripe', 'apple_pay'],
            ],
        ];

        foreach ($footer as $key => $value) {
            Setting::set('footer', $key, $value);
        }
    }
}
