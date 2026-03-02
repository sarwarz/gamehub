<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class FaqController extends Controller
{
    public function index()
    {
        $categories = FaqCategory::with(['faqs' => fn($q) => $q->orderBy('position')])
            ->orderBy('position')
            ->get();
        $settings = Setting::group('faq_page');

        return view('content.faqs.index', compact('categories', 'settings'));
    }

    public function update(Request $request)
    {
        foreach (['title', 'meta_title', 'meta_description', 'hero_title', 'hero_subtitle'] as $key) {
            if ($request->has($key)) {
                Setting::set('faq_page', $key, $request->input($key));
            }
        }
        Cache::forget('settings.group.faq_page');

        $categoriesData = json_decode($request->input('categories_data', '[]'), true);
        if (is_array($categoriesData)) {
            $existingCatIds = [];
            $existingFaqIds = [];

            foreach ($categoriesData as $catPos => $cat) {
                if (!empty($cat['id']) && $cat['id'] !== 'new') {
                    $category = FaqCategory::find($cat['id']);
                    if ($category) {
                        $category->update([
                            'name'      => $cat['name'],
                            'slug'      => Str::slug($cat['name']),
                            'icon'      => $cat['icon'] ?? null,
                            'position'  => $catPos,
                            'is_active' => $cat['is_active'] ?? true,
                        ]);
                    }
                } else {
                    $category = FaqCategory::create([
                        'name'      => $cat['name'],
                        'slug'      => Str::slug($cat['name']),
                        'icon'      => $cat['icon'] ?? null,
                        'position'  => $catPos,
                        'is_active' => $cat['is_active'] ?? true,
                    ]);
                }

                $existingCatIds[] = $category->id;

                foreach (($cat['faqs'] ?? []) as $faqPos => $faqData) {
                    if (!empty($faqData['id']) && $faqData['id'] !== 'new') {
                        $faq = Faq::find($faqData['id']);
                        if ($faq) {
                            $faq->update([
                                'faq_category_id' => $category->id,
                                'question'        => $faqData['question'],
                                'answer'          => $faqData['answer'],
                                'position'        => $faqPos,
                                'is_active'       => $faqData['is_active'] ?? true,
                            ]);
                        }
                    } else {
                        $faq = Faq::create([
                            'faq_category_id' => $category->id,
                            'question'        => $faqData['question'],
                            'answer'          => $faqData['answer'],
                            'position'        => $faqPos,
                            'is_active'       => $faqData['is_active'] ?? true,
                        ]);
                    }
                    $existingFaqIds[] = $faq->id;
                }
            }

            Faq::whereNotIn('id', $existingFaqIds)->delete();
            FaqCategory::whereNotIn('id', $existingCatIds)->delete();
        }

        return redirect()->route('faqs.index')->with('success', 'FAQ page updated successfully.');
    }
}
