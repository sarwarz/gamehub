<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

/**
 * @group Static Pages
 *
 * Public static page content endpoints.
 * Returns content for About Us, Contact, Privacy Policy, and Terms & Conditions pages.
 *
 * @unauthenticated
 */
class StaticPageApiController extends Controller
{
    /**
     * Get About Us page
     *
     * Returns the About Us page content, hero section, stats, and SEO metadata.
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "About page fetched successfully",
     *   "data": {
     *     "title": "About Us",
     *     "hero_title": "About GameHub",
     *     "hero_subtitle": "Your Trusted Digital Marketplace",
     *     "hero_image": "",
     *     "content": "<h2>Who We Are</h2>...",
     *     "stats": [{"value": "10K+", "label": "Happy Customers"}],
     *     "meta": {"title": "About Us - GameHub", "description": "Learn about GameHub"}
     *   }
     * }
     */
    public function about(): JsonResponse
    {
        try {
            $s = Setting::group('about_page');

            return $this->success([
                'title'         => $s['title'] ?? 'About Us',
                'hero_title'    => $s['hero_title'] ?? '',
                'hero_subtitle' => $s['hero_subtitle'] ?? '',
                'hero_image'    => $s['hero_image'] ?? '',
                'content'       => $s['content'] ?? '',
                'stats'         => $s['stats'] ?? [],
                'team_enabled'  => filter_var($s['team_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'team_members'  => $s['team_members'] ?? [],
                'meta'          => [
                    'title'       => $s['meta_title'] ?? '',
                    'description' => $s['meta_description'] ?? '',
                ],
            ], 'About page fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch about page');
        }
    }

    /**
     * Get Contact Us page
     *
     * Returns the Contact Us page content, contact details, map embed, and form status.
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "Contact page fetched successfully",
     *   "data": {
     *     "title": "Contact Us",
     *     "hero_title": "Get In Touch",
     *     "hero_subtitle": "We'd love to hear from you",
     *     "address": "123 Commerce Street, Digital City, DC 12345",
     *     "phone": "+1 (555) 123-4567",
     *     "email": "support@gamehub.com",
     *     "working_hours": "Mon - Fri: 9:00 AM - 6:00 PM",
     *     "map_embed": "",
     *     "form_enabled": true,
     *     "meta": {"title": "Contact Us - GameHub", "description": "Get in touch"}
     *   }
     * }
     */
    public function contact(): JsonResponse
    {
        try {
            $s = Setting::group('contact_page');

            return $this->success([
                'title'         => $s['title'] ?? 'Contact Us',
                'hero_title'    => $s['hero_title'] ?? '',
                'hero_subtitle' => $s['hero_subtitle'] ?? '',
                'address'       => $s['address'] ?? '',
                'phone'         => $s['phone'] ?? '',
                'email'         => $s['email'] ?? '',
                'working_hours' => $s['working_hours'] ?? '',
                'map_embed'     => $s['map_embed'] ?? '',
                'form_enabled'  => filter_var($s['form_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'meta'          => [
                    'title'       => $s['meta_title'] ?? '',
                    'description' => $s['meta_description'] ?? '',
                ],
            ], 'Contact page fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch contact page');
        }
    }

    /**
     * Get Privacy Policy page
     *
     * Returns the Privacy Policy page content and SEO metadata.
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "Privacy policy fetched successfully",
     *   "data": {
     *     "title": "Privacy Policy",
     *     "content": "<h2>Privacy Policy</h2>...",
     *     "last_updated": "2026-02-28",
     *     "meta": {"title": "Privacy Policy - GameHub", "description": "Read our privacy policy"}
     *   }
     * }
     */
    public function privacy(): JsonResponse
    {
        try {
            $s = Setting::group('privacy_page');

            return $this->success([
                'title'        => $s['title'] ?? 'Privacy Policy',
                'content'      => $s['content'] ?? '',
                'last_updated' => $s['last_updated'] ?? null,
                'meta'         => [
                    'title'       => $s['meta_title'] ?? '',
                    'description' => $s['meta_description'] ?? '',
                ],
            ], 'Privacy policy fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch privacy policy');
        }
    }

    /**
     * Get Terms & Conditions page
     *
     * Returns the Terms & Conditions page content and SEO metadata.
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "Terms & conditions fetched successfully",
     *   "data": {
     *     "title": "Terms & Conditions",
     *     "content": "<h2>Terms & Conditions</h2>...",
     *     "last_updated": "2026-02-28",
     *     "meta": {"title": "Terms & Conditions - GameHub", "description": "Read our terms"}
     *   }
     * }
     */
    public function terms(): JsonResponse
    {
        try {
            $s = Setting::group('terms_page');

            return $this->success([
                'title'        => $s['title'] ?? 'Terms & Conditions',
                'content'      => $s['content'] ?? '',
                'last_updated' => $s['last_updated'] ?? null,
                'meta'         => [
                    'title'       => $s['meta_title'] ?? '',
                    'description' => $s['meta_description'] ?? '',
                ],
            ], 'Terms & conditions fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch terms & conditions');
        }
    }
}
