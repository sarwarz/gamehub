<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

/**
 * @group Blog Categories
 *
 * Public APIs for blog categories used
 * for blog listing, filtering, and SEO.
 *
 * @unauthenticated
 */
class BlogCategoryController extends Controller
{
    /**
     * List blog categories
     *
     * Retrieve active blog categories.
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Blog categories fetched successfully",
     *   "data": []
     * }
     */
    public function index()
    {
        try {
            $categories = BlogCategory::where('is_active', true)
                ->orderBy('position')
                ->get();

            return $this->success(
                $categories->map(fn ($category) => $this->transform($category)),
                'Blog categories fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch blog categories');
        }
    }

    /**
     * Get category by slug
     *
     * Retrieve a single blog category using slug.
     *
     * @urlParam slug string required Category slug. Example: windows
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Blog category fetched successfully",
     *   "data": {
     *     "name": "Windows"
     *   }
     * }
     */
    public function show(string $slug)
    {
        try {
            $category = BlogCategory::where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if (!$category) {
                return $this->error('Blog category not found', 404);
            }

            return $this->success(
                $this->transform($category, true),
                'Blog category fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch blog category');
        }
    }

    /* --------------------------------
     | Data Transformer
     |-------------------------------- */

    protected function transform(BlogCategory $category, bool $full = false): array
    {
        return [
            'id'          => $category->id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'description' => $full ? $category->description : null,
            'position'    => $category->position,
            'meta' => [
                'title'       => $category->meta_title ?? $category->name,
                'description' => $category->meta_description,
                'keywords'    => $category->meta_keywords,
            ],
        ];
    }
}
