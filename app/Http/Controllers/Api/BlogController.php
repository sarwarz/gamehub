<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

/**
 * @group Blogs
 *
 * Public APIs for blog listing, reading articles,
 * and displaying blog details with comments.
 *
 * @unauthenticated
 */
class BlogController extends Controller
{
    /**
     * List blogs
     *
     * Retrieve published blog posts.
     *
     * @queryParam category_id int Optional. Filter by category ID. Example: 2
     * @queryParam search string Optional. Search by title. Example: windows
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Blogs fetched successfully",
     *   "data": []
     * }
     */
    public function index(Request $request)
    {
        $blogs = Blog::with('category')
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            })
            ->when($request->category_id, fn ($q) =>
                $q->where('blog_category_id', $request->category_id)
            )
            ->when($request->search, fn ($q) =>
                $q->where('title', 'like', '%' . $request->search . '%')
            )
            ->orderBy('position')
            ->latest('published_at')
            ->paginate(10);

        return $this->successResponse(
            $blogs->through(fn ($blog) => $this->transform($blog)),
            'Blogs fetched successfully'
        );
    }

    /**
     * Get blog by slug
     *
     * Retrieve a single blog post using slug.
     *
     * @urlParam slug string required Blog slug. Example: windows-11-activation-guide
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Blog fetched successfully",
     *   "data": {
     *     "title": "Windows 11 Activation Guide"
     *   }
     * }
     */
    public function show(string $slug)
    {
        $blog = Blog::with(['category', 'comments'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (!$blog) {
            return $this->errorResponse('Blog not found', 404);
        }

        // Increase view count (safe)
        $blog->increment('views');

        return $this->successResponse(
            $this->transform($blog, true),
            'Blog fetched successfully'
        );
    }

    /* --------------------------------
     | Data Transformer
     |-------------------------------- */

    protected function transform(Blog $blog, bool $full = false): array
    {
        return [
            'id'       => $blog->id,
            'title'    => $blog->title,
            'slug'     => $blog->slug,
            'excerpt'  => $full ? null : str(strip_tags($blog->content))->limit(160),
            'content'  => $full ? $blog->content : null,
            'image'    => asset($blog->featured_image),
            'views'    => $blog->views,
            'published_at' => $blog->published_at,
            'category' => $blog->category ? [
                'id'   => $blog->category->id,
                'name' => $blog->category->name,
                'slug' => $blog->category->slug,
            ] : null,
            'comments' => $full
                ? $blog->comments->map(fn ($comment) => [
                    'id'      => $comment->id,
                    'name'    => $comment->name,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                ])
                : null,
            'meta' => [
                'title'       => $blog->meta_title ?? $blog->title,
                'description' => $blog->meta_description,
                'keywords'    => $blog->meta_keywords,
            ],
        ];
    }

    /* --------------------------------
     | API Response Helpers
     |-------------------------------- */

    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], $code);
    }
}
