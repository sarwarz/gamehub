<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $blogs = Blog::with('category');

            return DataTables::of($blogs)
                ->addColumn('checkbox', fn ($blog) =>
                    '<input type="checkbox" class="row-checkbox" value="'.$blog->id.'">')

                ->addColumn('title_column', fn ($blog) => '
                    <strong>'.$blog->title.'</strong><br>
                    <small class="text-muted">/'.$blog->slug.'</small>
                ')

                ->addColumn('category', fn ($blog) =>
                    $blog->category
                        ? '<span class="badge bg-label-primary">'.$blog->category->name.'</span>'
                        : '—'
                )

                ->addColumn('status', fn ($blog) =>
                    $blog->is_published
                        ? '<span class="badge bg-success">Published</span>'
                        : '<span class="badge bg-danger">Draft</span>'
                )

                ->addColumn('actions', fn ($blog) => '
                    <div class="dropdown">
                        <button
                            type="button"
                            class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="ti tabler-dots-vertical"></i>
                        </button>

                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="'.route('blogs.edit', $blog).'">
                                <i class="ti tabler-edit me-1"></i> Edit
                            </a>

                            <a class="dropdown-item text-danger delete-btn"
                            href="javascript:void(0);"
                            data-id="'.$blog->id.'">
                                <i class="ti tabler-trash me-1"></i> Delete
                            </a>
                        </div>
                    </div>
                ')


                ->rawColumns(['checkbox','title_column','category','status','actions'])
                ->make(true);
        }

        return view('content.blogs.index');
    }

    public function create()
    {
        $categories = BlogCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('content.blogs.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'blog_category_id' => 'required|exists:blog_categories,id',
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|unique:blogs,slug',
            'content'          => 'required|string',
            'featured_image'   => 'nullable|image|max:2048',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'published_at'     => 'nullable|date',
            'position'         => 'nullable|integer',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['is_published'] = $request->has('is_published');

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] =
                $request->file('featured_image')->store('blogs', 'public');
        }

        Blog::create($data);

        return redirect()->route('blogs.index')
            ->with('success', 'Blog post created successfully');
    }

    public function edit(Blog $blog)
    {
        $categories = BlogCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('content.blogs.edit', compact('blog','categories'));
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'blog_category_id' => 'required|exists:blog_categories,id',
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|unique:blogs,slug,' . $blog->id,
            'content'          => 'required|string',
            'featured_image'   => 'nullable|image|max:2048',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'published_at'     => 'nullable|date',
            'position'         => 'nullable|integer',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['is_published'] = $request->has('is_published');

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $data['featured_image'] =
                $request->file('featured_image')->store('blogs', 'public');
        }

        $blog->update($data);

        return redirect()->route('blogs.index')
            ->with('success', 'Blog post updated successfully');
    }

    public function destroy(Blog $blog)
    {
        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function popular(Request $request)
    {
        if ($request->ajax()) {
            $blogs = Blog::with('category')
                ->orderByDesc('views');

            return DataTables::of($blogs)
                ->addColumn('checkbox', fn ($blog) =>
                    '<input type="checkbox" class="row-checkbox" value="'.$blog->id.'">')

                ->addColumn('title_column', fn ($blog) => '
                    <strong>'.$blog->title.'</strong><br>
                    <small class="text-muted">/'.$blog->slug.'</small>
                ')

                ->addColumn('category', fn ($blog) =>
                    $blog->category
                        ? '<span class="badge bg-label-primary">'.$blog->category->name.'</span>'
                        : '—'
                )

                ->addColumn('views', fn ($blog) =>
                    '<span class="fw-semibold">'.$blog->views.'</span>'
                )

                ->addColumn('status', fn ($blog) =>
                    $blog->is_published
                        ? '<span class="badge bg-success">Published</span>'
                        : '<span class="badge bg-danger">Draft</span>'
                )

                ->addColumn('actions', function ($blog) {
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
                                    <a class="dropdown-item" href="'.route('blogs.edit', $blog->id).'">
                                        <i class="ti tabler-edit me-1"></i> Edit
                                    </a>

                                    <a class="dropdown-item text-danger delete-btn"
                                    href="javascript:void(0);"
                                    data-id="'.$blog->id.'">
                                        <i class="ti tabler-trash me-1"></i> Delete
                                    </a>
                                </div>
                            </div>
                        ';
                    })


                ->rawColumns([
                    'checkbox',
                    'title_column',
                    'category',
                    'views',
                    'status',
                    'actions'
                ])
                ->make(true);
        }

        return view('content.blogs.popular');
    }

}
