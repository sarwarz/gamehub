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
                        <div class="dropdown">
                            <button
                                type="button"
                                class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ti tabler-dots-vertical"></i>
                            </button>

                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="'.route('blog-categories.edit', $cat->id).'">
                                    <i class="ti tabler-edit me-1"></i> Edit
                                </a>

                                <a class="dropdown-item text-danger delete-btn"
                                href="javascript:void(0);"
                                data-id="'.$cat->id.'">
                                    <i class="ti tabler-trash me-1"></i> Delete
                                </a>
                            </div>
                        </div>
                    ';
                })


                ->rawColumns(['checkbox', 'name_column', 'status', 'actions'])
                ->make(true);
        }

        return view('content.blog-categories.index');
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
