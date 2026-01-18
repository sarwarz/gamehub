<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

/**
 * @group Pages
 *
 * CMS APIs for static pages such as About Us,
 * Privacy Policy, Terms & Conditions, etc.
 */
class PageController extends Controller
{
    /**
     * List pages
     *
     * Retrieve active CMS pages.
     * Supports header/footer filtering.
     *
     * @queryParam header boolean Optional. Pages shown in header. Example: true
     * @queryParam footer boolean Optional. Pages shown in footer. Example: true
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Pages fetched successfully",
     *   "data": []
     * }
     */
    public function index(Request $request)
    {
        $pages = Page::where('is_active', true)
            ->when($request->header, fn ($q) => $q->where('show_in_header', true))
            ->when($request->footer, fn ($q) => $q->where('show_in_footer', true))
            ->orderBy('position')
            ->get();

        return $this->successResponse(
            $pages->map(fn ($page) => $this->transform($page)),
            'Pages fetched successfully'
        );
    }

    /**
     * Get page by slug
     *
     * Retrieve a CMS page using slug.
     *
     * @urlParam page string required Page slug. Example: privacy-policy
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Page fetched successfully",
     *   "data": {
     *     "title": "Privacy Policy"
     *   }
     * }
     */
    public function show(Page $page)
    {
        if (!$page->is_active) {
            return $this->errorResponse('Page not found', 404);
        }

        return $this->successResponse(
            $this->transform($page, true),
            'Page fetched successfully'
        );
    }

    /* --------------------------------
     | Data Transformer
     |-------------------------------- */

    protected function transform(Page $page, bool $full = false): array
    {
        return [
            'id'       => $page->id,
            'title'    => $page->title,
            'slug'     => $page->slug,
            'content'  => $full ? $page->content : null,
            'image'    => $page->featured_image,
            'meta'     => [
                'title'       => $page->meta_title ?? $page->title,
                'description' => $page->meta_description,
                'keywords'    => $page->meta_keywords,
            ],
            'header'   => $page->show_in_header,
            'footer'   => $page->show_in_footer,
            'position' => $page->position,
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
