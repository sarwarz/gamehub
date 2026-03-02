<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Http\Request;

/**
 * @group Blog Comments
 *
 * APIs for reading and submitting blog comments.
 */
class BlogCommentController extends Controller
{
    /**
     * List blog comments
     *
     * Retrieve approved comments for a blog post.
     *
     * @urlParam blog int required Blog ID. Example: 5
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Comments fetched successfully",
     *   "data": []
     * }
     */
    public function index(Blog $blog)
    {
        try {
            $comments = BlogComment::with('user')
                ->where('blog_id', $blog->id)
                ->where('is_approved', true)
                ->latest()
                ->get();

            return $this->success(
                $comments->map(fn ($comment) => $this->transform($comment)),
                'Comments fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch comments');
        }
    }

    /**
     * Submit blog comment
     *
     * Submit a comment for a blog post.
     * Comment will be pending approval.
     *
     * @authenticated
     *
     * @urlParam blog int required Blog ID. Example: 5
     *
     * @bodyParam comment string required Comment content. Example: Great article!
     *
     * @response 201 {
     *   "status": true,
     *   "message": "Comment submitted successfully and awaiting approval"
     * }
     */
    public function store(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $data['comment'] = strip_tags($data['comment']);

        try {
            BlogComment::create([
                'blog_id'     => $blog->id,
                'user_id'     => $request->user()->id,
                'comment'     => $data['comment'],
                'is_approved' => false,
            ]);

            return $this->success(
                null,
                'Comment submitted successfully and awaiting approval',
                201
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to submit comment');
        }
    }

    /* --------------------------------
     | Data Transformer
     |-------------------------------- */

    protected function transform(BlogComment $comment): array
    {
        return [
            'id'         => $comment->id,
            'comment'    => $comment->comment,
            'created_at' => $comment->created_at,
            'user'       => $comment->user ? [
                'id'   => $comment->user->id,
                'name' => $comment->user->name,
            ] : null,
        ];
    }
}
