<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class StaticPageController extends Controller
{
    private array $pageGroups = [
        'about'   => 'about_page',
        'contact' => 'contact_page',
        'faq'     => 'faq_page',
        'privacy' => 'privacy_page',
        'terms'   => 'terms_page',
    ];

    public function edit(string $page)
    {
        if (!isset($this->pageGroups[$page])) {
            abort(404);
        }

        $group = $this->pageGroups[$page];
        $settings = Setting::group($group);

        return view("content.static-pages.{$page}", compact('settings', 'page'));
    }

    public function update(Request $request, string $page)
    {
        if (!isset($this->pageGroups[$page])) {
            abort(404);
        }

        $group = $this->pageGroups[$page];

        foreach ($request->except('_token', '_method') as $key => $value) {
            if ($key === 'hero_image_file') continue;
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $value = $decoded;
                }
            }
            Setting::set($group, $key, $value);
        }

        if ($request->hasFile('hero_image_file')) {
            $dir = public_path('uploads/pages');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $file = $request->file('hero_image_file');
            $filename = $page . '_hero_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            Setting::set($group, 'hero_image', '/uploads/pages/' . $filename);
        }

        Cache::forget("settings.group.{$group}");

        return redirect()->route('static-pages.edit', $page)
            ->with('success', ucfirst($page) . ' page updated successfully.');
    }
}
