<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class WebsiteController extends Controller
{
    /* ── Home Page ── */

    public function homepage()
    {
        $settings = Setting::group('homepage');
        return view('content.website.homepage', compact('settings'));
    }

    public function updateHomepage(Request $request)
    {
        $sections = [
            'hero_slider', 'category_bar', 'featured_products', 'promotional_banner',
            'new_arrivals', 'categories_grid', 'stats_counter', 'hot_deals',
            'blog_section', 'newsletter', 'sections_order',
        ];

        foreach ($sections as $section) {
            if ($request->has($section)) {
                $data = $request->input($section);
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    }
                }
                Setting::set('homepage', $section, $data);
            }
        }

        if ($request->hasFile('promotional_banner_image')) {
            $path = $this->uploadFile($request->file('promotional_banner_image'), 'uploads/website');
            $banner = Setting::get('homepage', 'promotional_banner', []);
            $banner['image'] = $path;
            Setting::set('homepage', 'promotional_banner', $banner);
        }

        if ($request->hasFile('hot_deals_banner_image')) {
            $path = $this->uploadFile($request->file('hot_deals_banner_image'), 'uploads/website');
            $deals = Setting::get('homepage', 'hot_deals', []);
            $deals['banner_image'] = $path;
            Setting::set('homepage', 'hot_deals', $deals);
        }

        Cache::forget('settings.group.homepage');

        return redirect()->route('website.homepage')
            ->with('success', 'Home page settings updated successfully.');
    }

    /* ── Shop Page ── */

    public function shoppage()
    {
        $settings = Setting::group('shoppage');
        return view('content.website.shoppage', compact('settings'));
    }

    public function updateShoppage(Request $request)
    {
        $sections = ['layout', 'filters', 'banner', 'seo'];

        foreach ($sections as $section) {
            if ($request->has($section)) {
                $data = $request->input($section);
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    }
                }
                Setting::set('shoppage', $section, $data);
            }
        }

        if ($request->hasFile('banner_image')) {
            $path = $this->uploadFile($request->file('banner_image'), 'uploads/website');
            $banner = Setting::get('shoppage', 'banner', []);
            $banner['image'] = $path;
            Setting::set('shoppage', 'banner', $banner);
        }

        Cache::forget('settings.group.shoppage');

        return redirect()->route('website.shoppage')
            ->with('success', 'Shop page settings updated successfully.');
    }

    /* ── Footer ── */

    public function footer()
    {
        $settings = Setting::group('footer');
        return view('content.website.footer', compact('settings'));
    }

    public function updateFooter(Request $request)
    {
        $sections = ['about', 'columns', 'bottom_bar', 'payment_icons'];

        foreach ($sections as $section) {
            if ($request->has($section)) {
                $data = $request->input($section);
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    }
                }
                Setting::set('footer', $section, $data);
            }
        }

        Cache::forget('settings.group.footer');

        return redirect()->route('website.footer')
            ->with('success', 'Footer settings updated successfully.');
    }

    /* ── Helpers ── */

    protected function uploadFile($file, string $folder): string
    {
        $dir = public_path($folder);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return '/' . $folder . '/' . $filename;
    }
}
