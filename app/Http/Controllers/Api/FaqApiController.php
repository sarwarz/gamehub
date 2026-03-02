<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

/**
 * @group FAQ
 *
 * Public FAQ endpoints.
 * Returns frequently asked questions grouped by category.
 *
 * @unauthenticated
 */
class FaqApiController extends Controller
{
    /**
     * Get FAQ page data
     *
     * Returns the full FAQ page including page settings,
     * categories, and all questions grouped by category.
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "FAQ page fetched successfully",
     *   "data": {
     *     "page": {
     *       "title": "FAQ",
     *       "hero_title": "How can we help?",
     *       "hero_subtitle": "Search our FAQ",
     *       "meta_title": "FAQ - GameHub",
     *       "meta_description": "Find answers..."
     *     },
     *     "categories": [
     *       {
     *         "id": 1,
     *         "name": "General",
     *         "slug": "general",
     *         "icon": "tabler-info-circle",
     *         "faqs": [
     *           {
     *             "id": 1,
     *             "question": "How do I create an account?",
     *             "answer": "Click the sign up button..."
     *           }
     *         ]
     *       }
     *     ]
     *   }
     * }
     */
    public function index(): JsonResponse
    {
        try {
            $pageSettings = Setting::group('faq_page');

            $categories = FaqCategory::active()
                ->with(['activeFaqs'])
                ->orderBy('position')
                ->get()
                ->map(fn($cat) => [
                    'id'   => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'icon' => $cat->icon,
                    'faqs' => $cat->activeFaqs->map(fn($faq) => [
                        'id'       => $faq->id,
                        'question' => $faq->question,
                        'answer'   => $faq->answer,
                    ])->values(),
                ]);

            return $this->success([
                'page'       => [
                    'title'            => $pageSettings['title'] ?? 'FAQ',
                    'hero_title'       => $pageSettings['hero_title'] ?? '',
                    'hero_subtitle'    => $pageSettings['hero_subtitle'] ?? '',
                    'meta_title'       => $pageSettings['meta_title'] ?? '',
                    'meta_description' => $pageSettings['meta_description'] ?? '',
                ],
                'categories' => $categories,
            ], 'FAQ page fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch FAQ page');
        }
    }

    /**
     * Get FAQ by category slug
     *
     * Returns FAQs for a specific category.
     *
     * @urlParam slug string required The category slug. Example: general
     *
     * @response 200 scenario="success" {
     *   "status": true,
     *   "message": "FAQ category fetched successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "General",
     *     "slug": "general",
     *     "icon": "tabler-info-circle",
     *     "faqs": []
     *   }
     * }
     * @response 404 scenario="not found" {
     *   "status": false,
     *   "message": "Category not found."
     * }
     */
    public function byCategory(string $slug): JsonResponse
    {
        try {
            $category = FaqCategory::active()
                ->where('slug', $slug)
                ->with('activeFaqs')
                ->first();

            if (!$category) {
                return $this->error('Category not found.', 404);
            }

            return $this->success([
                'id'   => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'faqs' => $category->activeFaqs->map(fn($faq) => [
                    'id'       => $faq->id,
                    'question' => $faq->question,
                    'answer'   => $faq->answer,
                ])->values(),
            ], 'FAQ category fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch FAQ category');
        }
    }
}
