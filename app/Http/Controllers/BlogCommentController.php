<?php

namespace App\Http\Controllers;

use App\Models\BlogComment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $comments = BlogComment::with(['blog', 'user'])->latest();

            return DataTables::of($comments)
                ->addColumn('blog', fn ($c) =>
                    '<strong>'.$c->blog->title.'</strong>'
                )

                ->addColumn('user', fn ($c) =>
                    '<strong>'.$c->user->name.'</strong><br>
                     <small>'.$c->user->email.'</small>'
                )

                ->addColumn('status', fn ($c) =>
                    $c->is_approved
                        ? '<span class="badge bg-success">Approved</span>'
                        : '<span class="badge bg-warning">Pending</span>'
                )

                ->addColumn('actions', fn ($c) => '
                    <button class="btn btn-sm btn-success approve-btn"
                            data-id="'.$c->id.'">
                        Approve
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn"
                            data-id="'.$c->id.'">
                        Delete
                    </button>
                ')

                ->rawColumns(['blog','user','status','actions'])
                ->make(true);
        }

        return view('content.blog-comments.index');
    }

    public function approve(BlogComment $blogComment)
    {
        $blogComment->update(['is_approved' => true]);

        return response()->json(['message' => 'Approved']);
    }

    public function destroy(BlogComment $blogComment)
    {
        $blogComment->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
