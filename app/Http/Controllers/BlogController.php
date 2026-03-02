<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $blogs = Blog::with('category');

            if ($request->filled('status')) {
                $blogs->where('is_published', $request->status === 'published');
            }

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
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="'.route('blogs.edit', $blog).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                            <i class="ti tabler-pencil ti-xs"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-id="'.$blog->id.'" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>
                    </div>
                ')


                ->rawColumns(['checkbox','title_column','category','status','actions'])
                ->make(true);
        }

        $stats = [
            'total'     => Blog::count(),
            'published' => Blog::where('is_published', true)->count(),
            'draft'     => Blog::where('is_published', false)->count(),
        ];

        return view('content.blogs.index', compact('stats'));
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

            $image = $request->file('featured_image');

            // Unique file name
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

            // Move to public/uploads/blogs
            $image->move(public_path('uploads/blogs'), $filename);

            // Save path in DB
            $data['featured_image'] = 'uploads/blogs/' . $filename;
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

            //  Delete old image if exists
            if ($blog->featured_image && File::exists(public_path($blog->featured_image))) {
                File::delete(public_path($blog->featured_image));
            }

            $image = $request->file('featured_image');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

            //  Move to public/uploads/blogs
            $image->move(public_path('uploads/blogs'), $filename);

            //  Save new path
            $data['featured_image'] = 'uploads/blogs/' . $filename;
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

    public function bulkDelete(Request $request)
    {

        Blog::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Blog posts deleted']);
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
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <a href="'.route('blogs.edit', $blog->id).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                                    <i class="ti tabler-pencil ti-xs"></i>
                                </a>
                                <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-id="'.$blog->id.'" title="Delete">
                                    <i class="ti tabler-trash ti-xs"></i>
                                </button>
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
