<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BlogCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = BlogCategory::query();

            return DataTables::of($categories)
                ->addColumn('checkbox', fn ($cat) =>
                    '<input type="checkbox" class="row-checkbox" value="'.$cat->id.'">')

                ->addColumn('name_column', fn ($cat) =>
                    '<strong>'.$cat->name.'</strong><br>
                     <small class="text-muted">/'.$cat->slug.'</small>')

                ->addColumn('status', fn ($cat) =>
                    $cat->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>'
                )

                ->addColumn('actions', function ($cat) {
                    return '
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <a href="'.route('blog-categories.edit', $cat->id).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                                <i class="ti tabler-pencil ti-xs"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-id="'.$cat->id.'" title="Delete">
                                <i class="ti tabler-trash ti-xs"></i>
                            </button>
                        </div>
                    ';
                })


                ->rawColumns(['checkbox', 'name_column', 'status', 'actions'])
                ->make(true);
        }

        $stats = [
            'total' => \App\Models\BlogCategory::count(),
        ];

        return view('content.blog-categories.index', compact('stats'));
    }

    public function create()
    {
        return view('content.blog-categories.create');
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:blog_categories,slug',
            'description'      => 'nullable|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'position'         => 'nullable|integer',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active');

        BlogCategory::create($data);

        return redirect()->route('blog-categories.index')
            ->with('success', 'Blog category created successfully');
    }

    public function edit(BlogCategory $blogCategory)
    {
        return view('content.blog-categories.edit', compact('blogCategory'));
    }

    public function update(Request $request, BlogCategory $blogCategory)
    {

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:blog_categories,slug,' . $blogCategory->id,
            'description'      => 'nullable|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'position'         => 'nullable|integer',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active');

        $blogCategory->update($data);

        return redirect()->route('blog-categories.index')
            ->with('success', 'Blog category updated successfully');
    }

    public function destroy(BlogCategory $blogCategory)
    {

        try {
            $blogCategory->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Blog category deleted successfully'
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete blog category'
            ], 500);
        }
    }


    public function bulkDelete(Request $request)
    {

        BlogCategory::whereIn('id', $request->ids)->delete();
        return response()->json(['message' => 'Deleted']);
    }

}
