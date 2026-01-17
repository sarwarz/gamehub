<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PageController extends Controller
{
    /**
     * Admin list (DataTable)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $pages = Page::query();

            return DataTables::of($pages)
                ->addColumn('checkbox', fn ($page) =>
                    '<input type="checkbox" class="row-checkbox" value="'.$page->id.'">')

                ->addColumn('title_column', fn ($page) =>
                    '<strong>'.$page->title.'</strong><br>
                     <small class="text-muted">/'.$page->slug.'</small>')

                ->addColumn('menu', fn ($page) =>
                    ($page->show_in_header ? '<span class="badge bg-info me-1">Header</span>' : '') .
                    ($page->show_in_footer ? '<span class="badge bg-warning">Footer</span>' : '—')
                )

                ->addColumn('status', fn ($page) =>
                    $page->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>'
                )

                ->addColumn('actions', fn ($page) => '
                    <a href="'.route('pages.edit', $page).'"
                       class="btn btn-sm btn-warning">Edit</a>
                    <button class="btn btn-sm btn-danger delete-btn"
                            data-id="'.$page->id.'">Delete</button>
                ')

                ->rawColumns(['checkbox', 'title_column', 'menu', 'status', 'actions'])
                ->make(true);
        }

        return view('content.pages.index');
    }

    /**
     * Create page form
     */
    public function create()
    {
        return view('content.pages.create');
    }

    /**
     * Store page
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:pages,slug',
            'content'          => 'nullable|string',
            'featured_image'   => 'nullable|image|max:2048',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'position'         => 'nullable|integer',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['is_active'] = $request->has('is_active');
        $data['show_in_header'] = $request->has('show_in_header');
        $data['show_in_footer'] = $request->has('show_in_footer');

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')
                ->store('pages', 'public');
        }

        Page::create($data);

        return redirect()->route('pages.index')
            ->with('success', 'Page created successfully');
    }

    /**
     * Edit page form
     */
    public function edit(Page $page)
    {
        return view('content.pages.edit', compact('page'));
    }

    /**
     * Update page
     */
    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'content'          => 'nullable|string',
            'featured_image'   => 'nullable|image|max:2048',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'position'         => 'nullable|integer',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['is_active'] = $request->has('is_active');
        $data['show_in_header'] = $request->has('show_in_header');
        $data['show_in_footer'] = $request->has('show_in_footer');

        if ($request->hasFile('featured_image')) {
            if ($page->featured_image) {
                Storage::disk('public')->delete($page->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')
                ->store('pages', 'public');
        }

        $page->update($data);

        return redirect()->route('pages.index')
            ->with('success', 'Page updated successfully');
    }

    /**
     * Delete page
     */
    public function destroy(Page $page)
    {
        if ($page->featured_image) {
            Storage::disk('public')->delete($page->featured_image);
        }

        $page->delete();

        return response()->json(['message' => 'Page deleted']);
    }

    /**
     * Bulk delete
     */
    public function bulkDelete(Request $request)
    {
        Page::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Pages deleted']);
    }

    /**
     * Frontend page view
     */
    public function show(Page $page)
    {
        abort_if(!$page->is_active, 404);

        return view('pages.show', compact('page'));
    }
}
