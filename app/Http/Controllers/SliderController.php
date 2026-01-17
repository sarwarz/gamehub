<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sliders = Slider::with('product');

            return DataTables::of($sliders)
                ->addColumn('checkbox', function ($slider) {
                    return '<input type="checkbox" class="form-check-input row-checkbox" value="'.$slider->id.'">';
                })

                ->addColumn('slider_column', function ($slider) {
                    return '
                        <div class="d-flex align-items-center gap-2">
                            <img src="'.asset('storage/'.$slider->image).'"
                                class="rounded" width="50">
                            <div>
                                <strong>'.$slider->display_title.'</strong><br>
                                <small class="text-muted">'.$slider->display_subtitle.'</small>
                            </div>
                        </div>';
                })

                ->addColumn('product', function ($slider) {
                    return $slider->product
                        ? '<span class="badge bg-label-primary">'.$slider->product->title.'</span>'
                        : '—';
                })

                ->addColumn('status_badge', function ($slider) {
                    return $slider->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->addColumn('actions', function ($slider) {
                    return '
                        <a href="'.route('sliders.edit', $slider).'"
                        class="btn btn-sm btn-warning">
                        Edit
                        </a>
                        <button class="btn btn-sm btn-danger delete-btn"
                                data-id="'.$slider->id.'">
                            Delete
                        </button>
                    ';
                })

                ->rawColumns([
                    'checkbox',
                    'slider_column',
                    'product',
                    'status_badge',
                    'actions'
                ])
                ->make(true);
        }

        return view('content.sliders.index');
    }

    public function create()
    {
        $products = Product::orderBy('title')->get();
        return view('content.sliders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'nullable|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'product_id'   => 'nullable|exists:products,id',
            'image'        => 'required|image|max:2048',
            'button_text'  => 'nullable|string|max:50',
            'button_url'   => 'nullable|url',
            'position'     => 'nullable|integer',
        ]);

        $data['is_active'] = $request->has('is_active');



        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('sliders', 'public');
        }

        Slider::create($data);

        return redirect()->route('sliders.index')
            ->with('success', 'Slider created successfully');
    }

    public function edit(Slider $slider)
    {
        $products = Product::orderBy('title')->get();
        return view('content.sliders.edit', compact('slider', 'products'));
    }

    public function update(Request $request, Slider $slider)
    {
        $data = $request->validate([
            'title'        => 'nullable|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'product_id'   => 'nullable|exists:products,id',
            'image'        => 'nullable|image|max:2048',
            'button_text'  => 'nullable|string|max:50',
            'button_url'   => 'nullable|url',
            'position'     => 'nullable|integer',
            'is_active'    => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($slider->image) {
                Storage::disk('public')->delete($slider->image);
            }
            $data['image'] = $request->file('image')->store('sliders', 'public');
        }

        $slider->update($data);

        return redirect()->route('sliders.index')
            ->with('success', 'Slider updated successfully');
    }

    public function destroy(Slider $slider)
    {
        if ($slider->image) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->delete();

        return redirect()->route('sliders.index')
            ->with('success', 'Slider deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        Slider::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Sliders deleted successfully']);
    }

}
