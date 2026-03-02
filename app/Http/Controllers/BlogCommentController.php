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

            if ($request->filled('status')) {
                $comments->where('is_approved', $request->status === 'approved');
            }

            return DataTables::of($comments)
                ->addColumn('checkbox', fn ($c) =>
                    '<input type="checkbox" class="form-check-input row-checkbox" value="'.$c->id.'">')

                ->addColumn('blog', fn ($c) =>
                    '<strong>'.$c->blog->title.'</strong>'
                )

                ->addColumn('user', fn ($c) =>
                    '<strong>'.$c->user->name.'</strong><br>
                     <small class="text-muted">'.$c->user->email.'</small>'
                )

                ->addColumn('status', fn ($c) =>
                    $c->is_approved
                        ? '<span class="badge bg-label-success">Approved</span>'
                        : '<span class="badge bg-label-warning">Pending</span>'
                )

                ->addColumn('actions', function ($c) {
                    return '
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            '.(!$c->is_approved ? '<button type="button" class="btn btn-icon btn-sm btn-label-success approve-btn" data-id="'.$c->id.'" title="Approve"><i class="ti tabler-check ti-xs"></i></button>' : '').'
                            <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-id="'.$c->id.'" title="Delete">
                                <i class="ti tabler-trash ti-xs"></i>
                            </button>
                        </div>
                    ';
                })

                ->rawColumns(['checkbox', 'blog', 'user', 'status', 'actions'])
                ->make(true);
        }

        $stats = [
            'total'    => BlogComment::count(),
            'approved' => BlogComment::where('is_approved', true)->count(),
            'pending'  => BlogComment::where('is_approved', false)->count(),
        ];

        return view('content.blog-comments.index', compact('stats'));
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

    public function bulkApprove(Request $request)
    {
        BlogComment::whereIn('id', $request->ids)->update(['is_approved' => true]);

        return response()->json(['message' => 'Comments approved']);
    }

    public function bulkDelete(Request $request)
    {
        BlogComment::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Comments deleted']);
    }
}
